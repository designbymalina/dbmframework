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

use Dbm\Core\Module\InstalledModules;
use Mod\Installer\Contracts\InstallerStepInterface;
use Dbm\Core\Module\ModuleRegistry;
use Lib\Files\FileSystem;
use Mod\Installer\Constants\InstallerConstant;

final class FinishStep extends AbstractInstallerStep implements InstallerStepInterface
{
    public function getName(): string
    {
        return 'finish';
    }

    public function getTitle(): string
    {
        return 'installer.step.finish.title';
    }

    public function getDescription(): string
    {
        return 'installer.step.finish.content';
    }

    public function isInstallStep(): bool
    {
        return false;
    }

    public function boot(): void
    {
        if ($this->isCompleted()) {
            return;
        }

        $this->setPayload([
            'type' => InstallerConstant::ALERT,
            'class' => 'success',
            'text' => 'installer.step.finish.title',
            'actions' => [
                [
                    'label' => 'installer.action.finish',
                    'class' => 'btn btn-success',
                    'type'  => 'submit',
                ],
            ],
        ]);
    }

    public function handle(array $input): void
    {
        if ($this->isCompleted()) {
            return;
        }

        $fileSystem = $this->container->get(FileSystem::class);

        // zapis aktywnych modułów
        $this->writeModulesConfig(
            $this->container->get(ModuleRegistry::class)
        );

        // lock instalatora
        $arrLock = json_encode([
            "installed" => true,
            "admin" => $this->container->get(InstalledModules::class)->isInstalled('admin'),
            "completed_at" => date('c'),
        ]);

        $fileSystem->saveFile(BASE_DIRECTORY . '/modules/installed.lock', $arrLock);

        $this->markCompleted();

        // redirect
        header("Location: ./");
        exit;
    }

    private function writeModulesConfig(ModuleRegistry $registry): void
    {
        // np. dodatkowa konfiguracja i ustawienia
    }
}
