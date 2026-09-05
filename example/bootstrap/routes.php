<?php

/**
 * DBM Framework
 *
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

use App\Controller\HelloApiController;
use App\Controller\WelcomeController;
use Dbm\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {
    // --- Controllers (@NOTE Only for example) ---
    require __DIR__ . '/../src/Controller/WelcomeController.php';
    require __DIR__ . '/../src/Controller/HelloApiController.php';

    // --- Web ---
    $routes->get('/', [WelcomeController::class, 'index'], 'home');

    // --- API ---
    $routes->get('/api/hello', [HelloApiController::class, 'index'], 'api_hello');
};
