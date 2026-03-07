<?php

return [
    'public' => [
        '' => ['file' => 'login.php'],
        'login' => ['file' => 'login.php'],
    ],
    'protected' => [
        'dashboard' => [
            'file' => 'dashboard.php',
            'role' => 'viewer',
        ],
        'users' => [
            'file' => 'manage-users.php',
            'role' => 'admin',
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
    ],
];
