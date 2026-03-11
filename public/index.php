<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/setup.php';
require_once __DIR__ . '/../includes/Router.php';

$base_path = $config['app']['root'];

$routes = require_once __DIR__ . '/../includes/routes.php';
$public_routes = $routes['public'];
$protected_routes = $routes['protected'];

// 1. Capture the HTTP Method (GET/POST)
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$request_route = Router::getCleanPath(
    $_SERVER['REQUEST_URI'] ?? '',
    'error404',
);

// 2. Resolve the Route
$entry =
    $protected_routes[$request_route] ??
    ($public_routes[$request_route] ?? null);
$route_info = $entry[$method] ?? $entry;

// 3. Authentication Check
$is_public = array_key_exists($request_route, $public_routes);
$is_logged_in = $authService->isLoggedIn();

if (!$is_logged_in && !$is_public) {
    header('Location: /');
    exit();
}

// 4. Authorization Check (Role)
$required_role = $route_info['role'] ?? null;
if ($required_role && !$authService->hasRole($required_role)) {
    http_response_code(403);
    echo "403 Forbidden: You do not have the required '$required_role' role.";
    exit();
}

// 5. 404 Check
if (!$route_info) {
    http_response_code(404);
    $page = Router::getVerifiedPagePath($base_path, 'error-404.php');
    require_once $page;
    exit();
}

$className = $route_info['controller'] ?? null;
$action = $route_info['action'] ?? null; // FIXED: Changed $method to $action
$target = $route_info['file'] ?? null;

// 6. Controller Execution
if ($className && $action) {
    // Correctly check the HTTP method for CSRF
    if ($method === 'POST') {
        validate_csrf();
    }

    require_once __DIR__ . "/../includes/{$className}.php";

    // Instantiate with proper dependencies
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

// 7. Static File Execution
if ($target) {
    $page = Router::getVerifiedPagePath($base_path, $target);
    require_once $page;
    exit();
}
