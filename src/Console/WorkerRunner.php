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

use Dbm\Core\Paths;
use Dbm\Database\Contracts\DatabaseInterface;
use Dbm\Database\Contracts\RequiresDatabaseInterface;
use Dbm\Database\Validator\DatabaseEnvValidator;
use RuntimeException;

final class WorkerRunner extends AbstractConsoleRunner
{
    protected function getDirectory(): string
    {
        return Paths::joinPaths(
            Paths::basePath(),
            'src',
            'Console',
            'Worker'
        );
    }

    protected function getNamespace(): string
    {
        return 'App\\Console\\Worker';
    }

    protected function getSuffix(): string
    {
        return 'Worker';
    }

    protected function execute(string $class): void
    {
        $database = null;

        echo "==========\n";
        echo 'Worker started: ' . date('Y-m-d H:i:s') . "\n";
        echo "----------\n";

        $start = microtime(true);
        $status = "\033[32mOK\033[0m";

        try {
            if (is_subclass_of($class, RequiresDatabaseInterface::class)) {
                DatabaseEnvValidator::validate(requireDatabase: true);

                $database = $this->container->get(DatabaseInterface::class);
            }

            $worker = $this->container->get($class);

            if (!$worker instanceof AbstractWorker) {
                throw new RuntimeException(
                    "{$class} must extend " . AbstractWorker::class
                );
            }

            $worker->run();
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
}
