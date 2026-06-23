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
use Dbm\Core\Module\Lifecycle\ModuleRemovalService;
use Dbm\Core\Module\Package\PackageScanner;
use Dbm\Core\Module\Repository\InstallRepository;
use Dbm\Core\Module\Service\DatabaseMigrationService;
use Dbm\Core\Module\Service\FileMigrationService;
use Dbm\Core\Module\Service\InstallationGuard;
use Dbm\Core\Module\Service\ModulePackageService;
use Dbm\Core\Module\Service\ModuleState;
use Dbm\Database\Contracts\DatabaseInterface;
use Dbm\Events\EventDispatcher;
use Dbm\Infrastructure\Filesystem\Filesystem;
use Dbm\Infrastructure\Session\SessionManager;
use Psr\Log\LoggerInterface;

final class CoreModuleServiceProvider
{
    public static function register(DependencyContainer $container): void
    {
        $container->singleton(PathResolver::class);

        $container->singleton(
            ModuleManager::class,
            fn($c) => new ModuleManager(
                $c->get(ModuleBootstrapper::class),
                $c->get(PackageScanner::class),
                $c->get(SessionManager::class),
            )
        );

        $container->singleton(
            ModuleCache::class,
            fn($c) => new ModuleCache(
                $c->get(PathResolver::class),
                $c->get(Filesystem::class)
            )
        );

        $container->singleton(
            ModuleManifestLoader::class,
            fn($c) => new ModuleManifestLoader(
                $c->get(PathResolver::class),
                $c->get(Filesystem::class)
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
                $c, // @INFO Wstrzykiwanie kontenera?
            )
        );

        $container->singleton(
            ModuleState::class,
            fn($c) => new ModuleState(
                $c->get(PathResolver::class),
                $c->get(Filesystem::class),
            )
        );

        $container->singleton(
            PackageScanner::class,
            fn($c) => new PackageScanner(
                $c->get(ModuleState::class),
                $c->get(PathResolver::class),
                $c->get(Filesystem::class),
                $c->get(LoggerInterface::class)
            )
        );

        $container->singleton(
            InstallationGuard::class,
            fn($c) => new InstallationGuard(
                $c->get(Filesystem::class)
            )
        );

        $container->singleton(
            ModuleLifecycleManager::class,
            fn($c) => new ModuleLifecycleManager(
                $c->get(PackageScanner::class),
                $c->get(ModuleInstaller::class),
                $c->get(ModuleRemovalService::class),
                $c->get(ModuleBootstrapper::class),
                $c->get(InstallationGuard::class),
                $c->get(ModuleCache::class),
                $c->get(EventDispatcher::class)
            )
        );

        $container->singleton(
            InstalledModuleScanner::class,
            fn($c) => new InstalledModuleScanner(
                $c->get(PathResolver::class),
                $c->get(Filesystem::class)
            )
        );

        $container->singleton(
            FileMigrationService::class,
            fn($c) => new FileMigrationService(
                $c->get(Filesystem::class)
            )
        );

        $container->singleton(
            InstallRepository::class,
            function ($c) {
                return new InstallRepository(
                    $c->getOptional(DatabaseInterface::class)
                );
            }
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
                $c->get(Filesystem::class),
                $c->get(FileMigrationService::class),
                $c->get(PathResolver::class),
                $c->get(DatabaseMigrationService::class),
                $c->get(LoggerInterface::class)
            )
        );

        $container->singleton(
            ModuleInstaller::class,
            fn($c) => new ModuleInstaller(
                $c->get(ModulePackageService::class),
                $c->get(LoggerInterface::class)
            )
        );

        $container->singleton(
            ModuleRemovalService::class,
            fn($c) => new ModuleRemovalService(
                $c->get(PathResolver::class),
                $c->get(Filesystem::class),
                $c->get(LoggerInterface::class)
            )
        );
    }
}
