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

use Dbm\Core\DependencyContainer;
use Dbm\Http\Message\Request;
use ReflectionMethod;
use ReflectionParameter;

final class ActionArgumentResolver
{
    public function __construct(
        private readonly DependencyContainer $container,
        private readonly Request $request
    ) {}

    public function resolve(Route $route, object $controller): array
    {
        $method = new \ReflectionMethod($controller, $route->action);
        $args   = [];

        $routeParams = RoutingContext::getRouteParams();

        foreach ($method->getParameters() as $param) {
            $args[] = $this->resolveParameter($param, $routeParams);
        }

        return $args;
    }

    private function resolveParameter(
        \ReflectionParameter $param,
        array $routeParams
    ): mixed {
        $name = $param->getName();
        $type = $param->getType();

        // Parametry z URL
        if (array_key_exists($name, $routeParams)) {
            return $routeParams[$name];
        }

        // Request
        if ($type && !$type->isBuiltin()) {
            $class = $type->getName();

            if ($class === Request::class) {
                return $this->request;
            }

            // DI
            if ($this->container->has($class)) {
                return $this->container->get($class);
            }

            // fallback
            if (class_exists($class)) {
                return new $class();
            }
        }

        // Default
        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        throw new \RuntimeException(
            "Cannot resolve argument \${$name}"
        );
    }
}
