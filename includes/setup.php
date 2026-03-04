<?php
declare(strict_types=1);

// Start output buffering
ob_start();

// Auto flush as needed
register_shutdown_function(function () {
    if (ob_get_length()) {
        ob_end_flush();
    }
    error_log('Shutdown ran!'); // Check your error log after a request
});

// 1. Load Configurations
$config = require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';

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
header('X-Frame-Options: DENY'); // Prevent this page being framed (clickjacking protection)
header('X-Content-Type-Options: nosniff'); // Stop MIME type sniffing (reduces drive-by download risks)

// Build a focused Content Security Policy (CSP)
$csp = "default-src 'self'; "; // Default: allow resources only from same origin
$csp .= "img-src 'self' data:; "; // Allow images from same-origin and data: URIs (needed for CSS data-URI icons)
$csp .= "object-src 'none'; "; // Disallow <object>/<embed>/<applet> (blocks legacy plugin attack vectors)
$csp .= "base-uri 'self'; "; // Restrict <base> tag to same origin to prevent base href tampering
$csp .= "frame-ancestors 'none'; "; // Prevent this page from being framed by any origin (complements X-Frame-Options)
$csp .= "form-action 'self'; "; // Only allow forms to submit to same origin

// Optional: tighten script/style handling (recommended via nonces/hashes). Uncomment and use with care.
// Note: using 'unsafe-inline' is discouraged — prefer nonces or hashes.
// $csp .= "script-src 'self' 'nonce-XYZ'; "; // Allow scripts from self that provide runtime nonce 'XYZ' (replace at runtime)
// $csp .= "style-src 'self' 'nonce-XYZ'; ";  // Allow styles from self that provide runtime nonce 'XYZ'

// Generate a unique nonce for this request (after session_start for randomness)
$nonce = base64_encode(random_bytes(16));
// Add to CSP (uncomment and adapt your commented lines)
$csp .= "style-src 'self' 'nonce-$nonce'; "; // Allows external self + nonced inline styles
// Optionally add for scripts if needed: $csp .= "script-src 'self' 'nonce-$nonce'; ";

// NOT RECOMMENDED / DANGEROUS (commented out):
// // $csp .= "default-src 'self' data:; "; // Avoid adding data: to default-src (too permissive)
// // $csp .= "script-src 'unsafe-inline' 'self'; "; // Avoid 'unsafe-inline' for scripts (XSS risk)
// // $csp .= "style-src 'unsafe-inline' 'self'; "; // Avoid 'unsafe-inline' for styles (use nonces/hashes instead)

// Send the effective CSP header
header('Content-Security-Policy: ' . $csp); // Apply the assembled CSP

// Make the nonce available for templates (e.g., store in a global or function)
define('CSP_NONCE', $nonce); // Or use a function: function get_csp_nonce() { return CSP_NONCE; }

/*
 * <style nonce="<?= htmlspecialchars(CSP_NONCE, ENT_QUOTES) ?>">
 *  Your inline CSS here
 * </style>
 */

// Optional additional headers (enable if appropriate for your deployment):
// header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload'); // HSTS: enforce HTTPS (only if site always on HTTPS)
// header('Referrer-Policy: no-referrer-when-downgrade'); // Control referrer info (tweak to desired policy)
// header('X-XSS-Protection: 0'); // Deprecated in modern browsers; generally left unset (browsers use CSP/ built-in mitigations)

// If you want to test a CSP without blocking, use Report-Only (uncomment to enable):
// header('Content-Security-Policy-Report-Only: ' . $csp . " report-uri /csp-report-endpoint;");

// 4. Session & State Management

// Set the SameSite attribute via ini_set to ensure compatibility
ini_set('session.cookie_samesite', 'Lax');

// Start session with basic functional options
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => $config['app']['https_only'],
    'cookie_httponly' => true,
]);

ensure_csrf_token();

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
