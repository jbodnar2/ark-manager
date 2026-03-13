<?php
declare(strict_types=1);

namespace App\Core;

class PathHelper
{
    public static function getCleanPath(
        string $uri,
        ?string $fallback = null,
    ): ?string {
        $path = parse_url($uri, PHP_URL_PATH);

        if (!is_string($path)) {
            return $fallback;
        }

        $clean = trim(rawurldecode($path), '/');

        if (str_contains($clean, "\0") || strlen($clean) > 2048) {
            return $fallback;
        }

        return $clean;
    }

    public static function getVerifiedPagePath(
        string $root,
        string $filename,
        string $error_filename = '404.php',
    ): string {
        // Define where error views live
        $error_dir = $root . '/app/Views/errors/';

        $page_path = $error_dir . $filename;
        $fallback_path = $error_dir . $error_filename;

        if (is_file($page_path) && is_readable($page_path)) {
            return $page_path;
        }

        return $fallback_path;
    }
}
