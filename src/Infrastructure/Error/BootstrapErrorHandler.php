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

namespace Dbm\Infrastructure\Error;

use Throwable;

final class BootstrapErrorHandler
{
    public static function handle(Throwable $e, string $baseDirectory): void
    {
        self::log($e, $baseDirectory);
        self::render($e);
    }

    private static function getEnv(): string
    {
        return $_ENV['APP_ENV']
            ?? $_SERVER['APP_ENV']
            ?? 'production';
    }

    private static function basePath(): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

        if (str_contains($scriptName, '/public/')) {
            return substr($scriptName, 0, strpos($scriptName, '/public'));
        }

        return rtrim(dirname($scriptName), '/');
    }

    private static function isApiRequest(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $path = parse_url($uri, PHP_URL_PATH) ?? '';

        return str_starts_with($path, '/api');
    }

    private static function log(Throwable $e, string $base): void
    {
        $dir = $base . '/var/log';

        if (!is_dir($dir) && !mkdir($dir, 0o775, true) && !is_dir($dir)) {
            throw new \RuntimeException("Cannot create log directory: $dir");
        }

        $file = $dir . '/' . date('Ymd') . '_bootstrap.log';

        $message = sprintf(
            "[BOOTSTRAP] Date: %s, Exception: %s\n Message: %s\n File: %s\n Line: %d\n Trace:\n%s",
            date('Y-m-d H:i:s'),
            $e::class,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        file_put_contents(
            $file,
            $message . PHP_EOL . str_repeat('-', 80) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    private static function render(Throwable $e): void
    {
        $env = self::getEnv();

        if ($env !== 'development') {
            if (self::isApiRequest()) {
                self::renderApi();
            } else {
                self::renderProduction();
            }
            return;
        }

        self::renderDevelopment($e);
    }

    private static function renderApi(): void
    {
        http_response_code(500);

        header('Content-Type: application/json');

        echo json_encode([
            'status' => 500,
            'error' => 'Server Error',
        ]);

        exit;
    }

    private static function renderProduction(): void
    {
        $base = self::basePath();
        $status = 500;

        http_response_code(302);
        header("Location: {$base}/errors/error.html?code={$status}");
        exit;
    }

    private static function renderDevelopment(Throwable $e): void
    {
        http_response_code(500);

        $message = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        $file = htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8');
        $line = (int) $e->getLine();
        $trace = htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8');

        echo <<<HTML
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <title>DbM Framework - Fatal error</title>
                <style>
                    .dbm-ex-root, .dbm-ex-root * { all: revert; box-sizing: border-box; }
                    .dbm-ex-root { font-family: monospace; font-size: 16px; background: #f4f4f4; color: #333; padding: 2rem; }
                    .container { max-width: 992px; margin: auto; background: #fff; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden; }
                    .header { display: flex; justify-content: space-between; padding: 1rem; background: #e11d48; color: #fff; font-weight: normal; }
                    .page { font-size: 1.4rem; font-weight: bold; }
                    .navigation { font-size: 1rem; }
                    .title { font-size: 0.9rem; color: #cbd5e1; }
                    .description { text-align: right; }
                    .description a { font-size: 0.7rem; color: #cbd5e1; text-decoration: none; text-transform: uppercase; }
                    .main { padding: 2rem; }
                    .main p { margin-top: 0.5rem; margin-bottom: 0.5rem; }
                    .message { font-family: "Lucida Console", monospace; padding: 1rem; border: 1px solid #111827; background: #334155; color: #fff; border-radius: 5px; word-break: break-all; }
                    .info { padding: 1rem; background: #f3f4f6; border: 1px solid #d1d5db; color: #000; margin-top: 1rem; border-radius: 5px; word-break: break-all; }
                    .details { margin-top: 1rem; border-radius: 5px; border: 1px solid #d1d5db; background: #f9fafb; color: #000; }
                    .details summary { cursor: pointer; padding: 0.75rem 1rem; font-weight: bold; font-size: 14px; background: #f3f4f6; }
                    .details pre { max-height: 400px; overflow: auto; font-size: 12px; padding: 1rem; margin: 0; font-family: monospace; }
                </style>
            </head>
            <body class="dbm-ex-root">
                <div class="container">
                    <div class="header">
                        <div class="page">Fatal bootstrap error</div>
                        <div class="navigation">
                            <div class="title">DbM Framework Exception</div>
                            <div class="description"><a href="https://dbm.org.pl/">Go To Project</a></div>
                        </div>
                    </div>
                    <div class="main">
                        <div class="message">
                            <p><strong>Message:</strong> {$message}</p>
                        </div>
                        <div class="info">
                            <p><strong>File:</strong> {$file}</p>
                            <p><strong>Line:</strong> {$line}</p>
                        </div>
                        <details class="details">
                            <summary>Trace</summary>
                            <pre>{$trace}</pre>
                        </details>
                    </div>
                </div>
            </body>
            </html>
            HTML;
    }
}
