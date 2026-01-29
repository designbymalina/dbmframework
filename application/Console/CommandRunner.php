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

final class CommandRunner
{
    public function run(string $name): void
    {
        $normalized = $this->normalizeName($name);
        $class = "App\\Console\\Command\\{$normalized}Command";

        if (!class_exists($class)) {
            throw new \RuntimeException("Command not found: $name");
        }

        $command = new $class();
        $command->execute();
    }

    public function list(): void
    {
        foreach ($this->discoverCommands() as $name => $class) {
            printf(
                "  %-15s %s\n",
                strtolower($name),
                (new \ReflectionClass($class))->getShortName()
            );
        }
    }

    public function discoverCommands(): array
    {
        $commands = [];

        foreach (glob(BASE_DIRECTORY . '/src/Console/Command/*Command.php') as $file) {
            $name = basename($file, 'Command.php');
            $commands[$name] = "App\\Console\\Command\\{$name}Command";
        }

        return $commands;
    }

    private function normalizeName(string $name): string
    {
        return str_replace(' ', '', ucwords(
            str_replace(['-', '_'], ' ', strtolower($name))
        ));
    }
}
