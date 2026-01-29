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

namespace Dbm\Core\Module;

use Dbm\Core\Module\Exception\InvalidModulePackageException;
use Dbm\Core\Module\Service\ModulePackageService;

final class PackageInstaller
{
    public function __construct(
        private ModulePackageService $packages,
        private ModuleInstaller $installer
    ) {}

    /**
     * Instaluje pakiet bezpośrednio z ZIP-a
     */
    public function installPackage(string $zipPath): array
    {
        if (!is_file($zipPath)) {
            throw new InvalidModulePackageException('ZIP file not found');
        }

        // 1. Extract (HASH)
        $extractedRoot = $this->packages->extractIfNeeded($zipPath);

        // 2. Install
        $result = $this->installer->install($extractedRoot);

        return $result;
    }
}
