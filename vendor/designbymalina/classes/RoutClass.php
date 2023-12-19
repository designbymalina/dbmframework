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

use Dbm\Classes\ExceptionClass as DbmException;

class RoutClass
{
    // Default controller, method, params
    private $controller = "IndexController";
    private $method = "Index";
    private $params = [];

    public function __construct()
    {
        $path = BASE_DIRECTORY.'application'.DS.'Controller'.DS;
        $url = $this->parseUrl();

        // ### CONTROLLERS
        if (!empty($url)) {
            $controllerName = ucfirst($url[0]).'Controller'; // The static part of the controller name

            if (file_exists($path . $controllerName . '.php')) {
                $this->controller = $controllerName;
                unset($url[0]);
            } else {
                throw new DbmException('Controller file ' . $controllerName . '.php is required. File not found!', 404);
            }
        }

        // Include controller; * INFO: Composer autoload
        // require_once($path . $this->controller . '.php');

        // Instantiate controller
        $controllerNamespace = 'App\\Controller\\' . $this->controller;
        $this->controller = new $controllerNamespace();

        // ### METHODS
        if (!empty($url[1])) {
            $methodName = 'Method'; // The static part of the method name

            $exp = explode('-', $url[1]);
            $methodName = $exp[0] . $methodName;

            if (method_exists($this->controller, $methodName)) {
                $this->method = $methodName;
                unset($url[1]);
            } else {
                throw new DbmException($methodName. ' in ' . $controllerName . '.php is required. Method not found!');
            }
        }

        // ### PARAMS
        if (isset($url)) {
            $this->params = $url;
        } else {
            $this->params = [];
        }

        // Callback with array parameters
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    // INFO: Problem -> get values = http/s, see to .htaccess?
    public function parseUrl()
    {
        if (isset($_GET['url'])) {
            $url = $_GET['url'];
            $uri = $_SERVER['REQUEST_URI'];

            $path = str_replace(array('.php', '.html'), '', $url);
            $path = rtrim($path);
            $path = filter_var($path, FILTER_SANITIZE_URL);
            $path = explode('/', $path);

            if ((strpos($uri, 'site.html') == true) || (strpos($uri, 'offer.html') == true)) { // TODO! Mozna sprobowac dopracowac
                $method = substr($uri, strrpos($uri, ',') + 1);
                $param = str_replace(array('.php', '.html'), '', $method);

                if (strpos($param, '?')) {
                    $param = substr($param, 0, strpos($param, '?'));
                }

                if (is_numeric($param)) {
                    $param = strstr($url, ',', true);
                }

                $key = array_key_last($path);
                $path[$key] = $param;
            }

            return $path;
        }
    }
}
