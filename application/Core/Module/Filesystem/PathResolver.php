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

namespace Dbm\Core\Module\Filesystem;

final class PathResolver
{
    /** @var array<string,string>|null */
    private ?array $moduleMap = null;

    // ===== MAIN PATH =====

    public function basePath(string $path = ''): string
    {
        return $path === ''
            ? BASE_DIRECTORY
            : BASE_DIRECTORY . '/' . ltrim($path, '/');
    }

    // ===== MODULES AND DOCUMENTS PATHS (PACKAGES, BACKUPS, ENV) =====

    // INFO! Można usunąć metodę i przenieść zawartość z '_Documents' do 'storage'.
    public function documents(string $path = ''): string
    {
        return $this->basePath('_Documents' . ($path ? '/' . ltrim($path, '/') : ''));
    }

    public function packages(string $path = ''): string
    {
        return $this->documents('packages' . ($path ? '/' . ltrim($path, '/') : ''));
    }

    public function backups(string $path = ''): string
    {
        return $this->documents('backups' . ($path ? '/' . ltrim($path, '/') : ''));
    }

    public function env(): string
    {
        return $this->basePath('.env');
    }

    // ===== STORAGE PATHS =====

    public function storage(string $path = ''): string
    {
        return $this->basePath('storage' . ($path ? '/' . ltrim($path, '/') : ''));
    }

    /**
     * Manifest stanu instalacji (runtime aplikacji)
     */
    public function manifest(string $key): string
    {
        return $this->modulesState(trim($key) . '.module.json');
    }

    public function modulesState(string $path = ''): string
    {
        return $this->storage('modules' . ($path ? '/' . ltrim($path, '/') : ''));
    }

    public function cache(): string
    {
        return $this->storage('cache/modules.php');
    }

    // ===== MODULE PATHS =====

    public function modules(string $path = ''): string
    {
        return $this->basePath('modules' . ($path ? '/' . ltrim($path, '/') : ''));
    }

    public function moduleFile(): string
    {
        return 'module.json';
    }

    /**
     * Generuje ścieżkę do '/modules/ModuleName/'
     */
    public function modulePath(string $key): ?string
    {
        $dir = $this->moduleKey($key);

        return $dir ? $this->modules($dir) : null;
    }

    /**
     * Manifest modułu (only for read)
     */
    public function moduleManifest(string $key): string
    {
        $dir = $this->modulePath($key);

        if (!$dir) {
            throw new \RuntimeException("Module directory not found: {$key}");
        }

        return $dir . '/' . $this->moduleFile();
    }

    /**
     * Returns an array of module manifest paths.
     *
     * @return array<int, string>
     */
    public function moduleManifests(): array
    {
        $dir = $this->modules();

        if (!is_dir($dir)) {
            return [];
        }

        $files = [];

        foreach (scandir($dir) as $item) {
            if (in_array($item, ['.', '..'], true)) {
                continue;
            }

            $manifest = $dir . '/' . $item . '/' . $this->moduleFile();

            if (is_file($manifest)) {
                $files[] = $manifest;
            }
        }

        return $files;
    }

    /**
     * Odczytuje nazwę modułu po katalogu na podstawie klucza
     */
    public function moduleKey(string $key): ?string
    {
        if ($this->moduleMap === null) {
            $this->moduleMap = [];

            foreach ($this->moduleManifests() as $file) {
                $json = json_decode(file_get_contents($file), true);

                $moduleKey = $json['key'] ?? null;

                if ($moduleKey) {
                    $this->moduleMap[$moduleKey] = basename(dirname($file));
                }
            }
        }

        return $this->moduleMap[$key] ?? null;
    }

    // ===== Static =====

    public static function installerLock(): string
    {
        return BASE_DIRECTORY . '/storage/framework/installed.lock';
    }
}
