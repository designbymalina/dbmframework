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
 * Implementacja w index.php:
 *
 * autoloadingWithWithoutComposer($pathComposerAutoload);
 * Benchmark::mark('autoload_ready');
 * require BASE_DIRECTORY . '/application/globals.php';
 * Benchmark::mark('globals_ready');
 * registerModules($container); // INFO! Spowalnia czas, warto zoptymalizować.
 * Benchmark::mark('modules_ready');
 * itd.
 *
 * Odczyt w Kontrolerze:
 *
 * public function start()
 * {
 *     Benchmark::mark('controller_entry');
 *     dump(Benchmark::getStats());
 * }
 */

declare(strict_types=1);

namespace Dbm\Core;

class Benchmark
{
    /** @var array<string, float> */
    private static $markers = [];

    public static function mark(string $name): void
    {
        self::$markers[$name] = microtime(true);
    }

    /**
     * @return array<string, array{since_start: string, delta: string}>
     */
    public static function getStats(): array
    {
        $stats = [];
        $startTime = defined('REQUEST_START_TIME') ? REQUEST_START_TIME : reset(self::$markers);
        $prevTime = $startTime;

        foreach (self::$markers as $name => $time) {
            $stats[$name] = [
                'since_start' => round(($time - $startTime) * 1000, 2) . ' ms',
                'delta' => round(($time - $prevTime) * 1000, 2) . ' ms',
            ];
            $prevTime = $time;
        }

        return $stats;
    }
}
