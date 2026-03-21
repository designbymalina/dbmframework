<?php

declare(strict_types=1);

// Test command: vendor/bin/phpunit tests/Unit/ExampleUnitTest.php
// Definiujemy stałe wymagane w projekcie, np. BASE_DIRECTORY
$basePath = realpath(dirname(__DIR__));

if ($basePath === false) {
    throw new RuntimeException('Cannot resolve base directory path.');
}

if (!defined('BASE_DIRECTORY')) {
    define('BASE_DIRECTORY', rtrim(str_replace('\\', '/', $basePath), '/'));
}

// Wczytujemy autoload z Composer
$pathAutoload = BASE_DIRECTORY . '/vendor/autoload.php';
require_once($pathAutoload);
