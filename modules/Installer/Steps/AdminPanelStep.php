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

use Dbm\Core\Module\Lifecycle\ModuleLifecycleManager;
use Mod\Installer\Constants\InstallerConstant;
use Mod\Installer\Steps\Helper\AlertHelper;

final class AdminPanelStep extends AbstractInstallerStep
{
    public function getName(): string
    {
        return 'admin';
    }

    public function getTitle(): string
    {
        return 'installer.step.admin.title';
    }

    public function getDescription(): string
    {
        return ''; // optional: 'installer.step.admin.content';
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

        if (!empty($this->getPayload())) {
            return;
        }

        if (!is_file($this->getZipPath())) {
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

        $this->setPayload([
            'type' => InstallerConstant::TEXT,
            'text' => 'installer.step.admin.content',
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

        $installer = $this->container->get(ModuleLifecycleManager::class);

        if (!AlertHelper::installOrFail($installer, $this)) {
            return;
        }

        $this->markCompleted();
    }
}
