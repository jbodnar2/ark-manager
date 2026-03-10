<?php

return [
    // Public Routes: GET - All Access
    'public' => [
        '' => ['file' => 'login.php'],
        'login' => ['file' => 'login.php'],
    ],
    // Protected Routes: GET - Require Loggedin, Role
    'protected' => [
        'dashboard' => [
            'file' => 'dashboard.php',
            'role' => 'viewer',
        ],
        'users' => [
            'file' => 'manage-users.php',
            'role' => 'admin',
        ],
        'logout' => [
            'controller' => 'AuthController',
            'action' => 'logout',
            'role' => 'viewer',
        ],
        'naans' => [
            'file' => 'manage-naans.php',
            'role' => 'admin',
        ],
        'shoulders' => [
            'file' => 'manage-shoulders.php',
            'role' => 'admin',
        ],
        'arks' => [
            'file' => 'manage-arks.php',
            'role' => 'user',
        ],
        'error404' => [
            'file' => 'error-404.php',
            'role' => 'viewer',
        ],
        // Actions: POST, etc - Require Loggedin, Role
        'login' => [
            'controller' => 'AuthController',
            'action' => 'login',
            'role' => 'viewer',
        ],
        'add-user' => [
            'controller' => 'UserController',
            'action' => 'store',
            'role' => 'admin',
        ],
    ],
];
