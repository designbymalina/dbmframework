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
 * Klasa do wdrożenia zamiast BASE_DIRECTORY itp..
 * - - -
 *
 * Przykład jak używać w kodzie
 *
 * Zamiast:
 * BASE_DIRECTORY . '/application/start.php'
 * Używaj:
 * Paths::appPath() . '/start.php'
 *
 * @INFO Będąc przy start.php - urosło do takich rozmiarów,
 * że warto przenieść do klasy/klas o osobnej odpowiedzialności.
 * Zmienić na 'bootstrap', może dopisać kernel.
 */

declare(strict_types=1);

namespace Dbm\Core;

final class Paths
{
    private static ?string $base = null;

    public static function basePath(): string
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

    public static function appPath(): string
    {
        return self::basePath() . '/application';
    }

    public static function publicPath(): string
    {
        return self::basePath() . '/public';
    }

    public static function storagePath(): string
    {
        return self::basePath() . '/storage';
    }

    public static function join(string ...$parts): string
    {
        $clean = [];

        foreach ($parts as $i => $part) {
            $part = str_replace('\\', '/', $part);

            $part = $i === 0
                ? rtrim($part, '/')
                : trim($part, '/');

            if ($part !== '') {
                $clean[] = $part;
            }
        }

        return implode('/', $clean);
    }
}
