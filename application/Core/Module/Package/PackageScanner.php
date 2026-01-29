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

namespace Dbm\Core\Module\Package;

use Dbm\Core\Module\InstalledModules;
use Dbm\Infrastructure\Log\Logger;
use Lib\Files\FileSystem;
use ZipArchive;
use Throwable;

final class PackageScanner
{
    public function __construct(
        private FileSystem $filesystem,
        private InstalledModules $installed,
        private Logger $logger
    ) {}

    /**
     * @return PackageDescriptor[]
     */
    public function scan(): array
    {
        $packagesDir = BASE_DIRECTORY . '/_Documents/packages';

        if (!$this->filesystem->isDir($packagesDir)) {
            return [];
        }

        $results = [];

        foreach ($this->filesystem->listFiles($packagesDir, 'zip') as $zipPath) {
            try {
                $descriptor = $this->readPackageDescriptor($zipPath);
                if ($descriptor) {
                    $results[] = $descriptor;
                }
            } catch (Throwable $e) {
                $this->logger->error(
                    'Invalid package ZIP: ' . basename($zipPath),
                    ['exception' => $e]
                );
            }
        }

        return $results;
    }

    public function hasPendingPackages(): bool
    {
        foreach ($this->scan() as $package) {
            if (!$this->installed->isInstalled($package->key())) {
                return true;
            }
        }
        return false;
    }

    private function readPackageDescriptor(string $zipPath): ?PackageDescriptor
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Cannot open ZIP');
        }

        $manifestContent = null;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);

            if (str_ends_with($name, 'module.json')) {
                $manifestContent = $zip->getFromIndex($i);
                break;
            }
        }

        $zip->close();

        if (!$manifestContent) {
            throw new \RuntimeException('module.json not found in ZIP');
        }

        $manifest = json_decode($manifestContent, true, flags: JSON_THROW_ON_ERROR);

        if (empty($manifest['key'])) {
            throw new \RuntimeException('Invalid module.json: missing key');
        }

        return new PackageDescriptor(
            $manifest['key'],
            $zipPath,
            $manifest
        );
    }
}
