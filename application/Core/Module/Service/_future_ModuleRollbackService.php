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

use Lib\Files\FileSystem;

final class ModuleRollbackService
{
    public function __construct(
        private FileSystem $filesystem
    ) {}

    public function rollback(string $moduleKey): void
    {
        $backupRoot = BASE_DIRECTORY . '/_Documents/backup/' . $moduleKey;

        if (!$this->filesystem->isDir($backupRoot)) {
            return;
        }

        foreach ($this->filesystem->listFilesRecursively($backupRoot) as $file) {
            $relative = substr($file, strlen($backupRoot));
            $target   = BASE_DIRECTORY . $relative;

            $this->filesystem->ensureDir(dirname($target));
            $this->filesystem->copyFile($file, $target);
        }
    }
}
