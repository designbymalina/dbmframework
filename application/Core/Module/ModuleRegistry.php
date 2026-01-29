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
use Dbm\Core\Module\Contracts\ModuleInterface;
use Dbm\Core\Module\Contracts\TemplateAwareModule;
use Dbm\Routing\RouteBuilder;
use Dbm\Views\TemplateEngine;
use RuntimeException;

final class ModuleRegistry
{
    /** @var array<string, ModuleInterface> */
    private array $modules = [];

    /** @var array<string, bool> */
    private array $enabled = [];

    public function __construct(
        private DependencyContainer $container
    ) {}

    /**
     * Rejestruje moduł w systemie
     */
    public function register(ModuleInterface $module): void
    {
        $key = $module->getKey();
        $this->modules[$key] = $module;

        // Automatycznie aktywuje core moduły
        if ($module->isCore()) {
            $this->enabled[$key] = true;
        }
    }

    /**
     * Włącza moduł (plugin)
     */
    public function enable(string $key): void
    {
        if (!isset($this->modules[$key])) {
            throw new RuntimeException("Module '{$key}' not registered.");
        }

        $this->enabled[$key] = true;
    }

    /**
     * Zwraca wszystkie aktywne moduły
     *
     * @return iterable<ModuleInterface>
     */
    public function all(): iterable
    {
        foreach ($this->enabled as $key => $_) {
            yield $this->modules[$key];
        }
    }

    /**
     * Rejestracja routów modułów
     */
    public function registerRoutes(RouteBuilder $routes): void
    {
        foreach ($this->all() as $module) {
            $module->registerRoutes($routes);
        }
    }

    /**
     * Rejestracja serwisów modułów
     */
    public function registerServices(): void
    {
        foreach ($this->all() as $module) {
            $module->register($this->container);
        }
    }

    /**
     * Boot aktywnych modułów
     */
    public function boot(): void
    {
        foreach ($this->all() as $module) {
            $module->boot();
        }
    }

    public function bootAll(RouteBuilder $routes, TemplateEngine $templates): void
    {
        $this->registerServices();
        $this->registerRoutes($routes);
        $this->boot();

        foreach ($this->all() as $module) {
            if ($module instanceof TemplateAwareModule) {
                $module->bootTemplates($templates);
            }
        }
    }

    public function isInstalled(string $key): bool
    {
        return isset($this->modules[$key]);
    }

    public function isEnabled(string $key): bool
    {
        return $this->enabled[$key] ?? false;
    }
}
