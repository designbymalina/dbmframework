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

namespace Dbm\Debug;

class DebugRegistry
{
    private static ?DebugToolbar $toolbar = null;

    public static function setToolbar(?DebugToolbar $t): void
    {
        self::$toolbar = $t;
    }

    public static function getToolbar(): ?DebugToolbar
    {
        return self::$toolbar;
    }
}
