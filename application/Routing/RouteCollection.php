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

use Dbm\Exceptions\ExceptionHandler;

final class RouteCollection
{
    private array $static = [];
    private array $dynamic = [];
    private array $named = [];

    public function add(Route $route): void
    {
        $method = $route->httpMethod;

        // OVERRIDE BY NAME - last wins
        if ($route->name !== null && isset($this->named[$route->name])) {
            $old = $this->named[$route->name];

            // usuń starą trasę statyczną
            unset($this->static[$old->httpMethod][$old->path]);

            // usuń starą trasę dynamiczną
            if (isset($this->dynamic[$old->httpMethod])) {
                $this->dynamic[$old->httpMethod] = array_filter(
                    $this->dynamic[$old->httpMethod],
                    fn($r) => $r !== $old
                );
            }

            unset($this->named[$route->name]);
        }

        // Dodaj nową trasę
        if ($route->isStatic()) {
            $this->static[$method][$route->path] = $route;
        } else {
            $this->dynamic[$method][] = $route;
        }

        if ($route->name !== null) {
            $this->named[$route->name] = $route;
        }
    }

    public function matchStatic(string $method, string $uri): ?Route
    {
        return $this->static[$method][$uri] ?? null;
    }

    public function matchDynamic(string $method, string $uri): ?Route
    {
        foreach ($this->dynamic[$method] ?? [] as $route) {
            if (!preg_match($route->getCompiledPattern(), $uri, $matches)) {
                continue;
            }

            array_shift($matches);

            if ($params = $route->getParamNames()) {
                RoutingContext::setRouteParams(
                    array_combine($params, $matches)
                );
            }

            return $route;
        }

        return null;
    }

    public function getNamedRoutes(): array
    {
        return $this->named;
    }

    public function getByName(string $name): Route
    {
        return $this->named[$name]
            ?? throw new ExceptionHandler("Route '{$name}' not found.", 500);
    }

    ### ===== GUARD SUPPORT ===== ###

    public function snapshot(): array
    {
        return [
            'static' => $this->static,
            'dynamic' => $this->dynamic,
        ];
    }

    public function applyPermissionDiff(array $before, string $permission): void
    {
        foreach (['static', 'dynamic'] as $type) {
            foreach ($this->{$type} as $method => $routes) {
                $previous = $before[$type][$method] ?? [];

                foreach ($routes as $key => $route) {
                    if (!isset($previous[$key])) {
                        $route->permission = $permission;
                    }
                }
            }
        }
    }

    public function hasPath(string $uri): bool
    {
        // static
        foreach ($this->static as $routes) {
            if (isset($routes[$uri])) {
                return true;
            }
        }

        // dynamic
        foreach ($this->dynamic as $routes) {
            foreach ($routes as $route) {
                if (preg_match($route->getCompiledPattern(), $uri)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function allowedMethods(string $uri): array
    {
        $methods = [];

        // static
        foreach ($this->static as $method => $routes) {
            if (isset($routes[$uri])) {
                $methods[] = $method;
            }
        }

        // dynamic
        foreach ($this->dynamic as $method => $routes) {
            foreach ($routes as $route) {
                if (preg_match($route->getCompiledPattern(), $uri)) {
                    $methods[] = $method;
                    break;
                }
            }
        }

        return array_values(array_unique($methods));
    }

    ### ===== CACHE ===== ###

    public function export(): array
    {
        $out = [
            'static' => [],
            'dynamic' => [],
        ];

        foreach ($this->static as $method => $routes) {
            foreach ($routes as $path => $route) {
                $out['static'][$method][$path] = $route->toArray();
            }
        }

        foreach ($this->dynamic as $method => $routes) {
            foreach ($routes as $route) {
                $out['dynamic'][$method][] = $route->toArray();
            }
        }

        return $out;
    }

    public function import(array $data): void
    {
        $this->static = [];
        $this->dynamic = [];
        $this->named = [];

        foreach ($data['static'] ?? [] as $method => $routes) {
            foreach ($routes as $path => $routeData) {
                $this->add(Route::fromArray($routeData));
            }
        }

        foreach ($data['dynamic'] ?? [] as $method => $routes) {
            foreach ($routes as $routeData) {
                $this->add(Route::fromArray($routeData));
            }
        }
    }
}
