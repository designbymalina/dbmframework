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

use Dbm\Support\Helpers\DebugHelper;

/**
 * Core dump function for debugging (available globally)
 * - Uses DebugHelper for consistent formatting
 */
if (!function_exists('dump')) {
    /**
     * @param mixed ...$vars Variables to dump
     * @return void
     */
    function dump(mixed ...$vars): void
    {
        foreach ($vars as $var) {
            DebugHelper::dump($var);
        }
    }
}
