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

use Dbm\Core\Module\Filesystem\PathResolver;
use Dbm\Core\Module\Package\PackageScanner;
use Dbm\Core\Module\ModuleBootstrapper;
use Dbm\Core\Paths;
use Dbm\Infrastructure\Session\SessionManager;

final class ModuleManager
{
    public function __construct(
        private ModuleBootstrapper $bootstrapper,
        private PackageScanner $scanner,
        private SessionManager $session
    ) {}

    public function boot(): void
    {
        $installedLock = PathResolver::installerLock();

        // Boot modułów
        if (is_dir(Paths::joinPaths(Paths::basePath(), 'modules'))) {
            $this->bootstrapper->bootModules();
        }

        // Boot instalatora
        if (
            !is_file($installedLock)
            || $this->scanner->hasPendingPackages()
            || $this->session->getSession('dbmInstallerActive')
        ) {
            $this->bootstrapper->bootInstaller();
        }
    }
}
