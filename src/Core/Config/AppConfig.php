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

final class AppConfig
{
    public const ENV_PRODUCTION = 'production';
    public const ENV_DEVELOPMENT = 'development';

    public static function getEnv(): string
    {
        return getenv('APP_ENV') ?: self::ENV_DEVELOPMENT;
    }

    public static function isCacheEnabled(): bool
    {
        return strtolower((string) getenv('CACHE_ENABLED')) === 'true';
    }

    public static function hasDatabase(): bool
    {
        return !empty(getenv('DB_HOST'))
            && !empty(getenv('DB_NAME'))
            && !empty(getenv('DB_USER'));
    }
}
