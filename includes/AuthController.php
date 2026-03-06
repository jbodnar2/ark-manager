<?php
declare(strict_types=1);

class AuthController
{
    private UserRepository $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function login(string $username, string $password): void
    {
        $user = $this->userRepo->findByUsername($username);

        // Verify password and ensure the account is not inactive
        if (
            $user &&
            $user['role'] !== 'inactive' &&
            password_verify($password, $user['password_hash'])
        ) {
            $this->startUserSession($user);
            header('Location: /dashboard');
            exit();
        }

        $this->handleLoginFailure();
    }

    private function startUserSession(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role' => $user['role'],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'last_login' => time(),
        ];
    }

    private function handleLoginFailure(): void
    {
        $_SESSION['error'] = [
            'message' => 'Invalid credentials or inactive account.',
            'attempts' => ($_SESSION['error']['attempts'] ?? 0) + 1,
        ];
        header('Location: /login');
        exit();
    }

    // public static function isLoggedIn(): bool
    public function isLoggedIn($userRepo): bool
    {
        if (!isset($_SESSION['user']['id'])) {
            return false;
        }

        $currentAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $storedAgent = $_SESSION['user']['user_agent'];

        if ($currentAgent !== $storedAgent) {
            $this->logout();
            return false;
        }

        $user = $userRepo->findById((int) $_SESSION['user']['id']);

        if (!$user || $user['role'] === 'inactive') {
            $_SESSION = [];
            return false;
        }

        return true;
    }

    public function hasRole(string $requiredRole): bool
    {
        $userRole = $_SESSION['user']['role'] ?? 'unknown';

        $hierarchy = [
            'viewer' => ['viewer', 'user', 'admin'],
            'user' => ['user', 'admin'],
            'admin' => ['admin'],
        ];

        return in_array($userRole, $hierarchy[$requiredRole] ?? [], true);
    }

    public function logout(): void
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
