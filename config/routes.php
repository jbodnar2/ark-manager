<?php

return [
    'public' => [
        '' => [
            'GET' => [
                'controller' => 'AuthController',
                'action' => 'index',
            ],
            'POST' => ['controller' => 'AuthController', 'action' => 'login'],
        ],
        'error404' => ['file' => '404.php'],
    ],
    'protected' => [
        'dashboard' => [
            'GET' => [
                'controller' => 'DashboardController',
                'action' => 'index',
                'role' => 'viewer',
            ],
        ],
        'users' => [
            'GET' => [
                'controller' => 'UserController',
                'action' => 'index',
                'role' => 'admin',
            ],
            'POST' => [
                'controller' => 'UserController',
                'action' => 'store',
                'role' => 'admin',
            ],
        ],
        // ---
        // Direct to the "Add New User" form (currently using a dialog)
        // ---
        // 'users/create' => [
        //     'GET' => [
        //         'controller' => 'UserController',
        //         'action' => 'create',
        //         'role' => 'admin',
        //     ],
        // ],
        'users/show' => [
            'GET' => [
                'controller' => 'UserController',
                'action' => 'show',
                'role' => 'admin',
            ],
        ],
        'user/revoke-token' => [
            'POST' => [
                'controller' => 'UserController',
                'action' => 'revokeUserToken',
                'role' => 'admin',
            ],
        ],
        'user/generate-token' => [
            'POST' => [
                'controller' => 'UserController',
                'action' => 'generateUserToken',
                'role' => 'admin',
            ],
        ],
        'api/user' => [
            'GET' => [
                'controller' => 'UserController',
                'action' => 'getUserJSON',
                'role' => 'admin',
            ],
        ],
        'logout' => [
            'POST' => ['controller' => 'AuthController', 'action' => 'logout'],
        ],
    ],
];

// return [
//     'public' => [
//         '' => [
//             'controller' => 'AuthController',
//             'action' => 'getViewForm',
//         ],
//         'login' => [
//             'controller' => 'AuthController',
//             'action' => 'login',
//         ],
//         'error404' => ['file' => 'error-404.php'],
//     ],
//     'protected' => [
//         'dashboard' => ['file' => 'dashboard.php', 'role' => 'viewer'],
//         // 'users' => ['file' => 'manage-users.php', 'role' => 'admin'],
//         'users' => [
//             'controller' => 'UserController',
//             'action' => 'index',
//             'role' => 'admin',
//         ],
//         'logout' => [
//             'controller' => 'AuthController',
//             'action' => 'logout',
//             'role' => 'viewer',
//         ],
//         'naans' => ['file' => 'manage-naans.php', 'role' => 'admin'],
//         'shoulders' => ['file' => 'manage-shoulders.php', 'role' => 'admin'],
//         'arks' => ['file' => 'manage-arks.php', 'role' => 'user'],
//         'add-user' => [
//             'controller' => 'UserController',
//             'action' => 'store',
//             'role' => 'admin',
//         ],
//     ],
// ];
