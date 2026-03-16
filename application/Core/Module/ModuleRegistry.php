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
 * Example usage in Kernel:
 *
 * foreach ($registry->enabled() as $module) {
 *     $module->boot();
 * }
 */

declare(strict_types=1);

namespace Dbm\Core\Module;

use Dbm\Core\Module\Contracts\ModuleInterface;
use Dbm\Routing\RouteBuilder;

final class ModuleRegistry
{
    /** @var array<string, ModuleInterface> */
    private array $modules = [];

    public function register(AbstractModule $module): void
    {
        $this->modules[$module->getKey()] = $module;
    }

    /**
     * @return array<string, ModuleInterface>
     */
    public function all(): array
    {
        return $this->modules;
    }

    public function get(string $key): ?ModuleInterface
    {
        return $this->modules[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return isset($this->modules[$key]);
    }

    /**
     * @return array<string, ModuleInterface>
     */
    public function enabled(): array
    {
        return array_filter(
            $this->modules,
            static fn(ModuleInterface $module) => $module->isEnabled()
        );
    }

    public function registerRoutes(RouteBuilder $routes): void
    {
        foreach ($this->enabled() as $module) {
            $module->registerRoutes($routes);
        }
    }
}
