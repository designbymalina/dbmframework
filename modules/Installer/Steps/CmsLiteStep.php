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

namespace Mod\Installer\Steps;

use Dbm\Core\Module\PackageInstaller;
use Mod\Installer\Constants\InstallerConstant;
use Mod\Installer\Steps\Helper\AlertHelper;
use Mod\Installer\Steps\Helper\CacheHelper;

final class CmsLiteStep extends AbstractInstallerStep
{
    public function getName(): string
    {
        return 'cmslite';
    }

    public function getTitle(): ?string
    {
        return 'installer.step.cmslite.title';
    }

    public function getDescription(): ?string
    {
        return 'installer.step.cmslite.content';
    }

    public function boot(): void
    {
        if ($this->isDone()) {
            $this->setPayload([
                'type' => InstallerConstant::ALERT,
                'class' => 'info',
                'text' => 'installer.alert.already_installed',
            ]);

            $this->setDescription(null);
            return;
        }

        if (!empty($this->getPayload())) {
            return;
        }

        if ($this->getPhase() === 'check' && !is_file($this->getZipPath())) {
            $this->setPayload([
                'type' => InstallerConstant::ALERT,
                'class' => 'danger',
                'text' => 'installer.alert.archive_is_missing',
                'placeholder' => [
                    'path' => '/_Documents/packages/' . $this->getZipFile(),
                ],
            ]);
            return;
        }

        match ($this->getPhase()) {
            'check', 'ready' => $this->setPayload([
                'type' => InstallerConstant::ALERT,
                'class' => 'info',
                'text' => 'installer.alert.installation_ready',
            ]),
            'installing' => $this->setPayload([
                'type' => InstallerConstant::ALERT,
                'class' => 'info',
                'text' => 'installer.alert.installation_process',
            ]),
            'done' => $this->setPayload([
                'type' => InstallerConstant::ALERT,
                'class' => 'success',
                'text' => 'installer.alert.installation_success',
            ]),
        };
    }

    public function handle(array $input): void
    {
        if ($this->isDone()) {
            return;
        }

        if ($this->getPhase() === 'check') {
            if (!is_file($this->getZipPath())) {
                return;
            }
            $this->setPhase('ready');
        }

        if ($this->getPhase() === 'ready') {
            $this->setPhase('installing');
        }

        if ($this->getPhase() === 'installing') {
            $installer = $this->container->get(PackageInstaller::class);

            if (!AlertHelper::installOrFail($installer, $this)) {
                return;
            }

            $this->setPhase('done');
            $this->markCompleted();

            // Clear cache
            register_shutdown_function(static function (): void {
                CacheHelper::clearCache();
            });
        }
    }
}
