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

namespace Dbm\Localization;

use Dbm\Core\DependencyContainer;
use Dbm\Core\Paths;
use Dbm\Localization\Contracts\TranslationInterface;
use Dbm\Localization\Translation;
use Dbm\Localization\TranslationLoader;

final class LocalizationServiceProvider
{
    public static function register(DependencyContainer $container): void
    {
        // --- Language ---

        $container->singleton(
            CurrentLanguage::class,
            fn() => new CurrentLanguage(
                LanguageHelper::getDefaultLanguage()
            )
        );

        // --- Translation ---

        $container->singleton(
            TranslationLoader::class,
            fn($c) => (function () use ($c) {
                $loader = new TranslationLoader($c->get(CurrentLanguage::class));
                $loader->addPath(Paths::translationsPath());
                return $loader;
            })()
        );

        $container->singleton(
            Translation::class,
            fn($c) => new Translation(
                $c->get(TranslationLoader::class)->load()
            )
        );

        $container->singleton(
            TranslationInterface::class,
            fn($c) => $c->get(Translation::class)
        );
    }
}
