<?php
declare(strict_types=1);

class AuthController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    private const DEFAULT_TITLE = 'ARK Manager Login';

    public function getView(): void
    {
        if ($this->authService->isLoggedIn()) {
            header('Location: /dashboard');
            exit();
        }

        $page_title = self::DEFAULT_TITLE;
        require_once __DIR__ . '/pages/login.php';
    }

    public function login(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($this->authService->authenticate($username, $password)) {
            header('Location: /dashboard');
            exit();
        }

        $page_title = self::DEFAULT_TITLE;
        $error = 'Invalid username or password';

        require_once __DIR__ . '/pages/login.php';
        exit();
    }

    public function logout(): void
    {
        $this->authService->logout();
        header('Location: /');
        exit();
    }
}
