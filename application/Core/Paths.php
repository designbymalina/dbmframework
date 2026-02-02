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
 * Przykład jak używać w kodzie
 * - - -
 * Zamiast:
 * BASE_DIRECTORY . '/application/start.php'
 * Używaj:
 * Paths::application() . '/start.php'
 */

declare(strict_types=1);

namespace Dbm\Core;

final class Paths
{
    private static ?string $base = null;

    public static function base(): string
    {
        if (self::$base !== null) {
            return self::$base;
        }

        $baseDirectory = realpath(dirname(__DIR__, 2));
        // __DIR__ = application/Core
        // dirname(..., 2) = project root

        if ($baseDirectory === false) {
            throw new \RuntimeException('Cannot resolve base directory');
        }

        return self::$base = rtrim(
            str_replace('\\', '/', $baseDirectory),
            '/'
        );
    }

    public static function application(): string
    {
        return self::base() . '/application';
    }

    public static function public(): string
    {
        return self::base() . '/public';
    }

    public static function storage(): string
    {
        return self::base() . '/storage';
    }
}
