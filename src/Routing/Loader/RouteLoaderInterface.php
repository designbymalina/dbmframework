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
 * Minimalna implementacja
 *
 * Attribute
 *
 * #[\Attribute(\Attribute::TARGET_METHOD)]
 * class Route
 * {
 *     public function __construct(
 *         public string $path,
 *         public string $name,
 *         public array $methods = ['GET']
 *     ) {}
 * }
 *
 * Użycie
 *
 * $loader = new AttributeRouteLoader([
 *     App\Controller\IndexController::class,
 * ]);
 *
 * $loader->load($routes);
 *
 * @INFO Interfejs nie używany, można wdrożyć z AttributeRouteLoader
 */

declare(strict_types=1);

namespace Dbm\Routing\Loader;

use Dbm\Routing\RouteCollection;

interface RouteLoaderInterface
{
    public function load(RouteCollection $routes): void;
}
