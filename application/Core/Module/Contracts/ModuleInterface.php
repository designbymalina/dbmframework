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

namespace Dbm\Core\Module\Contracts;

use Dbm\Core\DependencyContainer;
use Dbm\Routing\RouteBuilder;

interface ModuleInterface
{
    /** Klucz modułu (np. cmslite) */
    public function getKey(): string;

    /** Ścieżka do modułu */
    public function getPath(): string;

    /** Czy moduł jest core */
    public function isCore(): bool;

    /** Czy moduł jest aktywny */
    public function isEnabled(): bool;

    /** Rejestracja serwisów */
    public function register(DependencyContainer $container): void;

    /** Rejestracja tras modułu */
    public function registerRoutes(RouteBuilder $routes): void;

    /** Boot po załadowaniu wszystkich modułów */
    public function boot(): void;
}
