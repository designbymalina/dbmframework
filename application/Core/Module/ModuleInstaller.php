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
use Dbm\Core\Module\Service\ModulePackageService;

final class ModuleInstaller
{
    public function __construct(
        private ModulePackageService $service,
    ) {}

    public function install(string $sourcePath): array
    {
        $packageRoot = $this->service->resolvePackageRoot($sourcePath);

        if (!$packageRoot) {
            throw new InvalidModulePackageException('Invalid package structure');
        }

        $moduleDir = $this->service->resolveModuleDir($packageRoot);
        $manifest = $this->service->readManifest($moduleDir);

        /** @var PackageDescriptor $package */
        $package = $this->service->loadPackageDescriptor($moduleDir, $sourcePath);

        // === FILES ===
        $this->service->copyWithBackup($packageRoot . '/modules', BASE_DIRECTORY . '/modules', $manifest['key']);
        $this->service->copyWithBackup($packageRoot . '/config', BASE_DIRECTORY . '/config', $manifest['key']);
        $this->service->copyWithBackup($packageRoot . '/public', BASE_DIRECTORY . '/public', $manifest['key']);
        $this->service->copyWithBackup($packageRoot . '/templates', BASE_DIRECTORY . '/templates', $manifest['key']);
        $this->service->copyWithBackup($packageRoot . '/translations', BASE_DIRECTORY . '/translations', $manifest['key']);

        // === FILE MIGRATIONS ===
        $this->service->fileMigrations($package->fileMigrations(), $packageRoot);

        // === DATABASE MIGRATIONS ===
        $this->service->databaseMigrations($package->databaseMigrations(), $packageRoot);

        // === CONFIG ===
        $this->service->writeConfig($manifest);

        // === ENV ===
        $this->service->writeEnv($manifest);

        // === CLEANUP (tylko ta paczka) ===
        $this->service->cleanupExtracted($packageRoot);

        return [
            'manifest' => $manifest,
            'module' => $manifest['key'],
        ];
    }
}
