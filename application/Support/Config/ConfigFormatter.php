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

namespace Dbm\Support\Config;

final class ConfigFormatter
{
    public static function export(array $config, int $level = 0): string
    {
        $indent = str_repeat('    ', $level);
        $out = "[\n";

        foreach ($config as $key => $value) {
            $out .= $indent . '    ' . self::exportKey($key) . ' => ';

            if (is_array($value)) {
                $out .= self::export($value, $level + 1);
            } else {
                $out .= self::exportValue($value);
            }

            $out .= ",\n";
        }

        $out .= $indent . ']';

        return $out;
    }

    private static function exportKey(string|int $key): string
    {
        return is_int($key)
            ? (string) $key
            : "'" . addslashes($key) . "'";
    }

    private static function exportValue(mixed $value): string
    {
        if (is_string($value)) {
            return "'" . addslashes($value) . "'";
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        return (string) $value;
    }
}
