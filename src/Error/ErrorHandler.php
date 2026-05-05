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

namespace Dbm\Error;

use Dbm\Core\Config\AppConfig;
use Dbm\Infrastructure\Error\ErrorLogger;

final class ErrorHandler
{
    public function __construct(
        private readonly ErrorLogger $errorLogger
    ) {}

    public function register(string $env): void
    {
        $env = AppConfig::getEnv();

        // ini_set('display_errors', '0');
        error_reporting(E_ALL);

        ini_set(
            'display_errors',
            $env === AppConfig::ENV_PRODUCTION ? '0' : '1'
        );

        set_error_handler([$this, 'handleError']); // @INFO ErrorHandler logs errors + shutdown.
        // set_exception_handler([$this, 'handleException']); // @INFO ExceptionMiddleware catches exceptions.
        register_shutdown_function([$this, 'handleShutdown']);
    }

    public function handleError(int $level, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $level)) {
            return false;
        }

        throw new \ErrorException($message, 0, $level, $file, $line);
    }

    public function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error === null) {
            return;
        }

        if (!in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            return;
        }

        $this->errorLogger->error($error['message'], $error['file'], $error['line'], $error['type'], 'FATAL');
    }
}
