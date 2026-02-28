<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/setup.php';
require_once __DIR__ . '/../includes/Router.php';

$root = $config['app']['root'];

$valid_routes = [
    '' => 'dashboard.php',
    'login' => 'login.php',
    'logout' => 'logout.php',
    'dashboard' => 'dashboard.php',
    'users' => 'dashboard.php', // 'manage-users.php'
    'naans' => 'dashboard.php', // manage-naans.php
    'shoulders' => 'dashboard.php', // manage-shoulders.php
    'arks' => 'dashboard.php', // manage-arks.php
    'error404' => 'error-404.php',
];

$clean_path = Router::getCleanPath($_SERVER['REQUEST_URI'] ?? '', 'error404');

$lookup_route = Router::mapToRoute($clean_path, $valid_routes);

if ($lookup_route === 'error404') {
    http_response_code(404);
}

$page_file = Router::getVerifiedPagePath($root, $valid_routes[$lookup_route]);

require_once $page_file;
