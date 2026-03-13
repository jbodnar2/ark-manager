<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/setup.php';
require_once __DIR__ . '/../app/Core/PathHelper.php';
require_once __DIR__ . '/../app/Core/Router.php';

use App\Core\PathHelper;
use App\Core\Router;

$base_path = $config['app']['root'];
$routes = require_once __DIR__ . '/../config/routes.php';

$router = new Router($routes);

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$request_route = PathHelper::getCleanPath(
    $_SERVER['REQUEST_URI'] ?? '',
    'error404',
);

// 1. Resolve the route first to see what we are dealing with
$route_info = $router->resolve($request_route, $method);
$is_public = $router->isPublic($request_route);
$is_authorized = $authService->isAuthorized();

// 2. Security Guard: If not authorized and not a public page, redirect to login.
// This handles 404s for logged-out users by sending them home before the 404 check.
if (!$is_authorized && !$is_public) {
    if (str_starts_with($request_route, 'api/')) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
    } else {
        header('Location: /');
    }
    exit();
}

// 3. 404 Guard: Only reached if user is authorized OR the route is public
if (!$route_info) {
    http_response_code(404);
    $page = PathHelper::getVerifiedPagePath($base_path, '404.php');
    require_once $page;
    exit();
}

// 4. Role Guard: Only reached if the route exists and user is logged in
$required_role = $route_info['role'] ?? null;
if ($required_role && !$authService->hasRole($required_role)) {
    http_response_code(403);
    $page = PathHelper::getVerifiedPagePath($base_path, '403.php');
    require_once $page;
    exit();
}

$className = $route_info['controller'] ?? null;
$action = $route_info['action'] ?? null;
$target = $route_info['file'] ?? null;

// 5. Dispatching
if ($className && $action) {
    if ($method === 'POST') {
        $headers = array_change_key_case(getallheaders(), CASE_LOWER);
        if (
            !isset($headers['authorization']) ||
            !str_starts_with($headers['authorization'], 'bearer ')
        ) {
            validate_csrf();
        }
    }

    require_once __DIR__ . "/../app/Controllers/{$className}.php";

    if ($className === 'UserController') {
        $controller = new App\Controllers\UserController(
            $userRepo,
            $authService,
        );
    } elseif ($className === 'DashboardController') {
        $controller = new App\Controllers\DashboardController($authService);
    } else {
        $controller = new App\Controllers\AuthController($authService);
    }

    $controller->$action();
    exit();
}

if ($target) {
    $page = PathHelper::getVerifiedPagePath($base_path, $target);
    require_once $page;
    exit();
}
