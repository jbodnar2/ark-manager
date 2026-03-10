<?php
declare(strict_types=1);

class AuthController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($this->authService->authenticate($username, $password)) {
            $_SESSION['success_message'] = 'Login successful.';

            unset($_SESSION['error_message']);

            header('Location: /dashboard');
        } else {
            $_SESSION['error_message'] = 'Invalid username or password.';
            header('Location: /login');
        }
        exit();
    }

    public function logout(): void
    {
        $this->authService->logout();
        header('Location: /login');
        exit();
    }
}
