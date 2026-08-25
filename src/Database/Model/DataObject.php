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
 * Generic database object returned by the hydrator.
 *
 * Provides dynamic property access without requiring
 * dedicated DTO or Entity classes.
 *
 * @TODO Smart Hydration - Future versions may support:
 * - scalar type casting
 * - DateTime hydration
 * - snake_case -> camelCase mapping
 * - PHP Attributes
 * - custom casters
 * - reflection cache
 *
 * @TODO Implement:
 * - ArrayAccess
 * - IteratorAggregate
 * - JsonSerializable
 * - Countable
 */

declare(strict_types=1);

namespace Dbm\Database\Model;

final class DataObject
{
    /** @var array<string, mixed> */
    protected array $attributes = [];

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return $this->attributes;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function set(string $key, mixed $value): static
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function fill(array $data): static
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->attributes;
    }
}
