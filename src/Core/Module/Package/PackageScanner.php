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

use Dbm\Core\Module\Filesystem\PathResolver;
use Dbm\Core\Module\Service\ModuleState;
use Dbm\Infrastructure\Filesystem\Filesystem;
use Psr\Log\LoggerInterface;
use ZipArchive;
use Throwable;

final class PackageScanner
{
    /** @var PackageDescriptor[]|null */
    private ?array $cache = null;

    /** @var array<string,PackageDescriptor>|null */
    private ?array $byKey = null;

    public function __construct(
        private readonly ModuleState $moduleState,
        private readonly PathResolver $paths,
        private readonly Filesystem $filesystem,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * @return PackageDescriptor[]
     */
    public function scan(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $packagesDir = $this->paths->packages();

        if (!$this->filesystem->isDir($packagesDir)) {
            return $this->cache = [];
        }

        $results = [];

        foreach ($this->filesystem->listFiles($packagesDir, 'zip') as $zipPath) {
            try {
                $results[] = $this->readPackageDescriptor($zipPath);
            } catch (Throwable $e) {
                $this->logger->error(
                    'Invalid package ZIP: ' . basename($zipPath),
                    ['exception' => $e]
                );
            }
        }

        return $this->cache = $results;
    }

    public function hasPendingPackages(): bool
    {
        foreach ($this->scan() as $package) {
            if (!$this->moduleState->isInstalled($package->key())) {
                return true;
            }
        }
        return false;
    }

    private function readPackageDescriptor(string $zipPath): PackageDescriptor
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Cannot open ZIP');
        }

        $manifestContent = null;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);

            if (preg_match('#(^|/)' . $this->paths->moduleFile() . '$#', $name)) {
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
            $manifest,
            $zipPath,
        );
    }

    public function findByKey(string $key): ?PackageDescriptor
    {
        if ($this->byKey === null) {
            $this->byKey = [];

            foreach ($this->scan() as $package) {
                $this->byKey[$package->key()] = $package;
            }
        }

        return $this->byKey[$key] ?? null;
    }
}
