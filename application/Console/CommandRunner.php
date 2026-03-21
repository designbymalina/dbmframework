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

final class CommandRunner extends AbstractConsoleRunner
{
    protected function getDirectory(): string
    {
        return BASE_DIRECTORY . '/src/Console/Command';
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
        (new $class())->execute();
    }
}
