<?php
declare(strict_types=1);

if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
    header('Location: /login');
    exit();
}

$is_verified_ip =
    ($_SERVER['REMOTE_ADDR'] ?? 'unknown') === ($_SESSION['user']['ip'] ?? '');

if (!$is_verified_ip) {
    $_SESSION = [];
    session_destroy();
    header('Location: /login');
    exit();
}
