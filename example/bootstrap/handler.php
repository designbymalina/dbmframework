<?php

/**
 * DBM Framework
 *
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

function bootstrapHandler(Throwable $e, string $baseDirectory): void
{
    if (class_exists(\Dbm\Infrastructure\Error\BootstrapErrorHandler::class)) {
        \Dbm\Infrastructure\Error\BootstrapErrorHandler::handle($e, $baseDirectory);
        return;
    }

    http_response_code(500);
    echo '<h1>Fatal error</h1>';
    echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
}
