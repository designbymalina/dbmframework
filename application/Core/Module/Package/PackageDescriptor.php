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
    public function __construct(
        private string $key,
        private string $zipPath,
        private array $manifest
    ) {}

    public function key(): string
    {
        return $this->key;
    }

    public function zipPath(): string
    {
        return $this->zipPath;
    }

    public function manifest(): array
    {
        return $this->manifest;
    }

    /* ===== FILE MIGRATIONS ===== */

    public function fileMigrations(): array
    {
        return $this->manifest['file_migrations'] ?? [];
    }

    /* ===== DATABASE ===== */

    public function hasDatabaseMigrations(): bool
    {
        return !empty($this->databaseMigrations());
    }

    public function databaseMigrations(): array
    {
        return $this->manifest['database']['migrations'] ?? [];
    }

    public function requiresDatabase(): bool
    {
        return !empty($this->databaseMigrations());
    }

    /* ===== META ===== */

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
