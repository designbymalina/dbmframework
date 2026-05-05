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
 * @TODO Attribute routing (future feature):
 * - Cache refleksji (wydajność)
 * - Walidacja metod HTTP
 * - Prefixy controllerów (np. #[Route('/admin')])
 * - Grupowanie tras
 * - Named routes fallback
 *
 * @INFO Klasa nie używana, można wdrożyć atrybuty, wówczas:
 *
 * use Dbm\Routing\Attribute\RouteAttribute;
 * class IndexController
 * {
 *     #[Route('/start', name: 'start', methods: ['GET'])]
 *     public function start(): ResponseInterface
 *     {
 *         ...
 *     }
 * }
 *
 * Wersja z atrybutami może być w 100% kompatybilna z dotychczasową wersją.
 * Zasada: Atrybuty to tylko dodatkowe źródło definicji routingu.
 * Przyszły flow powinien wyglądać tak:
 * $routeCollection = new RouteCollection();
 * // 1. ręczne (jak teraz)
 * (require 'routes/web.php')($routes, $container);
 * // 2. atrybuty (opcjonalnie)
 * $attributeLoader->load($routeCollection);
 * Priorytet: jeśli route istnieje -> atrybut ignorowany
 * if ($routes->has($route->name)) {
 *     continue;
 * }
 */

declare(strict_types=1);

namespace Dbm\Routing\Loader;

use Dbm\Routing\Route;
use Dbm\Routing\RouteCollection;
use ReflectionClass;

class AttributeRouteLoader implements RouteLoaderInterface
{
    /**
     * @param array<string, mixed> $controllers
     */
    public function __construct(
        private readonly array $controllers
    ) {}

    public function load(RouteCollection $routes): void
    {
        foreach ($this->controllers as $controllerClass) {
            $this->loadController($routes, $controllerClass);
        }
    }

    private function loadController(RouteCollection $routes, string $controllerClass): void
    {
        $reflection = new ReflectionClass($controllerClass);

        foreach ($reflection->getMethods() as $method) {
            $attributes = $method->getAttributes(Route::class);

            // @TODO Route atribute (for feature)
            foreach ($attributes as $attribute) {
                /** @var \Dbm\Routing\Attribute\RouteAttribute $route */
                $route = $attribute->newInstance();

                // if (!$route instanceof RouteAttribute) {
                //     continue;
                // }

                foreach ($route->methods as $httpMethod) {
                    $routes->add(
                        Route::fromMethod(
                            $httpMethod,
                            $route->path,
                            [$controllerClass, $method->getName()],
                            $route->name
                        )
                    );
                }
            }
        }
    }
}
