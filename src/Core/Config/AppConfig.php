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

namespace Dbm\Core\Config;

use Dbm\Environment\Environment;

final class AppConfig
{
    public const ENV_PRODUCTION = 'production';
    public const ENV_DEVELOPMENT = 'development';

    public static function env(string $name, string $default = ''): string
    {
        return Environment::get($name, $default);
    }

    public static function getEnv(): string
    {
        return Environment::get('APP_ENV', self::ENV_DEVELOPMENT);
    }

    public static function sessionKey(): string
    {
        $value = Environment::get('APP_SESSION_KEY');

        if ($value === '') {
            throw new \RuntimeException(
                'APP_SESSION_KEY is not configured.'
            );
        }

        return $value;
    }

    public static function isCacheEnabled(): bool
    {
        return strtolower(Environment::get('CACHE_ENABLED')) === 'true';
    }

    public static function hasDatabase(): bool
    {
        return Environment::get('DB_HOST') !== ''
            && Environment::get('DB_NAME') !== ''
            && Environment::get('DB_USER') !== '';
    }

    public static function httpClientDriver(): string
    {
        return self::env('HTTP_CLIENT_DRIVER', 'auto');
    }

    public static function httpClientLog(): bool
    {
        return strtolower(self::env('HTTP_CLIENT_LOG')) === 'true';
    }

    // ===== Debugging =====

    /**
     * Optional for debugging: Logging 404 (as an error).
     * Used in ExceptionMiddleware.
     */
    public static function securityLogs(): bool
    {
        return false;
    }
}
