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

namespace Dbm\Core\Module\Lifecycle;

use Dbm\Core\Module\Cache\ModuleCache;
use Dbm\Core\Module\Events\ModulesChangedEvent;
use Dbm\Core\Module\Package\PackageScanner;
use Dbm\Core\Module\ModuleInstaller;
use Dbm\Core\Module\ModuleBootstrapper;
use Dbm\Core\Module\Service\InstallationGuard;
use Dbm\Events\EventDispatcher;

final class ModuleLifecycleManager
{
    public function __construct(
        private readonly PackageScanner $scanner,
        private readonly ModuleInstaller $installer,
        private readonly ModuleRemovalService $removal,
        private readonly ModuleBootstrapper $bootstrapper,
        private readonly InstallationGuard $guard,
        private readonly ModuleCache $cache,
        private readonly EventDispatcher $events
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function install(string $key): array
    {
        $this->guard->start($key);

        try {
            $package = $this->scanner->findByKey($key);

            if (!$package) {
                throw new \RuntimeException("Package '{$key}' not found.");
            }

            // INFO! Backup tylko jeśli update
            // if ($this->isInstalled($key)) {
            //     $this->backup->createFullBackup($key);
            // }

            $result = $this->installer->install($package->zipPath());

            $this->rebuildCache(); // przebudowanie cache

            $this->events->dispatch(new ModulesChangedEvent('install', $key)); // przebudowa cache system modules

            $manifest = $result['manifest'] ?? null;

            if (!$manifest) {
                throw new \RuntimeException('Installer did not return manifest.');
            }

            return [
                'module' => $manifest['key'],
                'status' => 'success',
                'stage' => $result['stage'] ?? 'install',
                'manifest' => $manifest,
                'conflicts' => $result['conflicts'] ?? [],
            ];
        } catch (\Throwable $e) {
            // INFO! Można dopisać rollback
            // $this->backup->rollback($key);
            throw $e;
        } finally {
            $this->guard->finish();
        }
    }

    public function uninstall(string $key): void
    {
        $this->removal->uninstall($key);

        $this->rebuildCache();

        $this->events->dispatch(
            new ModulesChangedEvent('uninstall', $key)
        );
    }

    public function delete(string $key): void
    {
        $this->removal->delete($key);

        $this->rebuildCache();

        $this->events->dispatch(
            new ModulesChangedEvent('delete', $key)
        );
    }

    /**
     * Installs module directly from ZIP file.
     * Used by First Installer.
     *
     * @return array<string, mixed>
     */
    public function installFromZip(string $zipPath): array
    {
        if (!is_file($zipPath)) {
            throw new \RuntimeException('ZIP file not found.');
        }

        $result = $this->installer->install($zipPath);

        $this->rebuildCache(); // przebudowanie cache

        $manifest = $result['manifest'] ?? null;

        if (!$manifest) {
            throw new \RuntimeException('Installer did not return manifest.');
        }

        return [
            'module' => $manifest['key'],
            'status' => 'success',
            'stage' => $result['stage'] ?? 'install',
            'manifest' => $manifest,
            'conflicts' => $result['conflicts'] ?? [],
        ];
    }

    // ===== Private =====

    private function rebuildCache(): void
    {
        $this->cache->rebuild([$this->bootstrapper, 'discoverModules']);
    }
}
