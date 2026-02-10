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
    private ?string $currentPermission = null;

    public function __construct(
        private readonly RouteCollection $routes
    ) {}

    public function group(string $prefix, callable $callback): void
    {
        $previous = $this->groupPrefix;

        $this->groupPrefix = $this->normalizePath(
            $this->groupPrefix,
            $prefix
        );

        $callback($this);

        $this->groupPrefix = $previous;
    }

    public function get(string $path, array $handler, ?string $name = null): void
    {
        $this->add('GET', $path, $handler, $name);
    }

    public function post(string $path, array $handler, ?string $name = null): void
    {
        $this->add('POST', $path, $handler, $name);
    }

    public function put(string $path, array $handler, ?string $name = null): void
    {
        $this->add('PUT', $path, $handler, $name);
    }

    public function patch(string $path, array $handler, ?string $name = null): void
    {
        $this->add('PATCH', $path, $handler, $name);
    }

    public function delete(string $path, array $handler, ?string $name = null): void
    {
        $this->add('DELETE', $path, $handler, $name);
    }

    public function any(string $path, array $handler, ?string $name = null): void
    {
        foreach (['GET','POST','PUT','PATCH','DELETE'] as $method) {
            $this->add($method, $path, $handler, $name);
        }
    }

    public function guard(string $permission, callable $callback): void
    {
        $previous = $this->currentPermission;
        $this->currentPermission = $permission;

        $callback($this);

        $this->currentPermission = $previous;
    }

    private function add(string $method, string $path, array $handler, ?string $name): void
    {
        $uri = $this->normalizePath($this->groupPrefix, $path);

        $this->routes->add(
            Route::fromMethod(
                $method,
                $uri,
                $handler,
                $name,
                $this->currentPermission
            )
        );
    }


    private function normalizePath(string $prefix, string $path): string
    {
        $full = rtrim($prefix, '/') . '/' . ltrim($path, '/');
        return '/' . trim($full, '/');
    }
}
