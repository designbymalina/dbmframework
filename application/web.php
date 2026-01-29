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
 * DOCUMENTATION: Examples can be found in the README documentation -> Routing
 */

declare(strict_types=1);

use App\Controller\IndexController;
use Dbm\Core\DependencyContainer;
use Dbm\Routing\RouteBuilder;

return function (RouteBuilder $routes, DependencyContainer $container): void {
    // Index routes
    $routes->get('/', [IndexController::class, 'index'], 'home');
    $routes->get('/start', [IndexController::class, 'start'], 'start');
};
