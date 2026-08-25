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

namespace Dbm\Core;

use Dbm\Environment\Environment;
use InvalidArgumentException;
use RuntimeException;

class DotEnv
{
    /**
     * The directory where the .env file can be located.
     */
    protected string $path;

    public function __construct(string $path)
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException(
                sprintf('ERROR! File %s does not exist or is not readable.', $path)
            );
        }

        $this->path = $path;
    }

    /**
     * Create an immutable instance of DotEnv.
     */
    public static function createImmutable(string $path): self
    {
        return new self($path);
    }

    /**
     * Load environment variables from the .env file.
     *
     * All variables are collected before references such as
     * ${APP_NAME} are resolved. Therefore, the order of variables
     * in the .env file does not matter.
     *
     * @throws RuntimeException if the file is not readable
     */
    public function load(): void
    {
        if (!is_readable($this->path)) {
            throw new RuntimeException(
                sprintf('ERROR! %s file is not readable.', $this->path)
            );
        }

        $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            throw new RuntimeException(
                sprintf('ERROR! Unable to read %s file.', $this->path)
            );
        }

        /** @var array<string, string> $variables */
        $variables = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);

            $name = trim($name);
            $value = $this->cleanQuotes(trim($value));

            if ($name === '') {
                continue;
            }

            $variables[$name] = $value;
        }

        /**
         * Resolve references after all variables have been collected.
         */
        foreach ($variables as $name => $value) {
            $variables[$name] = $this->resolveReferences($value, $variables);
        }

        /**
         * Register variables without overwriting values that were
         * already supplied by the server/runtime environment.
         */
        foreach ($variables as $name => $value) {
            if (isset($_SERVER[$name]) || isset($_ENV[$name])) {
                continue;
            }

            putenv($name . '=' . $value);

            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }

    /**
     * Clean quotes from the value if present.
     */
    private function cleanQuotes(string $value): string
    {
        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"'))
            || (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            return substr($value, 1, -1);
        }

        return $value;
    }

    /**
     * @param array<string, string> $variables
     */
    private function resolveReferences(
        string $value,
        array $variables
    ): string {
        return preg_replace_callback(
            '/\${([A-Z0-9_]+)}/',
            static function (array $matches) use ($variables): string {
                return $variables[$matches[1]]
                    ?? Environment::get($matches[1], $matches[0]);
            },
            $value
        ) ?? $value;
    }
}
