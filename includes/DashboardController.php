<?php

declare(strict_types=1);

class DashboardController
{
    private AuthService $authService;

    private const DEFAULT_PAGE_TITLE = 'Dashboard';

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function showDashboard(): void
    {
        if (!$this->authService->isLoggedIn()) {
            header('Location: /');
            exit();
        }

        $page_title = self::DEFAULT_PAGE_TITLE;

        $user = $_SESSION['user'] ?? [];
        $is_admin = $this->authService->hasRole('admin');
        $is_user = $this->authService->hasRole('user');
        $is_viewer = $this->authService->hasRole('viewer');

        require_once __DIR__ . '/pages/dashboard.php';
    }
}
