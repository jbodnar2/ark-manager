<?php

return [
    'public' => [
        '' => ['file' => 'login.php'],
        'login' => [
            'file' => 'login.php',
            'controller' => 'AuthController',
            'action' => 'login',
        ],
        'error404' => ['file' => 'error-404.php'],
    ],
    'protected' => [
        'dashboard' => ['file' => 'dashboard.php', 'role' => 'viewer'],
        'users' => ['file' => 'manage-users.php', 'role' => 'admin'],
        'logout' => [
            'controller' => 'AuthController',
            'action' => 'logout',
            'role' => 'viewer',
        ],
        'naans' => ['file' => 'manage-naans.php', 'role' => 'admin'],
        'shoulders' => ['file' => 'manage-shoulders.php', 'role' => 'admin'],
        'arks' => ['file' => 'manage-arks.php', 'role' => 'user'],
        'add-user' => [
            'controller' => 'UserController',
            'action' => 'store',
            'role' => 'admin',
        ],
    ],
];
