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

abstract class AbstractConsoleRunner
{
    abstract protected function getDirectory(): string;

    abstract protected function getNamespace(): string;

    abstract protected function getSuffix(): string;

    abstract protected function execute(string $class): void;

    public function run(string $name): void
    {
        $map = $this->discover();

        $normalized = $this->normalizeName($name);

        if (!isset($map[$normalized])) {
            throw new \RuntimeException(static::class . " not found: $name");
        }

        $this->execute($map[$normalized]);
    }

    public function list(): void
    {
        foreach ($this->discover() as $name => $class) {
            printf(
                "  %-20s %s\n",
                strtolower($name),
                (new \ReflectionClass($class))->getShortName()
            );
        }
    }

    protected function discover(): array
    {
        $map = [];
        $dir = $this->getDirectory();
        $suffix = $this->getSuffix();
        $namespace = $this->getNamespace();

        foreach (glob($dir . "/*{$suffix}.php") as $file) {
            $name = basename($file, "{$suffix}.php");
            $map[$name] = "{$namespace}\\{$name}{$suffix}";
        }

        return $map;
    }

    protected function normalizeName(string $name): string
    {
        if (preg_match('/[A-Z]/', $name)) {
            return $name;
        }

        return str_replace(' ', '', ucwords(
            str_replace(['-', '_'], ' ', strtolower($name))
        ));
    }
}
