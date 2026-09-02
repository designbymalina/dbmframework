<?php

/**
 * DBM Framework
 *
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

use App\Controller\HelloApiController;
use App\Controller\HelloController;
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

    // --- Controllers (@NOTE Only for example) ---
    require __DIR__ . '/../src/Controller/HelloController.php';
    require __DIR__ . '/../src/Controller/HelloApiController.php';

    // --- Web ---
    $routeBuilder->get('/', [HelloController::class, 'index'], 'home');

    // --- API ---
    $routeBuilder->get('/api/hello', [HelloApiController::class, 'index'], 'api_hello');

    // ===== Middleware (@NOTE Toolbar for example) =====
    $middleware = $container->get(MiddlewareStack::class);
    (require __DIR__ . '/middleware.php')($middleware, $container);

    // ===== Application =====
    return new Application($container);
};
