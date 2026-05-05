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

namespace Dbm\Core\Module\Discovery;

use Dbm\Core\Module\Filesystem\PathResolver;
use Dbm\Core\Module\Package\PackageDescriptor;
use Dbm\Infrastructure\Filesystem\Filesystem;

final class InstalledModuleScanner
{
    public function __construct(
        private readonly PathResolver $paths,
        private readonly Filesystem $filesystem
    ) {}

    /**
     * @return PackageDescriptor[]
     */
    public function scan(): array
    {
        $modulesDir = $this->paths->modules();

        if (!$this->filesystem->isDir($modulesDir)) {
            return [];
        }

        $result = [];

        foreach ($this->filesystem->listDirs($modulesDir) as $dir) {
            $manifestPath = $dir . '/module.json';

            if (!$this->filesystem->isFile($manifestPath)) {
                continue;
            }

            $manifest = json_decode(
                $this->filesystem->readFile($manifestPath),
                true
            );

            if (!isset($manifest['key'])) {
                continue;
            }

            $result[] = new PackageDescriptor(
                $manifest['key'],
                $manifest,
                null
            );
        }

        return $result;
    }
}
