<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

namespace Dbm\Routing;

use Dbm\Core\DependencyContainer;
use Dbm\Http\Controller\BaseController;
use Dbm\Http\Message\Request;
use Dbm\Infrastructure\Cookie\CookieManager;
use Dbm\Infrastructure\Session\SessionManager;
use Dbm\Localization\Translation;
use Dbm\Views\TemplateEngine;
use ReflectionClass;
use ReflectionParameter;

final class ControllerResolver
{
    public function __construct(
        private readonly DependencyContainer $container
    ) {}

    /**
     * Resolves the controller and action method for the given route.
     *
     * @param Route $route
     * @return array{0: object, 1: string}
     */
    public function resolve(Route $route): array
    {
        $controller = $this->getControllerInstance($route->controller);
        $method = $route->action;

        if (!method_exists($controller, $method)) {
            throw new \RuntimeException(
                "Method {$method} not found in {$route->controller}"
            );
        }

        $this->injectDependencies($controller);

        return [$controller, $method];
    }

    ### Controller instantiation ###

    private function getControllerInstance(string $class): object
    {
        if ($this->container->has($class)) {
            return $this->container->get($class);
        }

        return $this->instantiate($class);
    }

    private function instantiate(string $class): object
    {
        $ref = new ReflectionClass($class);
        $ctor = $ref->getConstructor();

        if (!$ctor) {
            return new $class();
        }

        $args = array_map(
            fn(ReflectionParameter $p) => $this->resolveCtorParam($p),
            $ctor->getParameters()
        );

        return $ref->newInstanceArgs($args);
    }

    private function resolveCtorParam(ReflectionParameter $param): mixed
    {
        $type = $param->getType();

        if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
            $class = $type->getName();

            return $this->container->has($class)
                ? $this->container->get($class)
                : new $class();
        }

        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        throw new \RuntimeException(
            "Cannot resolve \${$param->getName()}"
        );
    }

    ### Dependency injection ###

    private function injectDependencies(object $controller): void
    {
        if ($controller instanceof BaseController) {
            $view = $this->container->get(TemplateEngine::class);
            $view->setControllerContext($controller);

            $controller->setContainer($this->container);
            $controller->setView($view);
        }

        if (method_exists($controller, 'setRequest')) {
            $controller->setRequest($this->container->get(Request::class));
        }

        if (method_exists($controller, 'setSessionManager')) {
            $controller->setSessionManager(
                $this->container->get(SessionManager::class)
            );
        }

        if (method_exists($controller, 'setCookieManager')) {
            $controller->setCookieManager(
                $this->container->get(CookieManager::class)
            );
        }

        if (method_exists($controller, 'setTranslation')) {
            $controller->setTranslation(
                $this->container->get(Translation::class)
            );
        }
    }
}
