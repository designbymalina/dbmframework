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

namespace Mod\Installer;

use Dbm\Core\DependencyContainer;
use Dbm\Core\Module\Contracts\TemplateAwareInterface;
use Dbm\Core\Module\CoreModule;
use Dbm\Localization\TranslationLoader;
use Dbm\Routing\RouteBuilder;
use Dbm\Views\TemplateEngine;
use Mod\Installer\Controller\InstallerController;

final class InstallerModule extends CoreModule implements TemplateAwareInterface
{
    private const INSTALLER_PATH = BASE_DIRECTORY . '/modules/Installer';

    public function getKey(): string
    {
        return 'installer';
    }

    public function isCore(): bool
    {
        return true;
    }

    public function register(DependencyContainer $container): void
    {
        // ----- Rejestracja ścieżek modułu -----

        $container->get(TemplateEngine::class)
            ->addPath(self::INSTALLER_PATH . '/Views');

        $container->get(TranslationLoader::class)
            ->addPath(self::INSTALLER_PATH . '/Translations');

        // ----- Rejestracja serwisów modułu -----

        // Example: $container->singleton(PathResolver::class);
    }

    public function registerRoutes(RouteBuilder $routes): void
    {
        $routes->get('/install', [InstallerController::class, 'index'], 'install');
        $routes->post('/install', [InstallerController::class, 'index'], 'install_post');
        $routes->get('/install/restart', [InstallerController::class, 'restart'], 'install_restart');
    }

    // From the interface to use @installer in included templates
    public function bootTemplates(TemplateEngine $template): void
    {
        $template->addNamespace('installer', BASE_DIRECTORY . '/modules/Installer/Views/');
    }
}
