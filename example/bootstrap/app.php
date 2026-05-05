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
use Dbm\Routing\RouteBuilder;

return function (): Application {
    // ===== Dependency Injection Container =====
    $container = new DependencyContainer();

    // ===== Register Core Services =====
    (require __DIR__ . '/services.php')($container);
    ;

    // ===== Routes =====
    $routeBuilder = $container->get(RouteBuilder::class);

    // --- Claass & Route
    require __DIR__ . '/controller.php';

    $routeBuilder->get('/', [HelloController::class, 'index']);

    // ===== Application =====
    return new Application($container);
};
