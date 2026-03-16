<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 *
 * INFO! Można dopisać module events, module dependencies, module priority.
 */

declare(strict_types=1);

namespace Dbm\Core\Module;

use Dbm\Core\DependencyContainer;
use Dbm\Core\Module\Cache\ModuleCache;
use Dbm\Core\Module\Contracts\TemplateAwareInterface;
use Dbm\Core\Module\Filesystem\PathResolver;
use Dbm\Core\Module\Helper\ModuleManifestLoader;
use Dbm\Views\TemplateEngine;
use Mod\Installer\InstallerModule;

final class ModuleBootstrapper
{
    public function __construct(
        private readonly ModuleRegistry $registry,
        private readonly ModuleManifestLoader $loader,
        private readonly PathResolver $paths,
        private readonly ModuleCache $cache,
        private readonly DependencyContainer $container,
    ) {}

    public function bootModules(): void
    {
        $modules = $this->cache->load();

        if ($modules === null) {
            $modules = $this->discoverModules();
            $this->cache->store($modules);
        }

        $installationCompleted = self::isInstallationCompleted(
            $this->paths->installerLock()
        );

        foreach ($modules as $module) {
            if (!$this->shouldBootModule($module, $installationCompleted)) {
                continue;
            }

            $class = $module['class'];

            $registryModule = new $class(
                $this->container,
                $module,
                $module['path']
            );

            $this->registry->register($registryModule);
        }

        $this->boot();
    }

    public function bootInstaller(): void
    {
        if (!class_exists(InstallerModule::class)) {
            return;
        }

        $module = new InstallerModule(
            $this->container,
            [],
            $this->paths->modules('Installer')
        );

        $view = $this->container->get(TemplateEngine::class);

        $module->register($this->container);

        if ($module instanceof TemplateAwareInterface) {
            $module->bootTemplates($view);
        }

        $module->boot();
    }

    /**
     * Scans the modules directory and returns an array of module manifests.
     *
     * @return array<string, array{key: string, class: string, enabled: bool, installed: bool, path: string}>
     */
    public function discoverModules(): array
    {
        $modules = [];

        foreach ($this->paths->moduleManifests() as $file) {
            $dir = basename(dirname($file));
            $key = strtolower($dir);

            $data = $this->loader->load($key);

            if (!$data || empty($data['class'])) {
                continue;
            }

            $enabled = $data['enabled'] ?? false;
            $installed = $data['installed'] ?? false;

            // Wyjątek dla Instalatora
            if ($key === 'installer') {
                $enabled = true;
                $installed = true;
            }

            // INFO! Optymalizacja czasu wykonania, cache dla wydajności.
            $modules[$key] = [
                'key' => $key,
                'class' => $data['class'],
                'enabled' => $enabled,
                'installed' => $installed,
                'path' => $this->paths->modules($dir),
            ];
        }

        return $modules;
    }

    // ===== Private =====

    private function shouldBootModule(array $module, bool $installationCompleted): bool
    {
        if (!$module['enabled']) {
            return false;
        }

        if ($module['key'] === 'installer' && $installationCompleted) {
            return false;
        }

        if (!class_exists($module['class'])) {
            return false;
        }

        return true;
    }

    private function boot(): void
    {
        $view = $this->container->get(TemplateEngine::class);

        foreach ($this->registry->all() as $module) {
            $module->register($this->container);

            if ($module instanceof TemplateAwareInterface) {
                $module->bootTemplates($view);
            }

            $module->boot();
        }
    }

    public static function isInstallationCompleted(string $lockFile): bool
    {
        if (!is_file($lockFile)) {
            return false;
        }

        $data = json_decode(file_get_contents($lockFile), true);

        return ($data['admin'] ?? false) === true;
    }
}
