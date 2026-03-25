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
 * INFO! Here we add everything that goes into the templates globally.
 */

declare(strict_types=1);

use Dbm\Http\Message\Request;
use Dbm\Infrastructure\Cookie\CookieManager;
use Dbm\Infrastructure\Session\SessionManager;
use Dbm\Localization\Translation;
use Dbm\Views\Flash\FlashBag;
use Dbm\Views\TemplateEngine;

/** @var \Dbm\Core\DependencyContainer $container */

// Initializes TemplateEngine and registers global providers
$view = $container->get(TemplateEngine::class);

$view->addGlobalProvider(
    static function (TemplateEngine $view) use ($container): void {
        // --- Framework View Injection
        // SECURITY: The view can read the Request but should not modify the application state.
        $view->setGlobal('request', $container->get(Request::class));
        $view->setGlobal('session', $container->get(SessionManager::class));
        $view->setGlobal('cookie', $container->get(CookieManager::class));
        $view->setGlobal('translation', $container->get(Translation::class));
        $view->setGlobal(
            'flash',
            static fn(?string $key = null) => $container->get(FlashBag::class)->get($key)
        );
    }
);
