<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;

class DashboardController extends BaseController
{
    private const DEFAULT_PAGE_TITLE = 'ARK Manager Dashboard';

    public function __construct(AuthService $authService)
    {
        // $this->authService = $authService;
        parent::__construct($authService);
    }

    public function index(): void
    {
        $this->render('dashboard/index', [
            'page_title' => self::DEFAULT_PAGE_TITLE,
        ]);
    }
}
