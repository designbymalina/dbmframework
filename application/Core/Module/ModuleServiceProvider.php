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
use Dbm\Core\Module\Package\PackageScanner;
use Dbm\Core\Module\Repository\InstallRepository;
use Dbm\Core\Module\Service\{
    ModulePackageService,
    FileMigrationService,
    DatabaseMigrationService
};
use Dbm\Infrastructure\Log\Logger;
use Dbm\Routing\Contracts\UrlGeneratorInterface;
use Dbm\Routing\UrlGenerator;
use Dbm\Support\Config\ModuleConfigWriter;
use Lib\Files\FileSystem;

final class ModuleServiceProvider
{
    public static function register(DependencyContainer $container): void
    {
        $container->singleton(
            ModuleRegistry::class,
            fn($c) => new ModuleRegistry(
                $c // INFO! Wstrzykiwanie kontenera, nie zależności?
            )
        );

        $container->singleton(
            ModuleBootstrapper::class,
            fn($c) => new ModuleBootstrapper(
                $c,
                $c->get(ModuleRegistry::class)
            )
        );

        $container->singleton(InstallRepository::class);

        $container->singleton(InstalledModules::class);

        $container->singleton(
            UrlGeneratorInterface::class,
            fn($c) => $c->get(UrlGenerator::class)
        );

        // // --- Additionally ---

        $container->singleton(
            PackageScanner::class,
            fn($c) => new PackageScanner(
                $c->get(FileSystem::class),
                $c->get(InstalledModules::class),
                $c->get(Logger::class)
            )
        );

        // $container->singleton(
        //     FileMigrationService::class,
        //     fn($c) => new FileMigrationService(
        //         $c->get(FileSystem::class)
        //     )
        // );

        // $container->singleton(
        //     DatabaseMigrationService::class,
        //     fn($c) => new DatabaseMigrationService(
        //         $c->get(InstallRepository::class)
        //     )
        // );

        // $container->singleton(
        //     ModuleConfigWriter::class,
        //     fn($c) => new ModuleConfigWriter(
        //         $c->get(FileSystem::class)
        //     )
        // );

        // $container->singleton(
        //     ModulePackageService::class,
        //     fn($c) => new ModulePackageService(
        //         $c->get(FileSystem::class),
        //         $c->get(FileMigrationService::class),
        //         $c->get(DatabaseMigrationService::class),
        //         $c->get(ModuleConfigWriter::class)
        //     )
        // );
    }
}
