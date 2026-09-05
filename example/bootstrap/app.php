<?php

/**
 * DBM Framework
 *
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

use Dbm\Application;
use Dbm\Core\DependencyContainer;
use Dbm\Routing\MiddlewareStack;
use Dbm\Routing\RouteBuilder;

return function (): Application {
    // ===== Dependency Injection Container =====
    $container = new DependencyContainer();

    // ===== Register Core Services =====
    (require __DIR__ . '/services.php')($container);

    // ===== Routes =====
    $routeBuilder = $container->get(RouteBuilder::class);

    (require __DIR__ . '/routes.php')($routeBuilder);

    // ===== Middleware (@NOTE Toolbar for example) =====
    $middleware = $container->get(MiddlewareStack::class);
    (require __DIR__ . '/middleware.php')($middleware, $container);

    // ===== Application =====
    return new Application($container);
};
