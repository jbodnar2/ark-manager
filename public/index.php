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
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $auth->login($username, $password);
        exit();
    }

    if ($request_route === 'add-user') {
        // Admin-only route (protected routes check runs for GET later,
        // so ensure the user is logged in & authorized)
        if (!$auth->isLoggedIn() || !$auth->hasRole('admin')) {
            http_response_code(403);
            exit('Forbidden');
        }

        // Collect and validate input
        $first = trim($_POST['first_name'] ?? '');
        $last = trim($_POST['last_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_pwd'] ?? '';

        // Basic server-side validations
        if (
            $first === '' ||
            $last === '' ||
            $username === '' ||
            $email === '' ||
            $password === '' ||
            $confirm === ''
        ) {
            $_SESSION['error_message'] = 'All fields are required.';
            header('Location: /users');
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_message'] = 'Invalid email address.';
            header('Location: /users');
            exit();
        }

        if ($password !== $confirm) {
            $_SESSION['error_message'] = 'Passwords do not match.';
            header('Location: /users');
            exit();
        }

        if (strlen($password) < 8) {
            $_SESSION['error_message'] =
                'Password must be at least 8 characters.';
            header('Location: /users');
            exit();
        }

        // Attempt to create the user, the repository will throw on duplicates/invalid role/email
        try {
            $newId = $userRepo->createUser(
                $username,
                $first,
                $last,
                $email,
                $password,
                $role,
            );
            $_SESSION['success_message'] = 'User created successfully.';
        } catch (InvalidArgumentException $e) {
            $_SESSION['error_message'] = $e->getMessage();
        } catch (PDOException $e) {
            error_log('DB error creating user: ' . $e->getMessage());
            $_SESSION['error_message'] = 'Unable to create user at this time.';
        }

        // PRG pattern
        header('Location: /users');
        exit();
    }

    if ($request_route === 'logout') {
        $auth->logout();
        exit();
    }
}

$is_logged_in = $auth->isLoggedIn();

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
