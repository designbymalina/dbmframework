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

abstract class CoreModule extends AbstractModule
{
    protected DependencyContainer $container;

    public function __construct(
        DependencyContainer $container,
        protected array $manifest,
        protected string $path
    ) {
        $this->container = $container;
    }

    public function isCore(): bool
    {
        return true;
    }

    public function getKey(): string
    {
        return strtolower($this->manifest['key']);
    }

    public function register(DependencyContainer $container): void
    {
        // default null
    }

    public function boot(): void
    {
        // default null
    }
}
