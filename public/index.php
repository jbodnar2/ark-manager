<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/setup.php';
require_once __DIR__ . '/../includes/Router.php';

$base_path = $config['app']['root'];

// 1. Load Route Configuration
$routes = require_once __DIR__ . '/../includes/routes.php';
$public_routes = $routes['public'];
$protected_routes = $routes['protected'];

// 2. Identify Request Path
$request_route = Router::getCleanPath(
    $_SERVER['REQUEST_URI'] ?? '',
    'error404',
);

// 3. Resolve Route Information
$route_info =
    $protected_routes[$request_route] ??
    ($public_routes[$request_route] ?? null);

$is_public = array_key_exists($request_route, $public_routes);
$is_logged_in = $authService->isLoggedIn();

// 4. Security Gate: Redirect guests if the route is not explicitly public
// This hides the existence of paths from unauthenticated users
if (!$is_logged_in && !$is_public) {
    header('Location: /login');
    exit();
}

// 5. Handle Non-Existent Routes (404) for Logged-In Users
if (!$route_info) {
    http_response_code(404);
    $page = Router::getVerifiedPagePath($base_path, 'error-404.php');
    require_once $page;
    exit();
}

// 6. Logic Layer: Handle POST Actions (Controllers)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    if (isset($route_info['controller'], $route_info['action'])) {
        $className = $route_info['controller'];
        $method = $route_info['action'];

        require_once __DIR__ . "/../includes/{$className}.php";

        if ($className === 'UserController') {
            $controller = new UserController($userRepo, $authService);
        } else {
            $controller = new AuthController($authService);
        }

        $controller->$method();
        exit();
    }
}

// 7. Authorization Layer: Check Roles for Protected Routes
if (!$is_public) {
    $required_role = $route_info['role'] ?? 'user';

    if (!$authService->hasRole($required_role)) {
        http_response_code(403);
        $page = Router::getVerifiedPagePath($base_path, 'error-403.php');
        require_once $page;
        exit();
    }
}

// 8. Rendering Layer: Handle GET Requests (Files)
$target = $route_info['file'] ?? null;
if ($target) {
    $page = Router::getVerifiedPagePath($base_path, $target);
    require_once $page;
    exit();
}

// Fallback for misconfigured routes
http_response_code(404);
$page = Router::getVerifiedPagePath($base_path, 'error-404.php');
require_once $page;
exit();
