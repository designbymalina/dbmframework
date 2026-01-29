<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 *
 * DOCUMENTATION: Examples can be found in the README documentation -> Middleware
 */

declare(strict_types=1);

use Dbm\Infrastructure\Log\Logger;
use Dbm\Middleware\ApiAuthMiddleware;
use Dbm\Middleware\CorsMiddleware;
use Dbm\Middleware\RequestLoggerMiddleware;
use Dbm\Routing\MiddlewareStack;

return function (MiddlewareStack $middleware): void {
    $logger = new Logger();

    // Global
    $middleware->add(new CorsMiddleware());

    // API-only
    $middleware->add(new ApiAuthMiddleware(), '/api');

    // Optional - Test request time in ms
    // $middleware->add(new RequestLoggerMiddleware($logger));
};
