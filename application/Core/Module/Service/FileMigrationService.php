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

final class FileMigrationService
{
    public function __construct(
        private FileSystem $filesystem
    ) {}

    public function migrate(
        string $sourceDir,
        string $targetDir
    ): void {
        if (!$this->filesystem->isDir($sourceDir)) {
            return;
        }

        foreach ($this->filesystem->listFilesRecursively($sourceDir) as $file) {
            $relative = substr($file, strlen($sourceDir));
            $target = $targetDir . $relative;

            if ($this->filesystem->fileExists($target)) {
                continue;
            }

            $this->filesystem->ensureDir(dirname($target));
            $this->filesystem->copyFile($file, $target);
        }
    }
}
