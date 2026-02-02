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

namespace Mod\Installer\Steps\Helper;

use Lib\Files\FileSystem;

final class CacheHelper
{
    public static function clearCache(): void
    {
        $cacheDir = BASE_DIRECTORY . '/var/cache';

        if (is_dir($cacheDir)) {
            (new FileSystem())->deleteDir($cacheDir);
        }
    }
}
