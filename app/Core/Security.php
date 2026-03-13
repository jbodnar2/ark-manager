<?php
namespace App\Core;

class Security
{
    public static function ensureCsrfToken(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public static function csrfField(): string
    {
        $token = $_SESSION['csrf_token'] ?? '';
        return sprintf(
            '<input type="hidden" name="csrf_token" value="%s">',
            htmlspecialchars($token),
        );
    }

    public static function validateCsrf(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }

        $submitted = $_POST['csrf_token'] ?? '';
        $stored = $_SESSION['csrf_token'] ?? '';

        if (empty($stored) || !hash_equals($stored, $submitted)) {
            return false;
        }

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return true;
    }
}
