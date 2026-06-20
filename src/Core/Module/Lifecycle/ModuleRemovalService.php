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

namespace Dbm\Core\Module\Lifecycle;

use Dbm\Core\Module\Exception\EnabledException;
use Dbm\Core\Module\Filesystem\PathResolver;
use Dbm\Core\Module\Service\ModulePackageService;
use Dbm\Infrastructure\Filesystem\Filesystem;
use Dbm\Infrastructure\Log\Logger;
use RuntimeException;

final class ModuleRemovalService
{
    public function __construct(
        private readonly PathResolver $paths,
        private readonly Filesystem $filesystem,
        private readonly Logger $logger
    ) {}

    /**
     * @INFO Dopisz usuwanie tabel DB utworzonych przez moduł,
     * usunąć konfigurację modułu - jeśli opcje są dostępne.
     */
    public function uninstall(string $key): void
    {
        $manifestPath = $this->paths->manifest($key);

        if (!$this->filesystem->fileExists($manifestPath)) {
            $message = "Module '{$key}' does not exist.";

            $this->logger->warning($message);

            throw new RuntimeException($message);
        }

        $meta = json_decode(
            $this->filesystem->readFile($manifestPath),
            true
        );

        if (!is_array($meta)) {
            throw new RuntimeException(
                "Manifest '{$key}' is empty or corrupted."
            );
        }

        $moduleDir = $this->paths->modulePath($key);

        if ($moduleDir === null) {
            throw new RuntimeException(
                "Module directory not found: {$key}"
            );
        }

        $moduleManifest = $moduleDir . '/module.json';

        $module = json_decode(
            $this->filesystem->readFile($moduleManifest),
            true
        );

        if (($module['type'] ?? '') === 'core') {
            throw new RuntimeException(
                "Core module '{$module['name']}' cannot be uninstalled."
            );
        }

        // Restore files

        foreach ($meta['files'] ?? [] as $file) {
            $relativePath = $file['path'];
            $absolutePath = $this->paths->basePath($relativePath);

            $other = $this->getFileFromOtherModules(
                $relativePath,
                $key
            );

            if ($other !== null) {
                if (is_file($absolutePath)) {
                    $currentHash = md5_file($absolutePath);

                    if ($currentHash !== $other['hash']) {
                        $this->restoreFromConflicts($relativePath);
                    }
                }

                continue;
            }

            if ($this->restoreFromConflicts($relativePath)) {
                continue;
            }
        }

        $meta['installed'] = false;
        $meta['enabled'] = false;

        $this->filesystem->saveFile(
            $manifestPath,
            json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
        );
    }

    public function delete(string $key): bool
    {
        $manifestPath = $this->paths->manifest($key);

        if ($this->filesystem->isFile($manifestPath)) {
            $install = json_decode(
                $this->filesystem->readFile($manifestPath),
                true
            );

            if (is_array($install) && ($install['enabled'] ?? false) === true) {
                throw new EnabledException();
            }

            $this->uninstall($key);
        }

        $moduleDir = $this->paths->modulePath($key);

        if ($moduleDir !== null && $this->filesystem->isDir($moduleDir)) {
            $this->filesystem->deleteDir($moduleDir);
        }

        $package = $this->paths->packages($key . '.zip');

        if ($this->filesystem->isFile($package)) {
            $this->filesystem->deleteFile($package);
        }

        $this->filesystem->deleteFile($manifestPath);

        return true;
    }

    // ===== Private methods =====

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getOtherModuleManifests(string $excludeKey): array
    {
        $manifests = [];

        foreach (glob($this->paths->modulesState('*.module.json')) as $file) {

            if (str_contains($file, $excludeKey . '.module.json')) {
                continue;
            }

            $data = json_decode($this->filesystem->readFile($file), true);

            if (is_array($data)) {
                $manifests[] = $data;
            }
        }

        return $manifests;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getFileFromOtherModules(string $path, string $excludeKey): ?array
    {
        foreach ($this->getOtherModuleManifests($excludeKey) as $manifest) {
            foreach ($manifest['files'] ?? [] as $file) {
                if ($file['path'] === $path) {
                    return $file;
                }
            }
        }

        return null;
    }

    private function restoreFromConflicts(string $path): bool
    {
        $conflictsDir = $this->paths->backups(ModulePackageService::DIR_CONFLICTS);

        if (!is_dir($conflictsDir)) {
            return false;
        }

        // Szukamy najnowszego pliku
        $files = glob($conflictsDir . '/*/' . $path);

        if (!$files) {
            return false;
        }

        rsort($files); // najnowszy pierwszy

        $latest = $files[0];

        $target = $this->paths->basePath($path);

        $this->filesystem->ensureDir(dirname($target));
        $this->filesystem->copyFile($latest, $target);

        return true;
    }
}
