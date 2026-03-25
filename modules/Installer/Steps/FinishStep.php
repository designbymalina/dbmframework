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
use Dbm\Core\Module\Cache\ModuleCache;
use Dbm\Core\Module\Filesystem\PathResolver;
use Dbm\Core\Module\ModuleRegistry;
use Dbm\Infrastructure\Session\SessionManager;
use Dbm\Libraries\Files\FileSystem;
use Mod\Installer\Constants\InstallerConstant;

final class FinishStep extends AbstractInstallerStep
{
    protected DependencyContainer $container;

    // INFO! Nazwa sesji musi być zgodna z start.php -> registerModules().
    public const SESSION_ACTIVE = 'dbmInstallerActive';

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
        return '';
    }

    public function boot(): void
    {
        $session = $this->container->get(SessionManager::class);
        $session->setSession(self::SESSION_ACTIVE, true);

        if ($this->isCompleted()) {
            $actions = [
                [
                    'label' => 'installer.button.home_page',
                    'class' => 'btn-light dbm-btn-gradient',
                    'path' => 'home',
                ],
            ];

            // Tylko jeśli NIE ma admina
            if (!$this->isAdminInstalled()) {
                $actions[] = [
                    'label' => 'installer.button.add_modules',
                    'class' => 'btn-dark',
                    'path' => 'install_restart',
                ];
            }

            $this->setPayload([
                'type' => InstallerConstant::ALERT,
                'class' => 'success',
                'text' => 'installer.alert.installation_success',
                'actions' => $actions,
            ]);

            $session->unsetSession(self::SESSION_ACTIVE);
            return;
        }

        // @INFO Można doprecyzować "text" -> is admin installed or not.
        $this->setPayload([
            'type' => InstallerConstant::TEXT,
            'text' => 'installer.step.finish.content',
        ]);
    }

    public function handle(array $input): void
    {
        if ($this->isCompleted()) {
            return;
        }

        $registry = $this->container->get(ModuleRegistry::class);

        if (!$this->modulesInstalledCorrectly($registry)) {
            $this->setPayload([
                'type' => InstallerConstant::ALERT,
                'class' => 'danger',
                'text' => 'installer.alert.module_verification_failed',
            ]);

            return;
        }

        $this->writeInstalledLock($registry);
        $this->disableInstaller($registry);
        $this->markCompleted();
    }

    private function writeInstalledLock(ModuleRegistry $registry): void
    {
        $fileSystem = $this->container->get(FileSystem::class);

        $content = json_encode([
            'installed' => true,
            'admin' => $registry->has('admin'),
            'completed_at' => date('c'),
        ]);

        $fileSystem->saveFile(
            PathResolver::installerLock(),
            $content
        );
    }

    private function modulesInstalledCorrectly(ModuleRegistry $registry): bool
    {
        return !empty($registry->enabled());
    }

    private function disableInstaller(ModuleRegistry $registry): void
    {
        if (!$registry->has('admin')) {
            return;
        }

        $cache = $this->container->get(ModuleCache::class);

        $modules = $cache->load();

        if ($modules === null || !isset($modules['installer'])) {
            return;
        }

        $modules['installer']['enabled'] = false;

        $cache->store($modules);
    }

    private function isAdminInstalled(): bool
    {
        $lockFile = PathResolver::installerLock();

        if (!is_file($lockFile)) {
            return false;
        }

        $data = json_decode(file_get_contents($lockFile), true);

        return ($data['admin'] ?? false) === true;
    }
}
