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

namespace Dbm\Support\Sanitize;

final class ValueSanitizer
{
    public static function text(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = strip_tags($value);
        $value = preg_replace('/\s+/u', ' ', $value);
        $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);

        return trim($value);
    }

    public static function email(?string $value): ?string
    {
        $value = self::text($value);

        if ($value === null || $value === '') {
            return null;
        }

        $value = mb_strtolower($value);

        return filter_var($value, FILTER_VALIDATE_EMAIL) ?: null;
    }

    public static function slug(?string $value): ?string
    {
        $value = self::text($value);

        if ($value === null || $value === '') {
            return null;
        }

        $value = strtolower($value);

        return preg_replace('/[^a-z0-9\-_]/', '', $value);
    }

    public static function locale(?string $value): ?string
    {
        $value = self::text($value);

        if ($value === null || $value === '') {
            return null;
        }

        $value = str_replace('-', '_', $value);

        if (preg_match('/^([a-z]{2})_([a-z]{2})$/i', $value, $m)) {
            return strtolower($m[1]) . '_' . strtoupper($m[2]);
        }

        return strtolower($value);
    }

    public static function country(?string $value): ?string
    {
        $value = strtoupper(trim($value));

        if (preg_match('/^[A-Z]{2}$/', $value)) {
            return $value;
        }

        return null;
    }

    public static function currency(?string $value): ?string
    {
        $value = self::text($value);

        if (preg_match('/^[A-Z]{3}$/', $value)) {
            return $value;
        }

        return null;
    }

    public static function vatNumber(?string $value): ?string
    {
        $value = self::text($value);

        if ($value === null || $value === '') {
            return null;
        }

        $value = preg_replace('/[\s\-\.]+/u', '', $value);

        return strtoupper($value);
    }

    public static function postcode(?string $value): ?string
    {
        $value = self::text($value);

        if ($value === null) {
            return null;
        }

        return strtoupper($value);
    }

    public static function phone(?string $value): ?string
    {
        $value = self::text($value);

        if ($value === null) {
            return null;
        }

        // @INFO Popraw na międzynarodowe numery telefonu
        // $value = preg_replace('/(?!^\+)[^\d]/', '', $value);

        return $value;
    }

    public static function ip(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_IP) ?: null;
    }

    public static function url(?string $value): ?string
    {
        $value = self::text($value);

        if ($value === null) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_URL) ?: null;
    }

    public static function filename(?string $value): ?string
    {
        $value = self::text($value);

        if ($value === null) {
            return null;
        }

        return preg_replace('/[\\\\\/:*?"<>|]/', '', $value);
    }

    public static function path(?string $value): ?string
    {
        $value = self::text($value);

        if ($value === null) {
            return null;
        }

        $value = str_replace('\\', '/', $value);

        while (str_contains($value, '//')) {
            $value = str_replace('//', '/', $value);
        }

        $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);

        $segments = [];

        foreach (explode('/', $value) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                continue;
            }

            $segments[] = $segment;
        }

        return implode('/', $segments);
    }

    public static function number(mixed $value): int|float
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        $value = str_replace(',', '.', (string) $value);

        return str_contains($value, '.')
            ? (float) $value
            : (int) $value;
    }

    public static function bool(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
