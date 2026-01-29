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

use Dbm\Http\Message\Request;
use Dbm\Routing\Contracts\RouterInterface;
use Dbm\Routing\Exceptions\MethodNotAllowedException;
use Dbm\Routing\Exceptions\RouteNotFoundException;

final class Router implements RouterInterface
{
    public function __construct(
        private readonly Request $request,
        private readonly RouteCollection $routes,
        private readonly RouteMatcher $matcher,
        private readonly MiddlewareStack $middleware,
        private readonly ControllerResolver $resolver,
        private readonly ActionArgumentResolver $argumentResolver,
        private readonly UriNormalizer $normalizer,
        private readonly UrlGenerator $urlGenerator
    ) {}

    public function dispatch(string $uri): void
    {
        RoutingContext::setUrlGenerator($this->urlGenerator);

        RoutingContext::setNamedRoutes(
            $this->routes->getNamedRoutes()
        );

        $method = $this->request->getMethod();
        $uri = $this->normalizer->normalize($uri, $this->request);

        $route = $this->matcher->match($uri, $method);

        if (!$route) {
            if ($this->routes->hasPath($uri)) {
                throw new MethodNotAllowedException(
                    $method,
                    $uri,
                    $this->routes->allowedMethods($uri)
                );
            }

            throw new RouteNotFoundException($method, $uri);
        }

        RoutingContext::setCurrentRoute($route);

        $this->middleware->handle($this->request, $route);

        [$controller, $method] = $this->resolver->resolve($route);

        $args = $this->argumentResolver->resolve($route, $controller);

        $response = $controller->$method(...$args);
        $response->send();
    }
}
