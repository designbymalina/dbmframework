<?php
/*
 * Application: DbM Framework v2.1
 * Author: Arthur Malinowsky (Design by Malina)
 * License: MIT
 * Web page: www.dbm.org.pl
 * Contact: biuro@dbm.org.pl
*/

declare(strict_types=1);

namespace Dbm\Classes;

use Dbm\Classes\ExceptionHandler;
use Dbm\Interfaces\DatabaseInterface;
use Dbm\Interfaces\RouterInterface;

class Router implements RouterInterface
{
    private const ADDRESS_EXTENSION = '.html';
    private const ADDRESS_DIVIDER = '.';

    protected $routes = [];
    private $database;

    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    public function addRoute(string $route, array $arrayController): void
    {
        $arrayControllerAction = $this->changeArrayKey($arrayController, ['controller', 'method']);
        $this->routes[$route] = $arrayControllerAction;
    }

    public function dispatch(string $uri): void
    {
        $database = $this->database;

        $uri = $this->matchDomain($uri);
        $route = $this->matchRoute($uri);
        $uri = $route['uri'];
        $hasParams = false;

        if (!array_key_exists($uri, $this->routes)) {
            if (!empty($route['params'])) {
                $hasParams = true;
                $uri = $this->buildRouteUri($route['paths'], $route['params']);
            }
        }

        if (array_key_exists($uri, $this->routes)) {
            $controller = $this->routes[$uri]['controller'];
            $method = $this->routes[$uri]['method'];

            if (class_exists($controller)) {
                $controllerInstance = new $controller($database);

                if (method_exists($controllerInstance, $method)) {
                    if ($hasParams) {
                        $controllerInstance->$method((int)end($route['params']));
                    } else {
                        $controllerInstance->$method();
                    }
                } else {
                    throw new ExceptionHandler("No method $method on class $controller!", 500);
                }
            } else {
                throw new ExceptionHandler("No controller $controller!", 500);
            }
        } else {
            throw new ExceptionHandler("Route not found! addRoute('$uri')", 404);
        }
    }

    private function changeArrayKey(array $array, array $keys): array
    {
        foreach ($array as $key => $value) {
            $newArray[$keys[$key]] = $value;
        }

        return $newArray;
    }

    private function buildRouteUri(array $paths, array $params): string
    {
        $paramsLength = count($params);

        if (!is_numeric($params[0]) && $paramsLength > 2) { // pattern /{#}.sec.{id}.html itp
            $params[0] = '{#}';
        }

        if (!is_numeric($params[0]) && !is_numeric(end($params))) { // pattern /{#}.offer.html
            $params[0] = '{#}';
        }

        if (is_numeric(end($params))) { // pattern /user.{id}.html
            $params[$paramsLength - 1] = '{id}';
        }

        if (!empty($paths)) {
            $paths = '/'.implode('/', $paths). '/';
        } else {
            $paths = '/';
        }

        return $paths . implode(self::ADDRESS_DIVIDER, $params) . self::ADDRESS_EXTENSION;
    }

    private function matchRoute(string $uri): array
    {
        $path = filter_var($uri, FILTER_SANITIZE_URL);
        $path = ltrim($path, '/');
        $path = explode("/", $path);

        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        $paths = [];
        $params = [];

        foreach ($path as $subPath) {
            if (strpos($subPath, self::ADDRESS_EXTENSION) !== false) {
                $subPath = str_replace(self::ADDRESS_EXTENSION, '', $subPath);
                $params = explode('.', $subPath);

                if (end($params) === $subPath) {
                    $params = [];
                    break;
                }

                $param = end($params);

                if (($pos = strpos($param, self::ADDRESS_EXTENSION)) !== false) {
                    $params[count($params) - 1] = substr($param, 0, $pos);
                }
            } else {
                $paths[] = $subPath;
            }
        }

        return [
            'uri' => $uri,
            'paths' => $paths,
            'params' => $params
        ];
    }

    /* Method for localhost and application in catalog */
    private function matchDomain(string $uri): string
    {
        $dir = dirname($_SERVER['PHP_SELF']);
        $host = $_SERVER['HTTP_HOST'] . $dir;
        $host = rtrim($host, '\\');

        if (strpos($host, '.') === false) { // domain recognition by dot
            $path = strstr($dir, 'public', true);
            $uri = str_replace($path, '', $uri);

            return '/' . $uri;
        }

        return $uri;
    }
}
