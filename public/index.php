<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/setup.php';
require_once __DIR__ . '/../includes/Router.php';
require_once __DIR__ . '/../includes/AuthController.php';

$base_path = $config['app']['root'];

$public_routes = ['', 'login'];

$protected_routes = [
    'login' => 'login.php',
    '' => 'login.php',
    'dashboard' => 'dashboard.php',
    'users' => 'manage-users.php',
    'naans' => 'manage-naans.php',
    'shoulders' => 'manage-shoulders.php',
    'arks' => 'manage-arks.php',
    'error404' => 'error-404.php',
];

$request_route = Router::getCleanPath(
    $_SERVER['REQUEST_URI'] ?? '',
    'error404',
);

if ($request_route === 'logout') {
    AuthController::logout();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $request_route === 'auth') {
    AuthController::login($db);
    exit();
}

if (
    !in_array($request_route, $public_routes) &&
    !AuthController::isLoggedIn()
) {
    header('Location: /');
    exit();
}

// 3. Resolve request to template file
$target_filename = $protected_routes[$request_route] ?? 'error-404.php';

// 4. Handle 404s
$is_not_found =
    $request_route === 'error404' || $target_filename === 'error-404.php';

if ($is_not_found) {
    http_response_code(404);
}

// 5. Load the page
$absolute_path = Router::getVerifiedPagePath($base_path, $target_filename);

require_once $absolute_path;
