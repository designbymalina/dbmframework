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

namespace Dbm\Core\Module;

final class InstallerContext
{
    private static bool $running = false;

    public static function enable(): void
    {
        self::$running = true;
    }

    public static function isRunning(): bool
    {
        return self::$running;
    }
}
