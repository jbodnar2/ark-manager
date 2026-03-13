<?php
// declare(strict_types=1);

// /**
//  * Ensures a CSRF token exists in the session.
//  * Generates one if it is missing.
//  */
// function ensure_csrf_token(): void
// {
//     if (empty($_SESSION['csrf_token'])) {
//         $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
//     }
// }

// /**
//  * Generates the HTML for a hidden CSRF input field.
//  */
// function csrf_field($echo = false): string
// {
//     $token = $_SESSION['csrf_token'] ?? '';
//     $field =
//         '<input type="hidden" name="csrf_token" value="' .
//         htmlspecialchars($token) .
//         '">';

//     if ($echo) {
//         print $field;
//     }

//     return $field;
// }

// /**
//  * Validates the CSRF token for POST requests.
//  * Terminates execution if the token is missing or invalid.
//  */
// function validate_csrf(): void
// {
//     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//         $submitted = $_POST['csrf_token'] ?? '';
//         $stored = $_SESSION['csrf_token'] ?? '';

//         if (empty($stored) || !hash_equals($stored, $submitted)) {
//             // Set a temporary message for the user
//             $_SESSION['error_message'] =
//                 'Session expired. Please log in again.';

//             // Send them back to the login page
//             header('Location: /login');
//             exit();
//         }
//         // If validation succeeded, regenerate for anti-replay
//         $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
//     }
// }

function logInfo($data): void
{
    $logFile = __DIR__ . '/../logs/debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $entry =
        "[$timestamp] " .
        (is_string($data) ? $data : print_r($data, true)) .
        PHP_EOL;
    file_put_contents($logFile, $entry, FILE_APPEND);
}
