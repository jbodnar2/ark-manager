<?php
declare(strict_types=1);

/**
 * Handles URI path extraction and route resolution.
 */
class Router
{
    /**
     * Extract, decode, and trim the URI path.
     *
     * @param string $uri The raw URI (from $_SERVER['REQUEST_URI']).
     * @param string|null $fallback The value to return if parsing fails.
     * @return string|null Returns the cleaned path slug or the fallback value.
     */
    public static function getCleanPath(
        string $uri,
        ?string $fallback = null,
    ): ?string {
        $path = parse_url($uri, PHP_URL_PATH);

        if (!is_string($path)) {
            return $fallback;
        }

        $clean = trim(rawurldecode($path), '/');

        if (strpos($clean, "\0") !== false || strlen($clean) > 2048) {
            return $fallback;
        }

        return $clean;
    }

    /**
     * Verifies the existence of a page file and returns the absolute path.
     *
     * @param string $root           The application root directory.
     * @param string $filename       The filename from the routes array.
     * @param string $error_filename The fallback filename if no match found.
     * @return string The full absolute path to the file.
     */
    public static function getVerifiedPagePath(
        string $root,
        string $filename,
        string $error_filename = 'error-404.php',
    ): string {
        $page_path = $root . '/includes/pages/' . $filename;
        $fallback_path = $root . '/includes/pages/' . $error_filename;

        if (is_file($page_path) && is_readable($page_path)) {
            return $page_path;
        }

        return $fallback_path;
    }

    /**
     * Maps a cleaned path slug to a key in the routes array.
     *
     * @param string $path    The cleaned path slug.
     * @param array  $routes  The associative array of valid routes.
     * @param string $default The fallback key if no match is found.
     * @return string
     */
    public static function mapToRoute(
        string $path,
        array $routes,
        string $default = 'error404',
    ): string {
        return array_key_exists($path, $routes) ? $path : $default;
    }
}
