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

namespace Dbm\Console;

final class ConsoleRequest
{
    private array $argv;

    public function __construct(array $argv)
    {
        $this->argv = array_values($argv);
    }

    public function command(): ?string
    {
        return $this->argv[1] ?? null;
    }

    public function name(): ?string
    {
        return $this->argv[2] ?? null;
    }

    public function arguments(): array
    {
        return array_slice($this->argv, 3);
    }

    public function all(): array
    {
        return $this->argv;
    }
}
