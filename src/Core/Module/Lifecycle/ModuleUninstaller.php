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

use Dbm\Core\Module\Filesystem\PathResolver;
use Dbm\Core\Module\Service\ModulePackageService;
use Dbm\Infrastructure\Filesystem\Filesystem;
use Dbm\Infrastructure\Log\Logger;

final class ModuleUninstaller
{
    public function __construct(
        private readonly PathResolver $paths,
        private readonly Filesystem $filesystem,
        private readonly Logger $logger,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function uninstall(string $key): array
    {
        $manifestPath = $this->paths->manifest($key);

        if (!$this->filesystem->fileExists($manifestPath)) {
            $message = "Moduł '$key' nie istnieje.";
            $this->logger->warning($message);

            return [
                'status' => 'warning',
                'message' => $message,
            ];
        }

        $json = $this->filesystem->readFile($manifestPath);

        $meta = json_decode($json, true);

        if (!is_array($meta)) {
            return [
                'status' => 'error',
                'message' => "Manifest '$key' jest pusty lub uszkodzony.",
            ];
        }

        // Core modules cannot be removed

        $moduleDir = $this->paths->modulePath($key);

        if (!$moduleDir) {
            return [
                'status' => 'error',
                'message' => "Module '{$key}' not found.",
            ];
        }

        $moduleManifest = $moduleDir . '/module.json';

        $module = json_decode(
            $this->filesystem->readFile($moduleManifest),
            true
        );

        if (($module['type'] ?? '') === 'core') {
            return [
                'status' => 'error',
                'message' => "Nie można odinstalować wbudowanego modułu '{$module['name']}'.",
            ];
        }

        // Remove installed files

        if (!empty($meta['files'])) {
            foreach ($meta['files'] as $file) {
                $relativePath = $file['path'];
                $absolutePath = $this->paths->basePath($relativePath);

                // Używany przez inny moduł - NIE usuwaj
                $other = $this->getFileFromOtherModules($relativePath, $key);

                if ($other !== null) {
                    // jeśli hash się różni - przywróć właściwy
                    if (is_file($absolutePath)) {
                        $currentHash = md5_file($absolutePath);

                        if ($currentHash !== $other['hash']) {
                            $this->restoreFromConflicts($relativePath);
                        }
                    }

                    continue;
                }

                // Spróbuj przywrócić z konfliktów
                if ($this->restoreFromConflicts($relativePath)) {
                    continue;
                }

                // Usuń jeśli istnieje
                if ($this->filesystem->fileExists($absolutePath)) {
                    $this->filesystem->deleteFile($absolutePath);
                }
            }
        }

        // Remove install manifest

        $this->filesystem->deleteFile($manifestPath);

        $name = $module['name'] ?? $key;

        return [
            'status' => 'success',
            'message' => "Moduł '$name' został odinstalowany.",
        ];
    }

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
