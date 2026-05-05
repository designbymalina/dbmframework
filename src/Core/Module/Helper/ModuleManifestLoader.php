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

namespace Dbm\Core\Module\Helper;

use Dbm\Core\Module\Filesystem\PathResolver;
use Dbm\Infrastructure\Filesystem\Filesystem;

final class ModuleManifestLoader
{
    public function __construct(
        private readonly PathResolver $paths,
        private readonly Filesystem $filesystem
    ) {}

    /**
     * @return array<string, string>|null
     */
    public function load(string $key): ?array
    {
        $moduleFile = $this->paths->moduleManifest($key);

        if (!$this->filesystem->isFile($moduleFile)) {
            return null;
        }

        $module = json_decode(
            $this->filesystem->readFile($moduleFile),
            true
        );

        if (!is_array($module)) {
            return null;
        }

        $installFile = $this->paths->manifest($key);

        $install = [];

        if ($this->filesystem->isFile($installFile)) {

            $install = json_decode(
                $this->filesystem->readFile($installFile),
                true
            ) ?: [];
        }

        return array_merge($module, $install);
    }
}
