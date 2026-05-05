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

final class Paths
{
    private static ?string $base = null;

    /** @var array<string, string> */
    private static array $cache = [];

    /** @var array<string, string> */
    private static array $overrides = [];

    // ===== Base =====

    public static function setBasePath(string $base): void
    {
        $base = realpath($base);

        if ($base === false) {
            throw new \RuntimeException('Invalid base path');
        }

        self::$base = self::normalize($base);
        self::$cache = []; // reset cache
    }

    public static function basePath(): string
    {
        if (self::$base === null) {
            throw new \RuntimeException('Base path not initialized');
        }

        return self::$base;
    }

    // ===== Overrides (advanced) =====

    public static function set(string $key, string $path): void
    {
        self::$overrides[$key] = self::normalize($path);
        unset(self::$cache[$key]);
    }

    public static function get(string $key): string
    {
        if (isset(self::$overrides[$key])) {
            return self::$overrides[$key];
        }

        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        throw new \RuntimeException("Path [$key] not defined");
    }

    // ===== Core paths ======

    public static function publicPath(): string
    {
        return self::$cache[__FUNCTION__]
            ??= self::joinPaths(self::basePath(), 'public');
    }

    public static function templatesPath(): string
    {
        return self::$cache[__FUNCTION__]
            ??= self::joinPaths(self::basePath(), 'templates');
    }

    public static function translationsPath(): string
    {
        return self::$cache[__FUNCTION__]
            ??= self::joinPaths(self::basePath(), 'translations');
    }

    public static function srcPath(): string
    {
        return self::$cache[__FUNCTION__]
            ??= self::joinPaths(self::basePath(), 'src');
    }

    public static function varPath(): string
    {
        return self::$cache[__FUNCTION__]
            ??= self::joinPaths(self::basePath(), 'var');
    }

    public static function logPath(): string
    {
        return self::$cache[__FUNCTION__]
            ??= self::joinPaths(self::varPath(), 'log');
    }

    // ===== Helpers =====

    public static function joinPaths(string ...$parts): string
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

    private static function normalize(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }
}
