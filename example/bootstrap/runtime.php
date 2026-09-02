<?php

/**
 * DBM Framework
 *
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

function initRuntime(string $baseDirectory): void
{
    initSessionRuntime();
    initErrorHandling($baseDirectory);
}

// ===== Session security configuration =====

function initSessionRuntime(): void
{
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['SERVER_PORT'] ?? null) == 443;

    $sessionName = $isHttps ? '__Host-dbmSession' : 'dbmSession';

    session_name($sessionName);

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    ini_set('session.gc_maxlifetime', '7200');
    ini_set('session.cookie_lifetime', '0');

    if ($isHttps) {
        ini_set('session.cookie_secure', '1');
    }
}

// ===== Error handling =====

function initErrorHandling(string $baseDirectory): void
{
    error_reporting(E_ALL);

    $isProd = !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);

    ini_set('display_errors', $isProd ? '0' : '1');
    ini_set('log_errors', '1');

    set_error_handler(function ($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new \ErrorException($message, 0, $severity, $file, $line);
    });

    set_exception_handler(function (\Throwable $e) use ($baseDirectory) {
        require $baseDirectory . '/bootstrap/handler.php';
        bootstrapHandler($e, $baseDirectory);
    });

    register_shutdown_function(function () use ($baseDirectory) {
        $error = error_get_last();

        if ($error && in_array($error['type'], [
            E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR,
        ])) {
            require $baseDirectory . '/bootstrap/handler.php';

            bootstrapHandler(
                new \ErrorException(
                    $error['message'],
                    0,
                    $error['type'],
                    $error['file'],
                    $error['line']
                ),
                $baseDirectory
            );
        }
    });
}
