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
use Exception;

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
                    try {
                        call_user_func_array([$controllerInstance, $method], $this->matchParams());
                    } catch(Exception $exception) {
                        throw new ExceptionHandler($exception->getMessage(), $exception->getCode());
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

        if ((strpos($uri, ',')) !== false) {
            $explode = explode('.', $uri);
            $ext = array_pop($explode);

            (!empty($ext)) ? $ext = '.' . $ext : $ext = '';

            $link = str_replace(['/', '.php', '.html'], '', $uri);
            $segments = explode(',', str_replace('/', '', $link));

            foreach ($segments as $key => $value) {
                if (is_numeric($value)) {
                    $segments[$key] = '{$}';
                } elseif ((strpos($value, '-')) !== false) { // TODO! If not by sign
                    $segments[$key] = '{#}';
                }
            }

            $dir = '';
            $count = count($path);

            if ($count > 1) {
                for ($i = 0; $i < $count - 1; $i++) {
                    $dir .= '/' . $path[$i];
                }
            }

            $uri = $dir . '/' . implode(',', $segments) . $ext;
        }

        return $uri;
    }

    private function matchParams(): array
    {
        $params = [];

        if (!empty($_GET)) {
            foreach ($_GET as $key => $value) {
                if ($key == 'url') {
                    continue;
                }
                $params[$key] = $value;
            }
        }

        return $params;
    }
}
