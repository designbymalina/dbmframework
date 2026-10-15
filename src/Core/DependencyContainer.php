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
 * Kontener DI w projekcie jest celowo uproszczony i półautomatyczny.
 *
 * - Serwisy mogą być jawnie rejestrowane przez set() i singleton().
 * - Klasy spełniające warunki autowiringu mogą być tworzone automatycznie.
 * - Autowiring wykorzystuje ReflectionClass do analizy konstruktora.
 * - Zależności typowane jako klasy są rozwiązywane rekurencyjnie przez kontener.
 * - Interfejsy i inne nierozwiązywalne zależności wymagają jawnej rejestracji lub aliasu.
 * - Kontener nie skanuje katalogów i nie rejestruje automatycznie wszystkich klas dostępnych przez Composer.
 *
 * Zalety:
 * - jawna kontrola nad usługami i singletonami,
 * - brak skanowania katalogów i automatycznej rejestracji,
 * - możliwość korzystania z prostego autowiringu,
 * - przewidywalna konfiguracja i łatwiejsze debugowanie.
 *
 * Ograniczenia:
 * - interfejsy wymagają jawnego powiązania z implementacją,
 * - parametry skalarne i zależności bez typu nie mogą być automatycznie rozwiązywane bez wartości domyślnych,
 * - nieprawidłowe lub brakujące rejestracje mogą powodować błędy podczas rozwiązywania usług.
 */

declare(strict_types=1);

namespace Dbm\Core;

use ReflectionClass;

final class DependencyContainer
{
    /** @var array<string, callable> */
    private array $definitions = [];
    /** @var array<string, mixed> */
    private array $instances = [];
    /** @var array<string, callable> */
    private array $singletons = [];
    /** @var array<string, array<string>> */
    private array $tags = [];
    /** @var array<string, string> */
    private array $aliases = [];

    public function set(string $id, callable $factory): void
    {
        $this->definitions[$id] = $factory;
    }

    public function get(string $id): mixed
    {
        // resolve alias
        if (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
        }

        // already instantiated singleton
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        // singleton definition
        if (isset($this->singletons[$id])) {
            $factory = $this->singletons[$id];
            $instance = $factory($this);

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

    public function singleton(string $id, ?callable $factory = null): void
    {
        // Debug: overriding service
        // if ($this->isRegistered($id)) {
        //     error_log("[DI] Overriding singleton: {$id}");
        // }

        $this->singletons[$id] = $factory ?? fn() => new $id();
    }

    public function has(string $id): bool
    {
        $id = $this->aliases[$id] ?? $id;

        return isset($this->definitions[$id])
            || isset($this->singletons[$id])
            || isset($this->instances[$id]);
    }

    public function tag(string $id, string $tag): void
    {
        $this->tags[$tag][] = $id;
    }

    public function alias(string $alias, string $target): void
    {
        $this->aliases[$alias] = $target;
    }

    public function getOptional(string $id): mixed
    {
        try {
            return $this->get($id);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<object>
     */
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

    public function setInstance(string $id, object $instance): void
    {
        $this->instances[$id] = $instance;
    }

    // ===== PRIVATE =====

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

            if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
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

    // @INFO Docelowo autowiring tylko dla: App\ Mod\ Dbm\*Module\...
    private function isAutowirable(string $id): bool
    {
        return class_exists($id)
            && !interface_exists($id)
            && !$this->isInternalType($id);
    }

    private function isInternalType(string $id): bool
    {
        return str_starts_with($id, 'Psr\\')
            || str_starts_with($id, 'Dbm\\Database\\Contracts\\')
            || str_starts_with($id, 'Dbm\\Security\\Contracts\\');
    }

    // @INFO Optional for debug.
    // private function isRegistered(string $id): bool
    // {
    //     $id = $this->aliases[$id] ?? $id;

    //     return isset($this->definitions[$id])
    //         || isset($this->singletons[$id])
    //         || isset($this->instances[$id]);
    // }
}
