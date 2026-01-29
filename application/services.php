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
 * File related to DependecyContainer()
 *
 * Dependency Injection configuration
 *
 * IMPORTANT:
 * - Only CORE / INFRA services
 * - No App\* services here
 * - Exceptions:
 *   - global application context
 *   - heavy infrastructure adapters
 *
 * DOCUMENTATION: Examples can be found in the README documentation -> Dependency Injection
 * LATER can be expanded by: ViewContext, EventDispatcher, MailSenderInterface, etc.
 */

declare(strict_types=1);

use Dbm\Core\DependencyContainer;
use Dbm\Core\Module\ModuleServiceProvider;
use Dbm\Database\DatabaseFactory;
use Dbm\Database\Contracts\DatabaseInterface;
use Dbm\Http\Message\Request;
use Dbm\Infrastructure\Cookie\CookieManager;
use Dbm\Infrastructure\Session\SessionManager;
use Dbm\Routing\{
    ActionArgumentResolver,
    Router,
    RouteCollection,
    RouteMatcher,
    MiddlewareStack,
    ControllerResolver,
    RouteBuilder,
    UriNormalizer,
    UrlGenerator
};
use Dbm\Views\TemplateEngine;
use Dbm\Infrastructure\Log\Logger;
use Dbm\Localization\Contracts\TranslationInterface;
use Dbm\Localization\LanguageService;
use Dbm\Localization\Translation;
use Dbm\Localization\TranslationLoader;
use Dbm\Views\Flash\FlashBag;
use Lib\Files\FileSystem;
use Lib\Sender\PHPMailerSender;

return function (DependencyContainer $container): void {

    ### HTTP / REQUEST ###

    $container->singleton(Request::class, fn() => new Request());
    $container->singleton(Logger::class, fn() => new Logger());

    ### ROUTING CORE ###

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

    $container->singleton(MiddlewareStack::class);
    $container->singleton(UriNormalizer::class);

    $container->singleton(
        ControllerResolver::class,
        fn($c) => new ControllerResolver($c)
    );

    $container->singleton(
        ActionArgumentResolver::class,
        fn($c) => new ActionArgumentResolver(
            $c,
            $c->get(Request::class)
        )
    );

    $container->singleton(
        UrlGenerator::class,
        fn($c) => new UrlGenerator(
            $c->get(RouteCollection::class)
        )
    );

    $container->singleton(
        Router::class,
        fn($c) => new Router(
            $c->get(Request::class),
            $c->get(RouteCollection::class),
            $c->get(RouteMatcher::class),
            $c->get(MiddlewareStack::class),
            $c->get(ControllerResolver::class),
            $c->get(ActionArgumentResolver::class),
            $c->get(UriNormalizer::class),
            $c->get(UrlGenerator::class)
        )
    );

    ### SESSION / COOKIE / TRANSLATION ###

    $container->singleton(SessionManager::class);
    $container->singleton(CookieManager::class);

    $container->singleton(
        LanguageService::class,
        fn($c) => new LanguageService(
            $c->get(Request::class),
            $c->get(CookieManager::class)
        )
    );

    $container->set(
        Translation::class,
        fn($c) => new Translation(
            $c->get(TranslationLoader::class)->load()
        )
    );

    $container->singleton(
        TranslationLoader::class,
        fn($c) => (function () use ($c) {
            $loader = new TranslationLoader($c->get(LanguageService::class));
            $loader->addPath(BASE_DIRECTORY . '/translations');
            return $loader;
        })()
    );

    $container->singleton(
        TranslationInterface::class,
        fn($c) => $c->get(Translation::class)
    );

    ### FLASH ###

    $container->singleton(
        FlashBag::class,
        fn($c) => new FlashBag($c->get(SessionManager::class))
    );

    ### VIEW ###

    $container->singleton(TemplateEngine::class);

    ### DATABASE ###

    $container->set(
        DatabaseInterface::class,
        fn() => isConfigDatabase()
            ? DatabaseFactory::createDatabase()
            : null
    );

    ### EMAIL - Optional ###

    $container->singleton(PHPMailerSender::class);

    ### FILE SYSTEM - Optional ###

    $container->singleton(FileSystem::class);

    ### MODULES - Optional ###

    ModuleServiceProvider::register($container);
};
