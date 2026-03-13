<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;

class DashboardController
{
    private AuthService $authService;

    private const DEFAULT_PAGE_TITLE = 'ARK Manager Dashboard';

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function getView(): void
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

        require_once __DIR__ . '/../Views/dashboard/index.php';
    }
}
