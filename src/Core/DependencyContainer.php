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
 * - Serwisy NIE są rejestrowane automatycznie
 * - Wszystkie zależności należy jawnie zdefiniować w:
 *   bootstrap/services.php
 *
 * Oznacza to, że każda nowa klasa używana przez kontener (np. Middleware,
 * Service, Handler) musi zostać ręcznie dodana do rejestru.
 *
 * W przeciwnym wypadku wystąpi błąd:
 * "Service <ClassName> not found"
 *
 * Zalety:
 * - bardzo niska narzutowość (brak refleksji i skanowania plików)
 * - przewidywalny czas startu (O(liczby zarejestrowanych serwisów))
 * - pełna kontrola nad instancjami i cyklem życia
 * - mniejsze zużycie pamięci
 * - łatwiejsze debugowanie
 * - brak "magii" i ukrytej konfiguracji
 *
 * Ograniczenia:
 * - konieczność ręcznej rejestracji serwisów
 * - ryzyko błędów przy dodawaniu nowych klas
 *
 * Debug:
 * Jeśli serwis nie jest znajdowany, sprawdź:
 * 1. Czy klasa istnieje (namespace, autoload)
 * 2. Czy została dodana w bootstrap/services.php
 * 3. Czy composer dump-autoload zostało wykonane (przy autoload Composer)
 *
 * Możliwe kierunki rozwoju:
 * - autowiring (kosztowne automatyczne wstrzykiwanie zależności)?
 * - skanowanie katalogu src/ i autorejestracja (PSR-4)
 * - konfiguracja oparta o atrybuty lub metadata
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
