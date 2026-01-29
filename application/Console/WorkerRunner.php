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

use Dbm\Database\Contracts\RequiresDatabaseInterface;
use Dbm\Database\Validator\DatabaseEnvValidator;
use Dbm\Database\DatabaseFactory;

final class WorkerRunner
{
    public function run(string $name): void
    {
        $normalized = $this->normalizeName($name);
        $class = "App\\Console\\Worker\\{$normalized}Worker";

        if (!class_exists($class)) {
            throw new \RuntimeException("Worker not found: $name");
        }

        $start = microtime(true);
        $database = null;

        echo "==========\n";
        echo "Worker started: " . date('Y-m-d H:i:s') . "\n";
        echo "----------\n";

        try {
            // INFO: Czy klasa jest zadeklarowana, ze wymaga bazy danych?
            // Jesli nie implementuje RequiresDatabaseInterface nie waliduje env, nie tworzy DB.
            if (is_subclass_of($class, RequiresDatabaseInterface::class)) {
                DatabaseEnvValidator::validate(requireDatabase: true);
                $database = DatabaseFactory::createDatabase();
                $worker = new $class($database);
            } else {
                $worker = new $class();
            }

            $worker->run();
            $status = "\033[32mOK\033[0m";
        } catch (\Throwable $e) {
            $status = "\033[31mERROR: {$e->getMessage()}\033[0m";
            throw $e;
        } finally {
            $database?->close();

            $time = round(microtime(true) - $start, 3);
            $memory = round(memory_get_peak_usage(true) / 1024 / 1024, 2);

            echo "----------\n";
            echo "Finished in {$time}s | {$memory} MB\n";
            echo "Status: {$status}\n";
            echo "==========\n";
        }
    }

    public function list(): void
    {
        foreach ($this->discoverWorkers() as $name => $class) {
            printf(
                "  %-15s %s\n",
                strtolower($name),
                (new \ReflectionClass($class))->getShortName()
            );
        }
    }

    public function discoverWorkers(): array
    {
        $commands = [];

        foreach (glob(BASE_DIRECTORY . '/src/Console/Worker/*Worker.php') as $file) {
            $name = basename($file, 'Worker.php');
            $commands[$name] = "App\\Console\\Worker\\{$name}Worker";
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
