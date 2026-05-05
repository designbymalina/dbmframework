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
use Dbm\Http\Message\Request;
use Dbm\Infrastructure\Log\Logger;
use Dbm\Routing\ActionArgumentResolver;
use Dbm\Routing\Contracts\UrlGeneratorInterface;
use Dbm\Routing\ControllerResolver;
use Dbm\Routing\MiddlewareStack;
use Dbm\Routing\Router;
use Dbm\Routing\RouteBuilder;
use Dbm\Routing\RouteCollection;
use Dbm\Routing\RouteMatcher;
use Dbm\Routing\UriNormalizer;

final class RoutingServiceProvider
{
    public static function register(DependencyContainer $container): void
    {
        $container->singleton(RequestContext::class, function ($c) {
            return RequestContextFactory::fromRequest(
                $c->get(Request::class)
            );
        });

        $container->singleton(
            UrlGeneratorInterface::class,
            function ($c) {
                return new UrlGenerator(
                    $c,
                    $c->get(RouteCollection::class),
                );
            }
        );

        $container->singleton(RouteCollection::class);

        $container->singleton(
            RouteBuilder::class,
            fn($c) => new RouteBuilder($c->get(RouteCollection::class))
        );

        $container->singleton(
            RouteMatcher::class,
            fn($c) => new RouteMatcher(
                $c->get(RouteCollection::class),
                $c->get(Logger::class)
            )
        );

        $container->singleton(
            MiddlewareStack::class,
            static fn() => new MiddlewareStack()
        );

        $container->singleton(UriNormalizer::class);

        $container->singleton(
            ControllerResolver::class,
            fn($c) => new ControllerResolver(
                $c,
                $c->get(UrlGeneratorInterface::class)
            )
        );

        $container->singleton(
            ActionArgumentResolver::class,
            fn($c) => new ActionArgumentResolver(
                $c
            )
        );

        $container->singleton(
            Router::class,
            fn($c) => new Router(
                $c->get(RouteCollection::class),
                $c->get(RouteMatcher::class),
                $c->get(ControllerResolver::class),
                $c->get(ActionArgumentResolver::class),
                $c->get(UriNormalizer::class),
            )
        );
    }
}
