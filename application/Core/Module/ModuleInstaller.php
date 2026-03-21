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

use Dbm\Core\Module\Exception\InvalidModulePackageException;
use Dbm\Core\Module\Package\PackageDescriptor;
use Dbm\Core\Module\Service\ModulePackageService;

final class ModuleInstaller
{
    public function __construct(
        private readonly ModulePackageService $service,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function install(string $sourcePath): array
    {
        $sourcePath = $this->service->extractIfNeeded($sourcePath);
        $packageRoot = $this->service->resolvePackageRoot($sourcePath);

        if (!$packageRoot) {
            throw new InvalidModulePackageException('Invalid package structure');
        }

        try {
            $moduleDir = $this->service->resolveModuleDir($packageRoot);
            $manifest = $this->service->readManifest($moduleDir);

            /** @var PackageDescriptor $package */
            $package = $this->service->loadPackageDescriptor($moduleDir, $sourcePath);

            // === COPY DIRECTORIES AND FILES AND CONFLICTS ===
            $timestamp = (new \DateTimeImmutable())->format('Ymd_His');

            $dirs = [
                'data', 'modules', 'public', 'storage', 'templates', 'translations', 'src/Shared',
            ];

            $conflicts = [];

            foreach ($dirs as $dir) {
                $conflicts = array_merge(
                    $conflicts,
                    $this->service->copyDirectoryFiles($dir, $packageRoot, $timestamp)
                );
            }

            // === FILE MIGRATIONS ===
            $this->service->fileMigrations($package->fileMigrations(), $packageRoot);

            // === DATABASE MIGRATIONS ===
            $this->service->databaseMigrations($package->databaseMigrations(), $packageRoot);

            // === CONFIG MANIFEST STATE ===
            $this->service->writeInstallManifest($manifest, $packageRoot);

            // === ENV ===
            $this->service->writeEnv($manifest);
        } catch (\Throwable $e) {
            throw new InvalidModulePackageException(
                'Package error: ' . $e->getMessage(),
                previous: $e
            );
        } finally {
            // === CLEANUP (only for package) ===
            $this->service->cleanupExtracted($packageRoot);
        }

        return [
            'manifest' => $manifest,
            'module' => $manifest['key'],
            'conflicts' => $conflicts,
            'status' => 'success',
            'stage' => 'install',
        ];
    }
}
