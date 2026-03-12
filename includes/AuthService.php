<?php
declare(strict_types=1);

class AuthService
{
    private UserRepository $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function isAuthorized(): bool
    {
        return $this->isLoggedIn() || $this->authenticateByToken();
    }

    public function authenticate(string $username, string $password): bool
    {
        $user = $this->userRepo->findByUsername($username);

        if (
            $user &&
            $user['role'] !== 'inactive' &&
            password_verify($password, $user['password_hash'])
        ) {
            $this->startUserSession($user);
            return true;
        }

        return false;
    }

    private function startUserSession(array $user): void
    {
        if (
            !isset($_SERVER['HTTP_AUTHORIZATION']) &&
            !isset($_SERVER['authorization'])
        ) {
            session_regenerate_id(true);
        }

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

    private function authenticateByToken(): bool
    {
        $headers = getAllHeaders();

        $authHeader =
            $headers['Authorization'] ?? ($headers['authorization'] ?? '');

        if (str_starts_with($authHeader, 'Bearer ')) {
            $token = trim(substr($authHeader, 7));
            $user = $this->userRepo->findByToken($token);

            if ($user && $user['role'] !== 'inactive') {
                $this->startUserSession($user);
                return true;
            }
        }

        return false;
    }

    public function isLoggedIn(): bool
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

        $user = $this->userRepo->findById((int) $_SESSION['user']['id']);

        if (!$user || $user['role'] === 'inactive') {
            $_SESSION = [];
            $this->logout();
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

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}
