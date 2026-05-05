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
use Dbm\Http\Contracts\RequestAwareInterface;
use Dbm\Http\Controller\BaseController;
use Dbm\Http\Psr\Message\ExtendedRequestInterface;
use Dbm\Infrastructure\Cookie\CookieManager;
use Dbm\Infrastructure\Session\SessionManager;
use Dbm\Localization\Translation;
use Dbm\Routing\Contracts\UrlGeneratorInterface;
use Dbm\Views\TemplateEngine;
use Psr\Http\Message\ServerRequestInterface;

final class ControllerResolver
{
    public function __construct(
        private readonly DependencyContainer $container,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {}

    /**
     * Resolves the controller and action method for the given route.
     *
     * @param Route $route
     * @return array{0: object, 1: string}
     */
    public function resolve(Route $route, ServerRequestInterface $request): array
    {
        $controller = $this->getControllerInstance($route->controller);
        $method = $route->action;

        if (!method_exists($controller, $method)) {
            throw new \RuntimeException(
                "Method {$method} not found in {$route->controller}"
            );
        }

        $this->injectDependencies($controller, $request);

        return [$controller, $method];
    }

    // ===== Controller instantiation =====

    private function getControllerInstance(string $class): object
    {
        if (!class_exists($class)) {
            throw new \RuntimeException("Controller {$class} not found");
        }

        try {
            return $this->container->get($class);
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                "Failed to resolve controller {$class}: " . $e->getMessage(),
                previous: $e
            );
        }
    }

    // ===== Dependency injection =====

    private function injectDependencies(object $controller, ServerRequestInterface $request): void
    {
        if (!$request instanceof ExtendedRequestInterface) {
            throw new \RuntimeException('Expected ExtendedRequestInterface');
        }

        $extendedRequest = $request;

        if ($controller instanceof BaseController) {
            $view = $this->container->get(TemplateEngine::class);

            $view->setRequest($request); // or $extendedRequest
            // @TODO! Dodane dla szybkiego TemplateFeature -> request() - Docelowo usunąć
            // Bez poniższego view może działać losowo.
            $view->setGlobal('request', $request);

            $view->setControllerContext($controller);

            $controller->setContainer($this->container);
            $controller->setView($view);
            $controller->setUrlGenerator($this->urlGenerator);
        }

        if ($controller instanceof RequestAwareInterface) {
            $controller->setRequest($extendedRequest);
        }

        // @INFO Zamiast method_exists można dopisać instanceof...
        // i użyć $this->injectOptional($controller);
        $this->callIfExists($controller, 'setSessionManager', $this->container->get(SessionManager::class));
        $this->callIfExists($controller, 'setCookieManager', $this->container->get(CookieManager::class));
        $this->callIfExists($controller, 'setTranslation', $this->container->get(Translation::class));
    }

    private function callIfExists(object $obj, string $method, mixed ...$args): void
    {
        if (method_exists($obj, $method)) {
            $obj->$method(...$args);
        }
    }

    // @INFO Optional - optimization
    // private function injectOptional(object $controller): void
    // {
    //     if ($controller instanceof SessionAwareInterface) {
    //         $controller->setSessionManager(
    //             $this->container->get(SessionManager::class)
    //         );
    //     }

    //     if ($controller instanceof CookieAwareInterface) {
    //         $controller->setCookieManager(
    //             $this->container->get(CookieManager::class)
    //         );
    //     }

    //     if ($controller instanceof TranslationAwareInterface) {
    //         $controller->setTranslation(
    //             $this->container->get(Translation::class)
    //         );
    //     }
    // }
}
