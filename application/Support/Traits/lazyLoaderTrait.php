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
 * Example of usage:
 * protected function service(): AccountService
 * {
 *     return $this->lazy('service', fn () => new AccountService($this->repository()));
 * }
 * and:
 * $this->service()->someMethod();
 */

declare(strict_types=1);

namespace Dbm\Support\Traits;

trait LazyLoaderTrait
{
    /** @var array<string, mixed> */
    private array $__lazy = [];

    protected function lazy(string $key, callable $factory): mixed
    {
        if (!array_key_exists($key, $this->__lazy)) {
            $this->__lazy[$key] = $factory();
        }

        return $this->__lazy[$key];
    }
}
