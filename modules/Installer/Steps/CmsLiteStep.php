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

final class CmsLiteStep extends AbstractInstallerStep
{
    public function getName(): string
    {
        return 'cmslite';
    }

    public function getTitle(): string
    {
        return 'installer.step.cmslite.title';
    }

    public function getDescription(): string
    {
        return ''; // optional: 'installer.step.cmslite.content'
    }

    public function boot(): void
    {
        if ($this->isCompleted()) {
            $this->setPayload([
                'type' => InstallerConstant::ALERT,
                'class' => 'info',
                'text' => 'installer.alert.installation_ready',
            ]);

            $this->setDescription(null);
            return;
        }

        if (!is_file($this->getZipPath())) {
            $this->setPayload([
                'type' => InstallerConstant::ALERT,
                'class' => 'danger',
                'text' => 'installer.alert.archive_is_missing',
            ]);
            return;
        }

        $this->setPayload([
            'type' => InstallerConstant::TEXT,
            'text' => 'installer.step.cmslite.content',
        ]);
    }

    public function handle(array $input): void
    {
        if ($this->isCompleted()) {
            return;
        }

        if (!is_file($this->getZipPath())) {
            return;
        }

        $installer = $this->container->get(PackageInstaller::class);

        if (!AlertHelper::installOrFail($installer, $this)) {
            return;
        }

        $this->markCompleted();
    }
}
