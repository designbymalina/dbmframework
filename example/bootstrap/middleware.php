<?php

/**
 * DBM Framework
 *
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

use Dbm\Debug\DebugToolbarMiddleware;
use Dbm\Middleware\ExceptionMiddleware;
use Dbm\Middleware\RequestToolbarEndMiddleware;
use Dbm\Middleware\RequestToolbarStartMiddleware;
use Dbm\Middleware\RouterMatchMiddleware;
use Dbm\Routing\MiddlewareStack;

return function (MiddlewareStack $middleware, $container): void {
    // --- START
    $middleware->add(new RequestToolbarStartMiddleware());

    // --- EXCEPTION
    $middleware->add($container->get(ExceptionMiddleware::class));

    // --- ROUTING
    $middleware->add($container->get(RouterMatchMiddleware::class));

    // --- DEBUG
    $middleware->add($container->get(DebugToolbarMiddleware::class));
    $middleware->add(new RequestToolbarEndMiddleware());
};
