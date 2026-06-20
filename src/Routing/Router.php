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
 * Router is PSR-7 compatible but NOT PSR-agnostic.
 *
 * It requires ExtendedRequestInterface which extends ServerRequestInterface
 * and provides framework-specific features (attributes, DI helpers, etc.).
 *
 * If a plain ServerRequestInterface is provided, it must already be
 * an instance of ExtendedRequestInterface (framework request).
 */

declare(strict_types=1);

namespace Dbm\Routing;

use Dbm\Events\EventDispatcher;
use Dbm\Http\Message\Response;
use Dbm\Http\Psr\Message\ExtendedRequestInterface;
use Dbm\Localization\CurrentLanguage;
use Dbm\Localization\Event\LocaleChangedEvent;
use Dbm\Localization\LanguageResolver;
use Dbm\Routing\Contracts\RouterInterface;
use Dbm\Routing\Exceptions\MethodNotAllowedException;
use Dbm\Routing\Exceptions\RouteNotFoundException;

final class Router implements RouterInterface
{
    public function __construct(
        private readonly RouteCollection $routes,
        private readonly RouteMatcher $matcher,
        private readonly ControllerResolver $resolver,
        private readonly ActionArgumentResolver $argumentResolver,
        private readonly UriNormalizer $normalizer,
        private readonly LanguageResolver $languageResolver,
        private readonly CurrentLanguage $currentLanguage,
        private readonly EventDispatcher $dispatcher
    ) {}

    public function dispatch(ExtendedRequestInterface $request, string $uri): Response
    {
        $method = $request->getMethod();

        $uri = $this->normalizer->normalize($uri, $request);

        $languageMatch = $this->languageResolver->resolve($uri);

        // @INFO Language request setting
        $this->currentLanguage->set($languageMatch->language);

        // Notify interested modules
        $this->dispatcher->dispatch(
            new LocaleChangedEvent($languageMatch->language)
        );

        $request = $request->withAttribute(
            'language',
            $languageMatch->language
        );

        $uri = $languageMatch->path;

        $result = $this->matcher->match($uri, $method);

        if ($result === null) {
            if ($this->routes->hasPath($uri)) {
                throw new MethodNotAllowedException(
                    $method,
                    $uri,
                    $this->routes->allowedMethods($uri)
                );
            }

            throw new RouteNotFoundException($method, $uri);
        }

        [$route, $params] = $result;

        $request = $request
            ->withAttribute('route', $route)
            ->withAttribute('route_params', $params);

        // closure / callable support
        $handler = $route->handler;

        if (is_callable($handler)) {
            return $handler($request, $params);
        }

        if ($route->controller === null || $route->action === null) {
            throw new \RuntimeException('Route has no valid handler or controller.');
        }

        // resolve
        [$controller, $action] = $this->resolver->resolve($route, $request);

        $args = $this->argumentResolver->resolve($route, $controller, $request);

        return $controller->$action(...$args);
    }
}
