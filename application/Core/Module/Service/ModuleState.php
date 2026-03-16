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

namespace Dbm\Core\Module\Service;

use Dbm\Core\Module\Filesystem\PathResolver;
use Dbm\Libraries\Files\FileSystem;

final class ModuleState
{
    public function __construct(
        private readonly PathResolver $paths,
        private readonly FileSystem $filesystem
    ) {}

    public function isInstalled(string $key): bool
    {
        $file = $this->paths->manifest($key);

        if (!$this->filesystem->isFile($file)) {
            return false;
        }

        $data = json_decode($this->filesystem->readFile($file), true);

        return $data['installed'] ?? false;
    }

    public function isEnabled(string $key): bool
    {
        $file = $this->paths->manifest($key);

        if (!$this->filesystem->isFile($file)) {
            return false;
        }

        $data = json_decode(
            $this->filesystem->readFile($file),
            true
        );

        return (bool) ($data['enabled'] ?? false);
    }
}
