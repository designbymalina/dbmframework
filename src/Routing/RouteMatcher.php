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

use Dbm\Infrastructure\Log\Logger;

final class RouteMatcher
{
    public function __construct(
        private readonly RouteCollection $routes,
        private readonly Logger $logger
    ) {}

    /**
     * @return array{0: Route, 1: array<string, string>}|null
     */
    public function match(string $uri, string $method): ?array
    {
        try {
            // static
            if ($route = $this->routes->matchStatic($method, $uri)) {
                return [$route, $route->getDefaults()];
            }

            // dynamic
            return $this->routes->matchDynamic($method, $uri);
        } catch (\Throwable $e) {
            $this->logger->critical('Route matching error: ' . $e->getMessage());
        }

        return null;
    }
}
