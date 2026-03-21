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
 * DOCUMENTATION: Examples can be found in the README documentation
 * -> Application Programming Interface (API)
 */

declare(strict_types=1);

use App\Controller\Api\ExampleApiController;
use App\Controller\Api\IndexApiController;
use Dbm\Core\DependencyContainer;
use Dbm\Core\Module\ModuleRegistry;
use Dbm\Routing\RouteBuilder;

return function (RouteBuilder $routes, DependencyContainer $container): void {
    $routes->get('/', [IndexApiController::class, 'index'], 'api_index');

    $routes->group('/example', function (RouteBuilder $routes): void {
        $routes->get('/', [ExampleApiController::class, 'list'], 'api_example_list');
        // $routes->get('/{id}', [ExampleApiController::class, 'get'], 'api_example_get');
        // $routes->post('/', [ExampleApiController::class, 'create'], 'api_example_create');
        // $routes->put('/{id}', [ExampleApiController::class, 'update'], 'api_example_update');
        // $routes->delete('/{id}', [ExampleApiController::class, 'delete'], 'api_example_delete');
    });

    # TODO! Module routes, albo osobny interfejs ApiModuleInterface ?
    $moduleRegistry = $container->get(ModuleRegistry::class);

    foreach ($moduleRegistry->all() as $module) {
        if (method_exists($module, 'registerApiRoutes')) {
            $module->registerApiRoutes($routes);
        }
    }
};
