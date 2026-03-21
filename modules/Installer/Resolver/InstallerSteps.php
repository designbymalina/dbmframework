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
use Dbm\Core\Module\Filesystem\PathResolver;
use Dbm\Core\Module\Package\PackageScanner;
use Mod\Installer\Contracts\InstallerStepInterface;
use Mod\Installer\InstallerState;
use Mod\Installer\Steps\{
    StartStep,
    CheckRequirementsStep,
    CmsLiteStep,
    DatabaseStep,
    AuthenticationStep,
    AdminPanelStep,
    FinishStep,
    NullInstallerStep
};

final class InstallerSteps
{
    public function __construct(
        private PackageScanner $scanner,
        private PathResolver $paths,
        private InstallerState $state
    ) {}

    /**
     * @return InstallerStepInterface[]
     */
    public function resolve(DependencyContainer $container): array
    {
        $pendingKeys = $this->resolvePendingPackages();

        if (empty($pendingKeys)) {
            return [
                new NullInstallerStep($container),
            ];
        }

        $steps = [
            new StartStep($container),
            new CheckRequirementsStep($container),
        ];

        if (in_array('cmslite', $pendingKeys, true)) {
            $steps[] = new CmsLiteStep($container);
        }

        if ($this->needsDatabase($pendingKeys)) {
            $steps[] = new DatabaseStep($container);
        }

        if (in_array('authentication', $pendingKeys, true)) {
            $steps[] = new AuthenticationStep($container);
        }

        if (in_array('admin', $pendingKeys, true)) {
            $steps[] = new AdminPanelStep($container);
        }

        $steps[] = new FinishStep($container);

        return $steps;
    }

    /**
     * @return string[]
     */
    private function resolvePendingPackages(): array
    {
        $pendingKeys = $this->state->get('pending_packages');

        if ($pendingKeys !== null) {
            return $pendingKeys;
        }

        $pendingKeys = [];

        foreach ($this->scanner->scan() as $package) {

            $manifest = $this->paths->manifest($package->key());

            if (!is_file($manifest)) {
                $pendingKeys[] = $package->key();
            }
        }

        $this->state->set('pending_packages', $pendingKeys);

        return $pendingKeys;
    }

    /**
     * Determine if any pending package requires database.
     *
     * @param string[] $pendingKeys
     */
    private function needsDatabase(array $pendingKeys): bool
    {
        foreach ($this->scanner->scan() as $package) {

            if (
                in_array($package->key(), $pendingKeys, true)
                && $package->requiresDatabase()
            ) {
                return true;
            }
        }

        return false;
    }
}
