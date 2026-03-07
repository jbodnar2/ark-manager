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
    if ($request_route === 'auth') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $auth->login($username, $password);
        exit();
    }

    if ($request_route === 'logout') {
        $auth->logout();
        exit();
    }
}

$is_logged_in = $auth->isLoggedIn($userRepo);

if (array_key_exists($request_route, $public_routes)) {
    $target = $public_routes[$request_route]['file'] ?? null;

    $page = Router::getVerifiedPagePath($base_path, $target);
    require_once $page;
    exit();
}

if (array_key_exists($request_route, $protected_routes)) {
    if (!$is_logged_in) {
        header('Location: /login');
        exit();
    }

    $route_info = $protected_routes[$request_route];

    if (!$auth->hasRole($route_info['role'])) {
        http_response_code(403);
        $page = Router::getVerifiedPagePath($base_path, 'error-403.php');
        require_once $page;
        exit();
    }

    $target = $route_info['file'];
    $page = Router::getVerifiedPagePath($base_path, $target);
    require_once $page;
    exit();
}

http_response_code(404);
$page = Router::getVerifiedPagePath($base_path, 'error-404.php');
require_once $page;
exit();
