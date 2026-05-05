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

abstract class AbstractWorker
{
    abstract public function run(): void;

    protected function log(string $message, string $level = 'info'): void
    {
        $time = date('H:i:s');

        $color = match ($level) {
            'success' => "\033[32m",
            'error' => "\033[31m",
            'warning' => "\033[33m",
            'info_dark' => "\033[34m",
            default => "\033[36m",
        };

        echo "{$color}[{$time}] {$message}\033[0m" . PHP_EOL;
    }

    protected function success(string $message): void
    {
        $this->log($message, 'success');
    }

    protected function error(string $message): void
    {
        $this->log($message, 'error');
    }
}
