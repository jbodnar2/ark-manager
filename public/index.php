<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/setup.php';
require_once __DIR__ . '/../includes/Router.php';

$base_path = $config['app']['root'];

$routes = require_once __DIR__ . '/../includes/routes.php';
$public_routes = $routes['public'];
$protected_routes = $routes['protected'];

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$request_route = Router::getCleanPath(
    $_SERVER['REQUEST_URI'] ?? '',
    'error404',
);

$entry =
    $protected_routes[$request_route] ??
    ($public_routes[$request_route] ?? null);

$route_info = $entry[$method] ?? $entry;

$is_public = array_key_exists($request_route, $public_routes);
$is_logged_in = $authService->isLoggedIn();

if (!$is_logged_in && !$is_public) {
    header('Location: /');
    exit();
}

$required_role = $route_info['role'] ?? null;

if ($required_role && !$authService->hasRole($required_role)) {
    http_response_code(403);
    echo "403 Forbidden: You do not have the required '$required_role' role.";
    exit();
}

if (!$route_info) {
    http_response_code(404);
    $page = Router::getVerifiedPagePath($base_path, 'error-404.php');
    require_once $page;
    exit();
}

$className = $route_info['controller'] ?? null;
$action = $route_info['action'] ?? null;
$target = $route_info['file'] ?? null;

if ($className && $action) {
    if ($method === 'POST') {
        validate_csrf();
    }

    require_once __DIR__ . "/../includes/{$className}.php";

    if ($className === 'UserController') {
        $controller = new UserController($userRepo, $authService);
    } elseif ($className === 'DashboardController') {
        $controller = new DashboardController($authService);
    } else {
        $controller = new AuthController($authService);
    }

    $controller->$action();
    exit();
}

if ($target) {
    $page = Router::getVerifiedPagePath($base_path, $target);
    require_once $page;
    exit();
}
