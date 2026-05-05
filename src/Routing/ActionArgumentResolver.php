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
use Psr\Http\Message\ServerRequestInterface;
use ReflectionParameter;

final class ActionArgumentResolver
{
    public function __construct(
        private readonly DependencyContainer $container
    ) {}

    /**
     * @return array<int, mixed>
     */
    public function resolve(Route $route, object $controller, ServerRequestInterface $request): array
    {
        $method = new \ReflectionMethod($controller, $route->action);
        $args = [];

        $routeParams = $request->getAttribute('route_params', []);

        foreach ($method->getParameters() as $param) {
            $args[] = $this->resolveParameter($request, $param, $routeParams);
        }

        return $args;
    }

    /**
     * @param array<string, mixed> $routeParams
     */
    private function resolveParameter(
        ServerRequestInterface $request,
        ReflectionParameter $param,
        array $routeParams
    ): mixed {
        $name = $param->getName();
        $type = $param->getType();

        // route param
        if (array_key_exists($name, $routeParams)) {
            $value = $routeParams[$name];

            if ($type instanceof \ReflectionNamedType && $type->isBuiltin()) {
                return $this->castToBuiltinType($value, $type->getName());
            }

            return $value;
        }

        // PSR-7 request injection
        if ($type instanceof \ReflectionNamedType) {
            $class = $type->getName();

            if ($class === ServerRequestInterface::class) {
                return $request;
            }

            if ($this->container->has($class)) {
                return $this->container->get($class);
            }
        }

        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        throw new \RuntimeException("Cannot resolve argument \${$name}");
    }

    private function castToBuiltinType(mixed $value, string $type): mixed
    {
        return match ($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOL),
            'string' => (string) $value,
            default => $value,
        };
    }
}
