<?php

/**
 * DBM Framework
 *
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

use Dbm\Core\DependencyContainer;
use Dbm\Exceptions\ExceptionHandler;
use Dbm\Http\Message\Request;
use Dbm\Http\Psr\Message\ExtendedRequestInterface;
use Dbm\Views\TemplateEngine;
use Dbm\Kernel\Contracts\KernelInterface;
use Dbm\Kernel\HttpKernel;
use Dbm\Routing\Contracts\UrlGeneratorInterface;
use Dbm\Routing\MiddlewareStack;
use Dbm\Routing\Router;
use Dbm\Routing\RouteBuilder;
use Dbm\Routing\RouteCollection;
use Dbm\Routing\RoutingServiceProvider;
use Psr\Http\Message\ServerRequestInterface;

return function (DependencyContainer $container): DependencyContainer {
    // ===== CORE =====

    $container->singleton(
        ExceptionHandler::class,
        fn($c) => new ExceptionHandler(
            $c->get(UrlGeneratorInterface::class)
        )
    );

    $container->singleton(
        KernelInterface::class,
        fn($c) => new HttpKernel(
            $c->get(Router::class),
            $c->get(MiddlewareStack::class),
            $c->get(TemplateEngine::class),
            $c->get(UrlGeneratorInterface::class)
        )
    );

    // ===== HTTP / REQUEST =====

    // Jedna instancja request na cały request lifecycle
    $container->singleton(
        ExtendedRequestInterface::class,
        fn() => Request::fromGlobals()
    );

    // Alias PSR - framework implementacja
    $container->alias(ServerRequestInterface::class, ExtendedRequestInterface::class);

    // Alias konkretnej klasy
    $container->alias(Request::class, ExtendedRequestInterface::class);

    // ===== ROUTING =====

    $container->singleton(RouteCollection::class);

    // Autowiring (warto dodać)
    $container->singleton(RouteBuilder::class);

    // Middleware pipeline (autowiring)
    $container->singleton(MiddlewareStack::class);

    // Router + URL generator itd.
    RoutingServiceProvider::register($container);

    return $container;
};
