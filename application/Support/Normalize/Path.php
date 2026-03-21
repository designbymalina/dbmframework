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
 * Example of usage:
 * $filePath = PathNormalizer::join(BASE_DIRECTORY, $matchedPath, $relativeClass) . '.php';
 */

declare(strict_types=1);

namespace Dbm\Support\Normalize;

final class Path
{
    public static function normalize(string $path): string
    {
        return trim(str_replace('\\', '/', $path), '/');
    }

    public static function join(string ...$parts): string
    {
        return implode(
            '/',
            array_map(
                static fn($p) => trim(str_replace('\\', '/', $p), '/'),
                $parts
            )
        );
    }
}
