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

namespace Dbm\Core\Module;

use Dbm\Core\DependencyContainer;
use Mod\Installer\InstallerModule;
use RuntimeException;

final class ModuleBootstrapper
{
    public function __construct(
        private DependencyContainer $container,
        private ModuleRegistry $registry
    ) {}

    public function bootFromConfig(array $config): void
    {
        foreach ($config['core'] ?? [] as $key => $class) {
            $this->loadModule($key, $class, true);
        }

        foreach ($config['enabled'] ?? [] as $key) {
            if (!isset($config['plugin'][$key])) {
                continue;
            }

            $this->loadModule($key, $config['plugin'][$key], false);
            $this->registry->enable($key);
        }

        $this->registerServices();
        $this->bootModules();
    }

    public function bootInstaller(): void
    {
        if (!class_exists(InstallerModule::class)) {
            return;
        }

        $this->loadModule('installer', InstallerModule::class, true);
    }

    private function loadModule(string $key, string $class, bool $core): void
    {
        $path = BASE_DIRECTORY . '/modules/' . ucfirst($key);
        $manifestPath = $path . '/module.json';

        if (!is_file($manifestPath)) {
            throw new RuntimeException("Missing module.json for module '{$key}'. Check your configuration.");
        }

        $manifest = json_decode(
            file_get_contents($manifestPath),
            true,
            flags: JSON_THROW_ON_ERROR
        );

        if (!class_exists($class)) {
            throw new RuntimeException("Module class not found: {$class}");
        }

        /** @var AbstractModule $module */
        $module = new $class(
            $this->container,
            $manifest,
            $path
        );

        $this->registry->register($module);

        if ($core) {
            $this->registry->enable($module->getKey());
        }
    }

    private function registerServices(): void
    {
        foreach ($this->registry->all() as $module) {
            $module->register($this->container);
        }
    }

    private function bootModules(): void
    {
        foreach ($this->registry->all() as $module) {
            $module->boot();
        }
    }
}
