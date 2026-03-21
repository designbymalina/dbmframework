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
 * INFO! Klasa do wdrożenia + lekki system eventów.
 *
 * ModuleLifecycleManager
 *
 * Handles module lifecycle operations such as:
 *
 * - install
 * - enable
 * - disable
 * - uninstall
 *
 * This class is responsible for updating module state
 * and triggering module lifecycle hooks.
 *
 * Lifecycle flow:
 *
 * discover -> install -> enable -> disable -> uninstall
 *
 * Module states:
 *
 * installed = false
 * enabled = false
 *
 * installed = true
 * enabled = false
 *
 * installed = true
 * enabled = true
 *
 * Integration points:
 *
 * - ModuleManager
 * - ModuleCache
 * - ModuleRegistry
 *
 * Future responsibilities:
 *
 * - run module migrations
 * - call module lifecycle hooks
 * - update modules cache
 * - clear router cache
 *
 * Example usage:
 *
 * $lifecycle->install('cmslite');
 * $lifecycle->enable('cmslite');
 */

declare(strict_types=1);

namespace Dbm\Core\Module\Service;

use Dbm\Core\Module\Cache\ModuleCache;

// use Dbm\Core\Module\Filesystem\PathResolver;

final class ModuleLifecycleManager
{
    public function __construct(
        private readonly ModuleCache $cache,
        // private readonly PathResolver $paths
    ) {}

    /**
     * Install module
     */
    public function install(string $key): bool
    {
        $modules = $this->cache->load();

        if (!isset($modules[$key])) {
            return false;
        }

        if ($modules[$key]['installed'] === true) {
            return true;
        }

        // run migrations
        // copy resources
        // execute install hook

        $modules[$key]['installed'] = true;
        $modules[$key]['enabled'] = false;

        $this->cache->store($modules);

        return true;
    }

    /**
     * Enable module
     */
    public function enable(string $key): bool
    {
        $modules = $this->cache->load();

        if (!isset($modules[$key])) {
            return false;
        }

        if (!$modules[$key]['installed']) {
            return false;
        }

        $modules[$key]['enabled'] = true;

        // call module enable hook

        $this->cache->store($modules);

        return true;
    }

    /**
     * Disable module
     */
    public function disable(string $key): bool
    {
        $modules = $this->cache->load();

        if (!isset($modules[$key])) {
            return false;
        }

        $modules[$key]['enabled'] = false;

        // call module disable hook

        $this->cache->store($modules);

        return true;
    }

    /**
     * Uninstall module
     */
    public function uninstall(string $key): bool
    {
        $modules = $this->cache->load();

        if (!isset($modules[$key])) {
            return false;
        }

        // run uninstall scripts
        // remove module data

        $modules[$key]['installed'] = false;
        $modules[$key]['enabled'] = false;

        $this->cache->store($modules);

        return true;
    }
}
