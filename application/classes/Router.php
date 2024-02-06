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
    private $database;

    public function __construct(Database $database)
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
        
        $uri = $this->matchLocalhost($uri);
        $uri = $this->matchRoute($uri);

        if (array_key_exists($uri, $this->routes)) {
            $controller = $this->routes[$uri]['controller'];
            $method = $this->routes[$uri]['method'];

            if (class_exists($controller)) {
                $controllerInstance = new $controller($database);

                if (method_exists($controllerInstance, $method)) {
                    $controllerInstance->$method();
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

    private function matchLocalhost(string $uri): ?string
    {
        $haystack = APP_PATH;
        $needle = 'localhost';

        if (strpos($haystack, $needle) !== false) {
            $folder = substr($haystack, strpos($haystack, $needle) + strlen($needle));
            
            return str_replace($folder, '/', $uri);
        }

        return null;
    }

    private function matchRoute(string $uri): string
    {
        $path = filter_var($uri, FILTER_SANITIZE_URL);
        $path = ltrim($path, '/');
        $path = explode("/", $path);

        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        /*if (strpos($uri, ',') !== false) {
            $uri = substr($uri, 0, 1) . substr($uri, strpos($uri, ',') + 1);
        }*/

        /* $parts = explode(',', $uri);
        $uri = '/' . array_pop($parts); */

        foreach($path as $index => $param){
            if(preg_match("/{.*}/", $param)){
                $indexNum[] = $index;
            }
        }

        //print_r($indexNum);

        /*if ((strpos($uri, '-')) !== false) {
            //$parts = explode('/', $uri);
            //$last = array_pop($parts);
            //echo ' | '. $last;

            $link = str_replace(['/', '.html'], '', $uri);
            $segments = explode(',', str_replace('/', '', $link));

            foreach ($segments as $key => $value) {
                if (is_numeric($value)) {
                    $segments[$key] = '{$}';
                }
                if ((strpos($value, '-')) !== false) {
                    $segments[$key] = '{#}';
                }
            }

            //$uri = '/' . $path[0] . '/' . implode(',', $segments) . '.html';
            $uri = '/' . implode(',', $segments) . '.html';

            //echo $uri;
        }*/

        return $uri;
    }

    /* private function requestMethod() : string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    private function requestPath(): string
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    } */
}
