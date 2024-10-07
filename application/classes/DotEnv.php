<?php
/*
 * Application: DbM Framework v2
 * Author: Arthur Malinowsky (Design by Malina)
 * License: MIT
 * Web page: www.dbm.org.pl
 * Contact: biuro@dbm.org.pl
*/

declare(strict_types=1);

namespace Dbm\Classes;

use InvalidArgumentException;
use RuntimeException;

class DotEnv
{
    /**
     * The directory where the .env file can be located.
     *
     * @var string
     */
    protected $path;

    public function __construct(string $path)
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException(sprintf('ERROR! File %s does not exist.', $path));
        }

        $this->path = $path;
    }

    public function load(): void
    {
        if (!is_readable($this->path)) {
            throw new RuntimeException(sprintf('ERROR! %s file is not readable.', $this->path));
        }

        $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            if (strpos($line, '=') === false) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);

            $name = trim($name);
            $value = trim($value);

            $value = $this->cleanQuotes($value);
            $value = $this->resolveReferences($value);

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }

    /**
     * Clean the quotes from the value if present.
     *
     * @param string $value
     * @return string
     */
    private function cleanQuotes(string $value): string
    {
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            return substr($value, 1, -1);
        }
        return $value;
    }

    /**
     * Replace variable references like ${VAR} with their respective values.
     *
     * @param string $value
     * @return string
     */
    private function resolveReferences(string $value): string
    {
        return preg_replace_callback('/\${([A-Z0-9_]+)}/', function ($matches) {
            $varName = $matches[1];
            return $_ENV[$varName] ?? $_SERVER[$varName] ?? $matches[0];
        }, $value);
    }

    /* TO TEST!
     * Convert boolean-like values (true/false strings) to actual booleans.
     *
     * @param string $value
     * @return bool|string
     *
    public function convertToBoolean(string $value)
    {
        $lowerValue = strtolower($value);

        if (($lowerValue === 'false') || ($lowerValue === null)) {
            return false; // will work for false and null and gives "null"
        } elseif (($lowerValue === 'true') || ($lowerValue === '1')) {
            return true; // gives 1 (type string)
        }

        return $value;
    } */
}
