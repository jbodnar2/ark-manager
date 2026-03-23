<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;

abstract class BaseController
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    protected function render(string $view_path, array $data = []): void
    {
        extract($data, EXTR_PREFIX_SAME, 'data');

        $current_user = $_SESSION['user'] ?? [];
        $is_admin = $this->authService->hasRole('admin');
        $is_user = $this->authService->hasRole('user');
        $is_viewer = $this->authService->hasRole('viewer');

        require_once __DIR__ . '/../Views/' . $view_path . '.php';
    }
}
