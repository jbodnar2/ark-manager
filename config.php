<?php

return [
    'app' => [
        'name' => getenv('APP_NAME') ?: 'ARK Manager',
        'env' => getenv('APP_ENV') ?: 'production',
        'debug' => filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN),
        'timezone' => 'America/New_York',
        'root' => __DIR__,
        'https_only' => filter_var(
            getenv('APP_HTTPS_ONLY'),
            FILTER_VALIDATE_BOOLEAN,
        ),
        'domains' => [
            'manager' => getenv('APP_DOMAIN'),
            'resolver' => getenv('RESOLVER_DOMAIN'),
        ],
    ],
    'db' => [
        'dir' => getenv('DB_DIR') ?: 'database',
        'name' => getenv('DB_NAME') ?: 'arks-db.sqlite',
    ],
    'analytics' => [
        'enabled' => filter_var(
            getenv('ANALYTICS_ENABLED'),
            FILTER_VALIDATE_BOOLEAN,
        ),
    ],
    'session' => [
        'lifetime' => 86400, // 24 hours
        'name' => 'ARK_RESOLVER_SESSION',
    ],
    'salts' => [
        'analytics' => getenv('ANALYTICS_SALT'),
        'password' => getenv('PASSWORD_SALT'),
    ],
];
