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
use Dbm\Core\Module\Contracts\TemplateAwareModule;
use Dbm\Core\Module\CoreModule;
use Dbm\Core\Module\ModuleInstaller;
use Dbm\Core\Module\PackageInstaller;
use Dbm\Core\Module\Repository\InstallRepository;
use Dbm\Core\Module\Service\DatabaseMigrationService;
use Dbm\Core\Module\Service\FileMigrationService;
use Dbm\Core\Module\Service\ModulePackageService;
use Dbm\Localization\TranslationLoader;
use Dbm\Routing\RouteBuilder;
use Dbm\Support\Config\ModuleConfigWriter;
use Dbm\Views\TemplateEngine;
use Lib\Files\FileSystem;
use Mod\Installer\Controller\InstallerController;

final class InstallerModule extends CoreModule implements TemplateAwareModule
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

        $container->singleton(
            FileMigrationService::class,
            fn($c) => new FileMigrationService(
                $c->get(FileSystem::class)
            )
        );

        $container->singleton(
            DatabaseMigrationService::class,
            fn($c) => new DatabaseMigrationService(
                $c->get(InstallRepository::class)
            )
        );

        $container->singleton(
            ModulePackageService::class,
            fn($c) => new ModulePackageService(
                $c->get(FileSystem::class),
                $c->get(FileMigrationService::class),
                $c->get(DatabaseMigrationService::class),
                $c->get(ModuleConfigWriter::class)
            )
        );

        $container->singleton(
            ModuleInstaller::class,
            fn($c) => new ModuleInstaller(
                $c->get(ModulePackageService::class)
            )
        );

        $container->singleton(
            PackageInstaller::class,
            fn($c) => new PackageInstaller(
                $c->get(ModulePackageService::class),
                $c->get(ModuleInstaller::class)
            )
        );

        $container->singleton(
            ModuleConfigWriter::class,
            fn($c) => new ModuleConfigWriter(
                $c->get(FileSystem::class)
            )
        );
    }

    public function registerRoutes(RouteBuilder $routes): void
    {
        $routes->get('/install', [InstallerController::class, 'index'], 'install');
        $routes->post('/install', [InstallerController::class, 'index'], 'install_post');
        $routes->get('/install/restart', [InstallerController::class, 'restart'], 'install_restart');
    }

    // From the interface to use @install in included templates
    public function bootTemplates(TemplateEngine $template): void
    {
        $template->addNamespace('installer', BASE_DIRECTORY . '/modules/Installer/Views/');
    }
}
