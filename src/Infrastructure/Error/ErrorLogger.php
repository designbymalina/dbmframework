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

use Dbm\Core\Paths;
use Dbm\Routing\Contracts\UrlGeneratorInterface;
use Throwable;

final class ErrorLogger
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator
    ) {}

    public function exception(Throwable $e, string $type = 'EXCEPTION'): void
    {
        $this->write(
            $this->formatException($e, $type)
        );
    }

    public function error(
        string $message,
        string $file,
        int $line,
        int $level = 0,
        string $type = 'ERROR'
    ): void {
        $this->write(
            $this->formatError($type, $message, $file, $line, $level)
        );
    }

    // ===== FORMAT =====

    private function formatException(Throwable $e, string $type): string
    {
        $trace = '  ' . strtok($e->getTraceAsString(), "\n") . '...';

        return sprintf(
            "[%s] Date: %s, Uri: %s\n Message: %s\n File: %s\n Line: %d\n Trace:\n%s",
            $type,
            date('Y-m-d H:i:s'),
            $this->uri(),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $trace
        );
    }

    private function formatError(
        string $type,
        string $message,
        string $file,
        int $line,
        int $level
    ): string {
        return sprintf(
            "[%s] Date: %s, Uri: %s\n Message: %s\n File: %s\n Line: %d\n Level: %d",
            $type,
            date('Y-m-d H:i:s'),
            $this->uri(),
            $message,
            $file,
            $line,
            $level
        );
    }

    // ===== IO =====

    private function write(string $content): void
    {
        try {
            $dir = Paths::varPath() . '/log/';

            if (!is_dir($dir)) {
                mkdir($dir, 0o755, true);
            }

            $file = $dir . date('Ymd') . '_' . $this->uri() . '.log';

            file_put_contents(
                $file,
                $content . PHP_EOL . str_repeat('-', 80) . PHP_EOL,
                FILE_APPEND | LOCK_EX
            );
        } catch (\Throwable) {
            @error_log($content);
        }
    }

    private function uri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $path = parse_url($uri, PHP_URL_PATH) ?? '';
        $path = $this->urlGenerator->stripBasePath($path);

        $path = trim($path, '/');

        if ($path === '') {
            return 'index';
        }

        $path = str_replace('/', '_', $path);
        $path = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $path);

        return strtolower(substr($path, 0, 50));
    }
}
