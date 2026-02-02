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

use Mod\Installer\Contracts\InstallerStepInterface;
use Mod\Installer\Constants\InstallerConstant;

final class AdminPanelStep extends AbstractInstallerStep implements InstallerStepInterface
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
        return 'installer.step.admin.content';
    }

    public function boot(): void
    {
        if (!empty($this->getPayload())) {
            return;
        }

        $zipPath = BASE_DIRECTORY . '/_Documents/packages/' . $this->getZipFile();

        if ($this->getPhase() === 'check' && !is_file($zipPath)) {
            $this->setPayload([
                'type' => InstallerConstant::ALERT,
                'class' => 'danger',
                'text' => 'installer.alert.archive_is_missing',
                'placeholder' => [
                    'path' => '/_Documents/packages/' . $this->getZipFile(),
                ],
            ]);
            // return;
        }
    }

    public function handle(array $input): void
    {
        $this->setPayload([
            'type' => InstallerConstant::TEXT,
            'text' => 'installer.step.admin.content',
        ]);

        $this->markCompleted();
    }
}
