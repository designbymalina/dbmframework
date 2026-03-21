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
 * INFO! Metoda nie używana, ale do wdrożenia,
 * najlepiej wraz z moduleLifecycleManager.
 *
 * ModuleDependencyResolver
 *
 * Resolves module dependencies and returns modules sorted
 * in correct boot order using topological sorting.
 *
 * This resolver is currently NOT used by the framework but is
 * prepared for future module dependency support.
 *
 * Planned integration:
 * ModuleBootstrapper::bootModules()
 *
 * Example usage:
 *
 * $modules = $this->cache->load();
 * $modules = $this->resolver->resolve($modules);
 *
 * IMPORTANT:
 * Resolver should only be executed when building module cache,
 * not on every request.
 *
 * Future requirement:
 * Module manifests must support dependency declaration.
 *
 * Example manifest structure:
 *
 * {
 *   "key": "admin",
 *   "dependencies": ["authentication"]
 * }
 *
 * Example dependency graph:
 *
 * Admin -> Authentication
 *
 * After resolving:
 *
 * Authentication
 * Admin
 *
 * Features already supported by resolver:
 * - Topological sorting
 * - Dependency validation
 * - Missing dependency detection
 *
 * Possible future extensions:
 * - Circular dependency detection
 * - Optional dependencies
 * - Version constraints
 */

declare(strict_types=1);

namespace Dbm\Core\Module\Dependency;

final class ModuleDependencyResolver
{
    /**
     * @param array<string,array> $modules
     * @return array<string,array>
     */
    public function resolve(array $modules): array
    {
        $sorted = [];
        $visited = [];

        foreach ($modules as $key => $module) {
            $this->visit($key, $modules, $sorted, $visited);
        }

        return $sorted;
    }

    private function visit(
        string $key,
        array $modules,
        array &$sorted,
        array &$visited
    ): void {

        if (isset($visited[$key])) {
            return;
        }

        $visited[$key] = true;

        $dependencies = $modules[$key]['dependencies'] ?? [];

        foreach ($dependencies as $dep) {

            if (!isset($modules[$dep])) {
                throw new \RuntimeException(
                    "Missing dependency '{$dep}' for module '{$key}'"
                );
            }

            $this->visit($dep, $modules, $sorted, $visited);
        }

        $sorted[$key] = $modules[$key];
    }
}
