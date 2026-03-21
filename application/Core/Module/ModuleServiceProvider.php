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

namespace Dbm\Core\Module;

use Dbm\Core\DependencyContainer;
use Dbm\Core\Module\Cache\ModuleCache;
use Dbm\Core\Module\Discovery\InstalledModuleScanner;
use Dbm\Core\Module\Filesystem\PathResolver;
use Dbm\Core\Module\Helper\ModuleManifestLoader;
use Dbm\Core\Module\Lifecycle\ModuleLifecycleManager;
use Dbm\Core\Module\Lifecycle\ModuleUninstaller;
use Dbm\Core\Module\Package\PackageScanner;
use Dbm\Core\Module\Repository\InstallRepository;
use Dbm\Core\Module\Service\DatabaseMigrationService;
use Dbm\Core\Module\Service\FileMigrationService;
use Dbm\Core\Module\Service\InstallationGuard;
use Dbm\Core\Module\Service\ModulePackageService;
use Dbm\Core\Module\Service\ModuleState;
use Dbm\Infrastructure\Log\Logger;
use Dbm\Libraries\Files\FileSystem;
use Dbm\Routing\Contracts\UrlGeneratorInterface;
use Dbm\Routing\UrlGenerator;

final class ModuleServiceProvider
{
    public static function register(DependencyContainer $container): void
    {
        // ===== Routing - INFO! Popraw w services.php na interfejs. =====

        $container->singleton(
            UrlGeneratorInterface::class,
            fn($c) => $c->get(UrlGenerator::class)
        );

        // ===== Modules =====

        $container->singleton(PathResolver::class);

        $container->singleton(
            ModuleCache::class,
            fn($c) => new ModuleCache(
                $c->get(PathResolver::class),
                $c->get(FileSystem::class)
            )
        );

        $container->singleton(
            ModuleManifestLoader::class,
            fn($c) => new ModuleManifestLoader(
                $c->get(PathResolver::class),
                $c->get(FileSystem::class)
            )
        );

        $container->singleton(ModuleRegistry::class);

        $container->singleton(
            ModuleBootstrapper::class,
            fn($c) => new ModuleBootstrapper(
                $c->get(ModuleRegistry::class),
                $c->get(ModuleManifestLoader::class),
                $c->get(PathResolver::class),
                $c->get(ModuleCache::class),
                $c, // INFO! Wstrzykiwanie kontenera?
            )
        );

        $container->singleton(
            ModuleState::class,
            fn($c) => new ModuleState(
                $c->get(PathResolver::class),
                $c->get(FileSystem::class),
            )
        );

        $container->singleton(
            PackageScanner::class,
            fn($c) => new PackageScanner(
                $c->get(ModuleState::class),
                $c->get(PathResolver::class),
                $c->get(FileSystem::class),
                $c->get(Logger::class)
            )
        );

        $container->singleton(
            InstallationGuard::class,
            fn($c) => new InstallationGuard(
                $c->get(FileSystem::class)
            )
        );

        $container->singleton(
            ModuleLifecycleManager::class,
            fn($c) => new ModuleLifecycleManager(
                $c->get(PackageScanner::class),
                $c->get(ModuleInstaller::class),
                $c->get(ModuleUninstaller::class),
                $c->get(ModuleBootstrapper::class),
                $c->get(InstallationGuard::class),
                $c->get(ModuleCache::class)
            )
        );

        $container->singleton(
            InstalledModuleScanner::class,
            fn($c) => new InstalledModuleScanner(
                $c->get(PathResolver::class),
                $c->get(FileSystem::class)
            )
        );

        $container->singleton(
            FileMigrationService::class,
            fn($c) => new FileMigrationService(
                $c->get(FileSystem::class)
            )
        );

        $container->singleton(InstallRepository::class);

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
                $c->get(PathResolver::class)
            )
        );

        $container->singleton(
            ModuleInstaller::class,
            fn($c) => new ModuleInstaller(
                $c->get(ModulePackageService::class),
            )
        );

        $container->singleton(
            ModuleUninstaller::class,
            fn($c) => new ModuleUninstaller(
                $c->get(PathResolver::class),
                $c->get(FileSystem::class),
                $c->get(Logger::class)
            )
        );
    }
}
