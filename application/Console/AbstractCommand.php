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

    protected function success(string $msg, bool $background = false): void
    {
        $this->output($msg, $background ? '42' : '32');
    }

    protected function info(string $msg, bool $background = false): void
    {
        $this->output($msg, $background ? '46' : '36');
    }

    protected function warning(string $msg, bool $background = false): void
    {
        $this->output($msg, $background ? '43' : '33');
    }

    protected function error(string $msg, bool $background = false): void
    {
        $this->output($msg, $background ? '41' : '31');
    }

    private function output(string $msg, string $colorCode): void
    {
        echo "\033[{$colorCode}m{$msg}\033[0m" . PHP_EOL;
    }
}
