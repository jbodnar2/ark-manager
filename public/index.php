<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/setup.php';
require_once __DIR__ . '/../includes/Router.php';
require_once __DIR__ . '/../includes/AuthController.php';

$routes = require_once __DIR__ . '/../includes/routes.php';
$base_path = $config['app']['root'];

$request_route = Router::getCleanPath(
    $_SERVER['REQUEST_URI'] ?? '',
    'error404',
);

if ($request_route === 'logout') {
    AuthController::logout();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();
    if ($request_route === 'auth') {
        AuthController::login($db);
        exit();
    }
}

$public_routes = $routes['public'];
$protected_routes = $routes['protected'];
$is_logged_in = AuthController::isLoggedIn();

if (array_key_exists($request_route, $public_routes)) {
    $target = $public_routes[$request_route];
    require_once Router::getVerifiedPagePath($base_path, $target);
    exit();
}

if (array_key_exists($request_route, $protected_routes)) {
    if (!$is_logged_in) {
        header('Location: /login');
        exit();
    }

    $route_info = $protected_routes[$request_route];

    if (!AuthController::hasRole($route_info['role'])) {
        http_response_code(403);
        require_once Router::getVerifiedPagePath($base_path, 'error-403.php');
        exit();
    }

    $target = $route_info['file'];
    require_once Router::getVerifiedPagePath($base_path, $target);
    exit();
}

http_response_code(404);
require_once Router::getVerifiedPagePath($base_path, 'error-404.php');
exit();
