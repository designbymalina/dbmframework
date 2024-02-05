<?php
/*
 * Application: DbM Framework v1.2
 * Author: Arthur Malinowsky (Design by Malina)
 * License: MIT
 * Web page: www.dbm.org.pl
 * Contact: biuro@dbm.org.pl
*/

declare(strict_types=1);

namespace Dbm\Classes;

use Dbm\Classes\ExceptionHandler;

class Router
{
    protected $routes = [];

    public function addRoute(string $route, array $arrayController): void
    {
        $arrayControllerAction = $this->changeArrayKey($arrayController, ['controller', 'action']);
        $this->routes[$route] = $arrayControllerAction;
    }

    public function dispatch(string $uri): void
    {
        $haystack = APP_PATH;
        $needle = 'localhost';

        if (strpos($haystack, $needle) !== false) {
            $folder = substr($haystack, strpos($haystack, $needle) + strlen($needle));
            $uri = str_replace($folder, '/', $uri);
        }

        if (strpos($uri, '?') !== false) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        if (strpos($uri, ',') !== false) {
            $uri = substr($uri, 0, 1) . substr($uri, strpos($uri, ',') + 1);
        }

        if (array_key_exists($uri, $this->routes)) {
            $controller = $this->routes[$uri]['controller'];
            $action = $this->routes[$uri]['action'];

            $controller = new $controller();
            $controller->$action();
        } else {
            throw new ExceptionHandler("No route found! addRoute('$uri')", 404);
        }
    }

    private function changeArrayKey(array $array, array $keys): array
    {
        foreach ($array as $key => $value) {
            $newArray[$keys[$key]] = $value;
        }

        return $newArray;
    }
}
