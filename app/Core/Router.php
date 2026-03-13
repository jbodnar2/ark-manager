<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function resolve(string $path, string $method): ?array
    {
        // 1. Check Public Routes
        if (isset($this->routes['public'][$path][$method])) {
            return $this->routes['public'][$path][$method];
        }

        // 2. Check Protected Routes
        if (isset($this->routes['protected'][$path][$method])) {
            $route = $this->routes['protected'][$path][$method];
            $route['is_protected'] = true;
            return $route;
        }

        // 3. Fallback to 404 if path doesn't exist in either category
        if (!$this->pathExists($path) || $path === 'error404') {
            return $this->routes['public']['error404'] ?? null;
        }

        return null;
    }

    /**
     * Helper to check if a path is defined in the public array.
     */
    public function isPublic(string $path): bool
    {
        return isset($this->routes['public'][$path]);
    }

    /**
     * Internal check to see if the path exists at all.
     */
    private function pathExists(string $path): bool
    {
        return isset($this->routes['public'][$path]) ||
            isset($this->routes['protected'][$path]);
    }
}
