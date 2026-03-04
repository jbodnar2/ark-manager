<?php
declare(strict_types=1);

class AuthController
{
    public static function login(PDO $db): void
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $sql =
            'SELECT password_hash, first_name, last_name, role FROM users WHERE username = :user LIMIT 1';
        $stmt = $db->prepare($sql);

        $stmt->execute(['user' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);

            $_SESSION['user'] = [
                'username' => $username,
                'last_login' => time(),
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'role' => $user['role'],
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ];

            header('Location: /dashboard');
            exit();
        } else {
            $_SESSION['error']['message'] = 'Invalid username or password';
            $_SESSION['error']['attempts'] =
                ($_SESSION['error']['attempts'] ?? 0) + 1;

            header('Location: /login');
            exit();
        }
    }

    public static function isLoggedIn()
    {
        if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
            return false;
        }

        $current_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $stored_ip = $_SESSION['user']['ip'] ?? '';

        if ($current_ip !== $stored_ip) {
            self::logout();
            return false;
        }

        return true;
    }

    public static function hasRole(string $required_role): bool
    {
        $user_role = $_SESSION['user']['role'] ?? 'unknown';

        if ($required_role === 'admin') {
            return $user_role === 'admin';
        }

        if ($required_role === 'user') {
            return in_array($user_role, ['admin', 'user']);
        }

        if ($required_role === 'viewer') {
            return in_array($user_role, ['admin', 'user', 'viewer']);
        }

        return false;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly'],
            );
        }
        session_destroy();
        header('Location: /');
        exit();
    }
}
