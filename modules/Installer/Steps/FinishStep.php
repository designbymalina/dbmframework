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

use Dbm\Core\DependencyContainer;
use Dbm\Core\Module\InstalledModules;
use Dbm\Core\Module\ModuleRegistry;
use Dbm\Infrastructure\Session\SessionManager;
use Lib\Files\FileSystem;
use Mod\Installer\Constants\InstallerConstant;

final class FinishStep extends AbstractInstallerStep
{
    protected DependencyContainer $container;

    public const SESSION_ACTIVE = 'installer_active';

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
        return ''; // optional: 'installer.step.finish.content';
    }

    // INFO! Optional (default is true).
    // public function isInstallStep(): bool
    // {
    //     return false;
    // }

    public function boot(): void
    {
        $session = $this->container->get(SessionManager::class);
        $session->setSession(self::SESSION_ACTIVE, true);

        if ($this->isCompleted()) {
            $this->setPayload([
                'type' => InstallerConstant::ALERT,
                'class' => 'success',
                'text' => 'installer.alert.installation_success',
                'actions' => [
                    [
                        'label' => 'installer.button.home_page',
                        'class' => 'btn-light dbm-btn-gradient',
                        'path' => 'home',
                    ],
                    [
                        'label' => 'installer.button.add_modules',
                        'class' => 'btn-light',
                        'path' => 'install_restart',
                    ],
                ],
            ]);

            $session->unsetSession(self::SESSION_ACTIVE);

            return;
        }

        $this->setPayload([
            'type' => InstallerConstant::TEXT,
            'text' => 'installer.step.finish.content',
        ]);
    }

    /**
     * @param array<string, mixed> $input
     */
    public function handle(array $input): void
    {
        if ($this->isCompleted()) {
            return;
        }

        $this->writeModulesConfig($this->container->get(ModuleRegistry::class));

        if (!$this->modulesInstalledCorrectly()) {
            $this->setPayload([
                'type' => InstallerConstant::ALERT,
                'class' => 'danger',
                'text' => 'installer.alert.module_verification_failed',
            ]);
            return;
        }

        $this->markCompleted();
    }

    private function writeModulesConfig(ModuleRegistry $registry): void
    {
        $fileSystem = $this->container->get(FileSystem::class);

        $arrLock = json_encode([
            "installed" => true,
            "admin" => $this->container->get(InstalledModules::class)->isInstalled('admin'),
            "completed_at" => date('c'),
        ]);

        $fileSystem->saveFile(BASE_DIRECTORY . '/modules/installed.lock', $arrLock);
    }

    private function modulesInstalledCorrectly(): bool
    {
        $installed = $this->container->get(InstalledModules::class);

        foreach ($this->getRequiredModules() as $module) {
            if (!$installed->isInstalled($module)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, string>
     */
    private function getRequiredModules(): array
    {
        $config = require BASE_DIRECTORY . '/config/modules.php';

        return $config['enabled'] ?? [];
    }
}
