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

namespace Mod\Installer\Resolver;

use Dbm\Core\DependencyContainer;
use Dbm\Core\Module\InstalledModules;
use Dbm\Core\Module\Package\PackageScanner;
use Mod\Installer\Steps\{
    StartStep,
    CheckRequirementsStep,
    CmsLiteStep,
    DatabaseStep,
    AuthenticationStep,
    AdminPanelStep,
    FinishStep
};

final class InstallerSteps
{
    public function __construct(
        private PackageScanner $scanner,
        private InstalledModules $installed
    ) {}

    public function resolve(DependencyContainer $container): array
    {
        $steps = [
            new StartStep($container),
            new CheckRequirementsStep($container),
        ];

        /** Base system - zawsze dodajemy */
        // if (!$this->installed->isInstalled('cmslite')) {
        //     $steps[] = new CmsLiteStep($container);
        // }

        $cmsLiteStep = new CmsLiteStep($container);

        if ($this->installed->isInstalled('cmslite')) {
            $cmsLiteStep->markCompleted(); // ustawiamy isDone i fazę dla doinstalowania
        }

        $steps[] = $cmsLiteStep;

        /** Scan packages */
        $packages = $this->scanner->scan();

        $needsDatabase = false;
        $pending = [];

        // foreach ($packages as $package) {
        //     $key = $package->key();

        //     if ($this->installed->isInstalled($key)) {
        //         continue;
        //     }

        //     $pending[$key] = $package;

        //     if ($package->hasDatabaseMigrations()) {
        //         $needsDatabase = true;
        //     }
        // }
        foreach ($packages as $package) {
            $key = $package->key();

            if ($this->installed->isInstalled($key)) {
                continue;
            }

            $pending[$key] = $package;

            if ($package->requiresDatabase()) {
                $needsDatabase = true;
            }
        }

        /** Database – once */
        if ($needsDatabase) {
            $steps[] = new DatabaseStep($container);
        }

        /** Known packages (explicit order) */
        if (isset($pending['authentication'])) {
            $steps[] = new AuthenticationStep($container);
        }

        if (isset($pending['admin'])) {
            $steps[] = new AdminPanelStep($container);
        }

        /** Finish */
        $steps[] = new FinishStep($container);

        return $steps;
    }
}
