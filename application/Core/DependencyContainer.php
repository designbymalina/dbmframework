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
 * Methods available in DI can be edited in application/services.php
 */

declare(strict_types=1);

namespace Dbm\Core;

use ReflectionClass;

final class DependencyContainer
{
    private array $definitions = [];
    private array $instances = [];
    private array $singletons = [];
    private array $tags = [];
    private array $autowireNamespaces = ['App\\', 'Mod\\'];

    public function set(string $id, callable $factory): void
    {
        $this->definitions[$id] = $factory;
    }

    public function singleton(string $id, ?callable $factory = null): void
    {
        $this->singletons[$id] = $factory ?? fn() => new $id();
    }

    public function tag(string $id, string $tag): void
    {
        $this->tags[$tag][] = $id;
    }

    public function has(string $id): bool
    {
        return isset($this->definitions[$id])
            || isset($this->singletons[$id])
            || class_exists($id);
    }

    public function get(string $id): mixed
    {
        // already instantiated singleton
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        // singleton definition
        if (isset($this->singletons[$id])) {
            $factory = $this->singletons[$id];
            $instance = is_callable($factory)
                ? $factory($this)
                : new $id();

            return $this->instances[$id] = $instance;
        }

        // normal factory
        if (isset($this->definitions[$id])) {
            return ($this->definitions[$id])($this);
        }

        // autowiring
        if ($this->isAutowirable($id)) {
            return $this->autowire($id);
        }

        // last fallback
        if (class_exists($id)) {
            throw new \RuntimeException(
                "Service {$id} exists but is not registered and not autowirable."
            );
        }

        throw new \RuntimeException("Service {$id} not found.");
    }

    public function getByTag(string $tag): array
    {
        if (!isset($this->tags[$tag])) {
            return [];
        }

        $services = [];

        foreach ($this->tags[$tag] as $id) {
            $services[] = $this->get($id);
        }

        return $services;
    }

    private function autowire(string $id): object
    {
        $ref = new ReflectionClass($id);

        if (!$ref->isInstantiable()) {
            throw new \RuntimeException("Class {$id} is not instantiable");
        }

        $ctor = $ref->getConstructor();

        if ($ctor === null) {
            return new $id();
        }

        $args = [];

        foreach ($ctor->getParameters() as $param) {
            $type = $param->getType();

            if ($type === null || $type->isBuiltin()) {
                if ($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                    continue;
                }

                throw new \RuntimeException(
                    "Cannot autowire parameter \${$param->getName()} of {$id}"
                );
            }

            $args[] = $this->get($type->getName());
        }

        return $ref->newInstanceArgs($args);
    }

    private function isAutowirable(string $id): bool
    {
        if (!class_exists($id)) {
            return false;
        }

        foreach ($this->autowireNamespaces as $prefix) {
            if (str_starts_with($id, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
