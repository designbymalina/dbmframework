<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 *
 * Przykład użycia loggera
 * - - -
 * $logger = new Logger();
 * Logowanie komunikatu
 * $logger->info('Użytkownik zalogował się: {username}', ['username' => 'Jan Kowalski']);
 * Logowanie błędu
 * $logger->error('Nie można połączyć z bazą danych.');
 * Logowanie wyjątku
 * try {
 *  throw new \Exception('Testowy wyjątek');
 * } catch (Exception $exception) {
 *  $context = ['query' => $query];
 *  $logger->critical($exception->getMessage() . " | Query: {query}", $context);
 * }
 */

declare(strict_types=1);

namespace Dbm\Infrastructure\Log;

use Dbm\Core\Paths;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Stringable;

class Logger implements LoggerInterface
{
    private const VALID_LEVELS = [
        LogLevel::EMERGENCY,
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::ERROR,
        LogLevel::WARNING,
        LogLevel::NOTICE,
        LogLevel::INFO,
        LogLevel::DEBUG,
    ];

    /**
     * @param string|\Stringable $message
     * @param array<string, mixed> $context
     */
    public function emergency($message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     * @param array<string, mixed> $context
     */
    public function alert($message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     * @param array<string, mixed> $context
     */
    public function critical($message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     * @param array<string, mixed> $context
     */
    public function error($message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     * @param array<string, mixed> $context
     */
    public function warning($message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     * @param array<string, mixed> $context
     */
    public function notice($message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     * @param array<string, mixed> $context
     */
    public function info($message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * @param string|\Stringable $message
     * @param array<string, mixed> $context
     */
    public function debug($message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     * @param mixed $level
     * @param mixed $message
     * @param array<string, mixed> $context
     * @return void
     * @throws InvalidArgumentException
     */
    public function log($level, $message, array $context = []): void
    {
        // Validate log level according to PSR-3
        if (!in_array($level, self::VALID_LEVELS, true)) {
            throw new InvalidArgumentException(
                sprintf('Invalid log level: %s. Must be one of: %s', $level, implode(', ', self::VALID_LEVELS))
            );
        }

        $logDir = Paths::joinPaths(Paths::logPath(), 'logger');
        $logFile = $logDir . '/' . date('Ymd') . "_{$level}.log";

        // Create log directory if it doesn't exist
        if (!is_dir($logDir)) {
            if (!mkdir($logDir, 0o775, true) && !is_dir($logDir)) {
                @error_log("Logger: Failed to create log directory: $logDir");
                return;
            }
        }

        // Convert Stringable to string
        if ($message instanceof \Stringable) {
            $messageString = (string) $message;
        } elseif (is_string($message)) {
            $messageString = $message;
        } else {
            $messageString = json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        // Interpolate message with context
        $interpolatedMessage = $this->interpolateMessage($messageString, $context);

        // Normalize context
        $normalizedContext = $this->normalizeContext($context);
        $jsonContext = json_encode(
            $normalizedContext,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        $logEntry = sprintf(
            "[%s] Date: %s\nMessage: %s\nContext:\n%s\n%s",
            strtoupper($level),
            date('Y-m-d H:i:s'),
            $interpolatedMessage,
            $jsonContext,
            str_repeat('-', 80) . PHP_EOL
        );

        // Check if directory is writable
        if (!is_writable($logDir)) {
            @error_log("Logger: Log directory is not writable: $logDir");
            return;
        }

        // Write to log file
        $result = @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        if ($result === false) {
            @error_log("Logger: Failed to write to log file: $logFile");
        }

        // @INFO Rotate logs (optional)
        // $this->rotateLogs($logDir);
    }

    /**
     * Interpolates placeholders in the message with context values.
     *
     * @param string $message Message with placeholders {key}
     * @param array <string, mixed> $context Context array with values to interpolate
     * @return string Message with interpolated values
     */
    private function interpolateMessage(string $message, array $context): string
    {
        foreach ($context as $key => $value) {

            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            }

            if ($value instanceof \Stringable) {
                $value = (string) $value;
            }

            if (is_scalar($value)) {
                $message = str_replace("{{$key}}", (string) $value, $message);
            }
        }

        return $message;
    }

    /**
     * @param array <string, mixed> $context
     * @return array <string, mixed>
     */
    private function normalizeContext(array $context): array
    {
        foreach ($context as $key => $value) {
            if ($value instanceof \Throwable) {
                $trace = explode("\n", $value->getTraceAsString());
                $trace = array_slice($trace, 0, 5);

                $context[$key] = [
                    'file' => $value->getFile(),
                    'line' => $value->getLine(),
                    'trace' => implode("\n", $trace),
                ];

                continue;
            }

            if ($value instanceof \Stringable) {
                $context[$key] = (string) $value;
                continue;
            }

            if (!is_scalar($value) && !is_array($value)) {
                $context[$key] = (string) json_encode($value);
            }
        }

        return $context;
    }

    /* // Log rotation
    private function rotateLogs(string $dir): void
    {
        $files = glob($dir . '/*.log');

        $maxAge = 14 * 24 * 60 * 60; // 14 dni
        $maxSize = 10 * 1024 * 1024; // 10MB

        foreach ($files as $file) {
            // usuń stare
            if (filemtime($file) < (time() - $maxAge)) {
                @unlink($file);
                continue;
            }

            // przytnij za duże
            if (filesize($file) > $maxSize) {
                file_put_contents($file, ''); // truncate
            }
        }
    } */
}
