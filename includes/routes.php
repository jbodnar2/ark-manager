<?php

return [
    'public' => [
        '' => [
            'GET' => [
                'controller' => 'AuthController',
                'action' => 'getView',
            ],
            'POST' => ['controller' => 'AuthController', 'action' => 'login'],
        ],
        'error404' => ['file' => 'error-404.php'],
    ],
    'protected' => [
        'dashboard' => [
            'GET' => [
                'controller' => 'DashboardController',
                'action' => 'getView',
                'role' => 'viewer',
            ],
        ],
        'users' => [
            'GET' => [
                'controller' => 'UserController',
                'action' => 'getView',
                'role' => 'admin',
            ],
        ],
        'users/add' => [
            'POST' => [
                'controller' => 'UserController',
                'action' => 'addUser',
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
        'user/genrate-token' => [
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
