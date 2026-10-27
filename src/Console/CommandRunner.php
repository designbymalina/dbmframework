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

final class CommandRunner extends AbstractConsoleRunner
{
    protected function getDirectory(): string
    {
        return Paths::joinPaths(
            Paths::basePath(),
            'src',
            'Console',
            'Command'
        );
    }

    protected function getNamespace(): string
    {
        return 'App\\Console\\Command';
    }

    protected function getSuffix(): string
    {
        return 'Command';
    }

    protected function execute(string $class): void
    {
        $database = null;

        try {
            if (is_subclass_of(
                $class,
                \Dbm\Database\Contracts\RequiresDatabaseInterface::class
            )) {
                \Dbm\Database\Validator\DatabaseEnvValidator::validate(
                    requireDatabase: true
                );

                $database = $this->container->get(
                    \Dbm\Database\Contracts\DatabaseInterface::class
                );
            }

            $command = $this->container->get($class);

            if (!$command instanceof AbstractCommand) {
                throw new \RuntimeException(
                    "{$class} must extend " . AbstractCommand::class
                );
            }

            $command->execute();
        } finally {
            $database?->close();
        }
    }
}
