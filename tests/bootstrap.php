<?php

// Test command: vendor/bin/phpunit tests/Unit/ExampleUnitTest.php
// Definiujemy stałe wymagane w projekcie, np. BASE_DIRECTORY
if (!defined('BASE_DIRECTORY')) {
    define(
        'BASE_DIRECTORY',
        rtrim(str_replace('\\', '/', realpath(dirname(__DIR__))), '/')
    );
}

// Wczytujemy autoload z Composer
$pathAutoload = BASE_DIRECTORY . '/vendor/autoload.php';
require_once($pathAutoload);
