<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/setup.php';
require_once __DIR__ . '/../includes/Router.php';

$base_path = $config['app']['root'];

$routes = require_once __DIR__ . '/../includes/routes.php';
$public_routes = $routes['public'];
$protected_routes = $routes['protected'];

$request_route = Router::getCleanPath(
    $_SERVER['REQUEST_URI'] ?? '',
    'error404',
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $route_info = $protected_routes[$request_route] ?? ($public_routes ?? null);

    if (
        $route_info &&
        isset($route_info['controller'], $route_info['action'])
    ) {
        $className = $route_info['controller'];
        $method = $route_info['action'];

        require_once __DIR__ . "/../includes/{$className}.php";

        if ($className === 'UserController') {
            $controller = new UserController($userRepo, $authService);
        } else {
            $controller = new AuthController($authService);
        }

        $controller->$method();
    }
}

$is_logged_in = $authService->isLoggedIn();
$is_public_route = array_key_exists($request_route, $public_routes);
$is_protected_route = array_key_exists($request_route, $protected_routes);

// Everyone can access public routes, regardless
if ($is_public_route) {
    $target = $public_routes[$request_route]['file'] ?? null;

    $page = Router::getVerifiedPagePath($base_path, $target);

    require_once $page;
    exit();
}

if (!$is_logged_in && !$is_public_route) {
    header('Location: /login');
    exit();
}

if ($is_logged_in && $is_protected_route) {
    $route_info = $protected_routes[$request_route];
    $has_required_role = $authService->hasRole($route_info['role']);

    if ($has_required_role) {
        $target = $route_info['file'];
        $page = Router::getVerifiedPagePath($base_path, $target);
        require_once $page;
        exit();
    }

    http_response_code(403);
    $page = Router::getVerifiedPagePath($base_path, 'error-403.php');
    require_once $page;
    exit();
}

http_response_code(404);
$page = Router::getVerifiedPagePath($base_path, 'error-404.php');
require_once $page;
exit();
