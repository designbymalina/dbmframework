<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 *
 * INFO! Optymalizacja i szybka wersja cache.
 * Zamiast: array of metadata, można zrobić kompilowany loader.
 * Plik: storage/cache/modules_boot.php:
 * return function($container, $registry) {}
 * Wówczas Boot robi:
 * $boot = require modules_boot.php;
 * $boot($container, $registry);
 */

declare(strict_types=1);

namespace Dbm\Core\Module\Cache;

use Dbm\Core\Module\Filesystem\PathResolver;
use Dbm\Libraries\Files\FileSystem;

final class ModuleCache
{
    public function __construct(
        private readonly PathResolver $paths,
        private readonly FileSystem $filesystem
    ) {}

    /**
     * @return array<string, array{key:string, class:string, enabled:bool, installed:bool, path:string}>|null
     */
    public function load(): ?array
    {
        $file = $this->paths->cache();

        if (!$this->filesystem->isFile($file)) {
            return null;
        }

        $data = require $file;

        return is_array($data) ? $data : [];
    }

    /**
     * @param array<string, array{key:string, class:string, enabled:bool, installed:bool, path:string}> $modules
     */
    public function store(array $modules): void
    {
        $file = $this->paths->cache();

        $array = $this->exportArray($modules);

        $content = <<<PHP
            <?php

            // Application: DbM Framework
            // Modules cache file.
            // This file is auto-generated for performance reasons.
            // It will be regenerated automatically when modules change.

            return {$array};

            PHP;

        $this->filesystem->saveFile($file, $content, 0o644);
    }

    /**
     * @param callable(): array<string, array{key:string, class:string, enabled:bool, installed:bool, path:string}> $discover
     */
    public function rebuild(callable $discover): void
    {
        $modules = $discover();

        if (!$modules) {
            return;
        }

        $this->store($modules);
    }

    /**
     * Clears the cache by deleting the cache file.
     * Method only for dev/debug, not used in production code.
     */
    public function clear(): void
    {
        $file = $this->paths->cache();
        $this->filesystem->deleteFile($file);
    }

    // ===== Private =====

    /**
     * @param array<string, array{
     *  key:string,class:string,enabled:bool,installed:bool,path:string
     * }> $data
     */
    private function exportArray(array $data): string
    {
        $export = var_export($data, true);

        $export = str_replace(
            ["array (", ")"],
            ["[", "]"],
            $export
        );

        $export = preg_replace('/=>\s+\[/', '=> [', $export);
        $export = preg_replace('/\[\s*\]/', '[]', $export);

        return $export;
    }
}
