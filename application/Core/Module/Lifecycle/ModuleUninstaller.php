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
use Dbm\Infrastructure\Log\Logger;
use Dbm\Libraries\Files\FileSystem;

final class ModuleUninstaller
{
    public function __construct(
        private readonly PathResolver $paths,
        private readonly FileSystem $filesystem,
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
                $path = $this->paths->basePath($file['path']);

                if ($this->filesystem->fileExists($path)) {
                    $this->filesystem->deleteFile($path);
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
}
