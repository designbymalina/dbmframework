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

abstract class AbstractCommand
{
    abstract public function execute(): void;

    protected function success(string $msg): void
    {
        echo "\033[42m$msg\033[0m \n";
    }

    protected function info(string $msg): void
    {
        echo "\033[36m$msg\033[0m\n";
    }

    protected function error(string $msg): void
    {
        echo "\033[31m$msg\033[0m\n";
    }
}
