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
use Dbm\Routing\RouteBuilder;

abstract class AbstractModule implements ModuleInterface
{
    /**
     * @param array<string, mixed> $manifest
     */
    public function __construct(
        protected DependencyContainer $container,
        protected array $manifest,
        protected string $path
    ) {}

    public function getKey(): string
    {
        return strtolower($this->manifest['key']);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function isCore(): bool
    {
        return ($this->manifest['type'] ?? 'plugin') === 'core';
    }

    public function isEnabled(): bool
    {
        return (bool) ($this->manifest['enabled'] ?? true);
    }

    public function register(DependencyContainer $container): void
    {
        // configuration (paths, translations)
    }

    public function registerRoutes(RouteBuilder $routes): void
    {
        // configure routes
    }

    public function boot(): void
    {
        // runtime logic (optional)
    }
}
