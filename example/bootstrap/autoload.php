<?php

/**
 * DBM Framework
 *
 * @license MIT
 * @link https://www.dbm.org.pl
 */

declare(strict_types=1);

// --- Application autoloader for the example ---
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    $baseDirectory = __DIR__ . '/../src/';

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $file = $baseDirectory
        . str_replace('\\', '/', substr($class, strlen($prefix)))
        . '.php';

    if (is_file($file)) {
        require $file;
    }
});
