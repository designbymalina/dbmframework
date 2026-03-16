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

namespace Dbm\Core\Module\Package;

final class PackageDescriptor
{
    /**
     * @param array<string, mixed> $manifest
     */
    public function __construct(
        private string $key,
        private array $manifest,
        private ?string $zipPath = null,
    ) {}

    public function key(): string
    {
        return $this->key;
    }

    public function name(): string
    {
        return $this->manifest['name'] ?? $this->key();
    }

    public function version(): string
    {
        return $this->manifest['version'] ?? '1.0.0';
    }

    public function description(): string
    {
        return $this->manifest['description'] ?? '';
    }

    public function type(): string
    {
        return $this->manifest['type'] ?? 'plugin';
    }

    public function class(): string
    {
        return $this->manifest['class'];
    }

    public function isCore(): bool
    {
        return $this->type() === 'core';
    }

    /**
     * @return array<string, mixed>
     */
    public function manifest(): array
    {
        return $this->manifest;
    }

    public function zipPath(): string
    {
        return $this->zipPath;
    }

    /* ===== FILE MIGRATIONS ===== */

    /**
     * @return array<string, mixed>
     */
    public function fileMigrations(): array
    {
        return $this->manifest['file_migrations'] ?? [];
    }

    /* ===== DATABASE ===== */

    public function hasDatabaseMigrations(): bool
    {
        return !empty($this->databaseMigrations());
    }

    /**
     * @return array<string, mixed>
     */
    public function databaseMigrations(): array
    {
        return $this->manifest['database']['migrations'] ?? [];
    }

    public function requiresDatabase(): bool
    {
        return !empty($this->databaseMigrations());
    }

    /* ===== META ===== */

    /**
     * @return array<string, mixed>
     */
    public function requires(): array
    {
        return $this->manifest['requires'] ?? [];
    }

    public function isOptional(): bool
    {
        return (bool) ($this->manifest['optional'] ?? false);
    }

    public function stage(): string
    {
        return $this->manifest['stage'] ?? 'runtime';
    }
}
