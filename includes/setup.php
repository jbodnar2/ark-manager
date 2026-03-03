<?php
declare(strict_types=1);

// Start output buffering
ob_start();

// 1. Load Configurations
$config = require_once __DIR__ . '/../config.php';

// 2. Error Reporting Logic
if ($config['app']['debug']) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    $session_save_path = $config['app']['root'] . '/logs/sessions';
    if (!is_dir($session_save_path)) {
        mkdir($session_save_path, 0700, true);
    }
    ini_set('session.save_path', $session_save_path);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// 3. Global Security Headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'self'");

// 4. Session & State Management

// Set the SameSite attribute via ini_set to ensure compatibility
ini_set('session.cookie_samesite', 'Lax');

// Start session with basic functional options
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => $config['app']['https_only'],
    'cookie_httponly' => true,
]);

// 5. Database Connection
try {
    $database_file =
        $config['app']['root'] .
        '/' .
        $config['db']['dir'] .
        '/' .
        $config['db']['name'];

    $db = new PDO('sqlite:' . $database_file);

    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->setAttribute(PDO::ATTR_TIMEOUT, 5);

    $db->exec('PRAGMA journal_mode = WAL');
    $db->exec('PRAGMA foreign_keys = ON');
} catch (PDOException $e) {
    error_log($e->getMessage());
    exit('Error: Unable to connect to the database.');
}
