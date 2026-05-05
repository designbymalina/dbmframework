<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

namespace Dbm\Routing;

final class RouteBuilder
{
    private string $groupPrefix = '';
    private ?string $groupPermission = null;

    /** @var string[] */
    private array $groupMiddleware = [];

    public function __construct(
        private readonly RouteCollection $routes
    ) {}

    /**
     * @param string[] $middleware
     */
    public function group(string $prefix, callable $callback, array $middleware = []): void
    {
        $prevPrefix = $this->groupPrefix;
        $prevMiddleware = $this->groupMiddleware;

        $this->groupPrefix = $this->normalizePath($this->groupPrefix, $prefix);
        $this->groupMiddleware = array_merge($this->groupMiddleware, $middleware);

        $callback($this);

        $this->groupPrefix = $prevPrefix;
        $this->groupMiddleware = $prevMiddleware;
    }

    /**
     * @param array{0: string, 1: string} $handler
     */
    public function get(string $path, array $handler, ?string $name = null): Route
    {
        return $this->add('GET', $path, $handler, $name);
    }

    /**
     * @param array{0: string, 1: string} $handler
     */
    public function post(string $path, array $handler, ?string $name = null): Route
    {
        return $this->add('POST', $path, $handler, $name);
    }

    /**
     * @param array{0: string, 1: string} $handler
     */
    public function put(string $path, array $handler, ?string $name = null): Route
    {
        return $this->add('PUT', $path, $handler, $name);
    }

    /**
     * @param array{0: string, 1: string} $handler
     */
    public function patch(string $path, array $handler, ?string $name = null): Route
    {
        return $this->add('PATCH', $path, $handler, $name);
    }

    /**
     * @param array{0: string, 1: string} $handler
     */
    public function delete(string $path, array $handler, ?string $name = null): Route
    {
        return $this->add('DELETE', $path, $handler, $name);
    }

    /**
     * @param array{0: string, 1: string} $handler
     */
    public function any(string $path, array $handler, ?string $name = null): Route
    {
        $route = null;

        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
            $route = $this->add($method, $path, $handler, $name);
        }

        return $route;
    }

    public function permission(string $permission, callable $callback): void
    {
        $previous = $this->groupPermission;
        $this->groupPermission = $permission;

        $callback($this);

        $this->groupPermission = $previous;
    }

    // ===== Private =====

    /**
     * @param array{0: string, 1: string} $handler
     */
    private function add(string $method, string $path, array $handler, ?string $name): Route
    {
        $uri = $this->normalizePath($this->groupPrefix, $path);

        $route = Route::fromMethod(
            $method,
            $uri,
            $handler,
            $name,
            $this->groupPermission
        );

        if (!empty($this->groupMiddleware)) {
            $route->middleware($this->groupMiddleware);
        }

        $this->routes->add($route);

        return $route;
    }

    private function normalizePath(string $prefix, string $path): string
    {
        $full = rtrim($prefix, '/') . '/' . ltrim($path, '/');

        return '/' . trim($full, '/');
    }
}
