<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/setup.php';
require_once __DIR__ . '/../includes/Router.php';

$base_path = $config['app']['root'];

$routes = [
    '' => 'login.php',
    'dashboard' => 'dashboard.php',
    'users' => 'manage-users.php',
    'naans' => 'manage-naans.php',
    'shoulders' => 'manage-shoulders.php',
    'arks' => 'manage-arks.php',
    'error404' => 'error-404.php',
];

// 1. Get the user's request
$request_slug = Router::getCleanPath($_SERVER['REQUEST_URI'] ?? '', 'error404');

// 2. Handle Controller actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $request_slug === 'auth') {
    require_once __DIR__ . '/../includes/AuthController.php';
    AuthController::login($db);
    exit();
}

if ($request_slug === 'logout') {
    require_once __DIR__ . '/../includes/AuthController.php';
    AuthController::logout();
    exit();
}

// 3. Resolve request to template file
$target_filename = $routes[$request_slug] ?? 'error-404.php';

// 4. Handle 404s
$is_not_found =
    $request_slug === 'error404' || $target_filename === 'error-404.php';

if ($is_not_found) {
    http_response_code(404);
}

// 5. Load the page
$absolute_path = Router::getVerifiedPagePath($base_path, $target_filename);

require_once $absolute_path;
