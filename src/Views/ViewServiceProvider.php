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

namespace Dbm\Views;

use Dbm\Core\DependencyContainer;
use Dbm\Infrastructure\Session\SessionManager;
use Dbm\Infrastructure\Cookie\CookieManager;
use Dbm\Localization\Translation;
use Dbm\Views\Flash\FlashBag;

class ViewServiceProvider
{
    public static function register(DependencyContainer $container): void
    {
        $container->singleton(TemplateEngine::class, function ($c) {
            $view = new TemplateEngine();

            $view->addGlobalProvider(
                function (TemplateEngine $view) use ($c) {
                    $view->setGlobal('session', $c->get(SessionManager::class));
                    $view->setGlobal('cookie', $c->get(CookieManager::class));
                    $view->setGlobal('translation', $c->get(Translation::class));
                    $view->setGlobal(
                        'flash',
                        fn(?string $key = null) => $c->get(FlashBag::class)->get($key)
                    );
                }
            );

            return $view;
        });
    }
}
