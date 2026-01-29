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

// Strict typing
declare(strict_types=1);

// Importing required classes from namespace
use Dbm\Core\DependencyContainer;
use Dbm\Core\DotEnv;
use Dbm\Database\Contracts\DatabaseInterface;
use Dbm\Exceptions\ExceptionHandler;

// Output buffering
ob_start();

// Define constants
$baseDirectory = realpath(dirname(__DIR__));

if ($baseDirectory === false) {
    throw new RuntimeException('Cannot resolve BASE_DIRECTORY');
}

define('REQUEST_START_TIME', microtime(true));
define('BASE_DIRECTORY', rtrim(str_replace('\\', '/', $baseDirectory), '/'));

// Include core functionalities
require_once BASE_DIRECTORY . '/application/start.php';

// Initialize configuration and autoloading
$pathConfig = BASE_DIRECTORY . '/.env';
$pathComposerAutoload = BASE_DIRECTORY . '/vendor/autoload.php';

try {
    // Load configuration
    configurationSettings($pathConfig);

    // Autoloading with or without Composer
    autoloadingWithWithoutComposer($pathComposerAutoload);

    // Load environment variables
    $dotenv = new DotEnv($pathConfig);
    $dotenv->load();

    // Set error handling
    setupErrorHandling();

    // Start session
    initializeSession();

    // Creating DI Container
    $container = new DependencyContainer();
    // Service registration
    $servicesConfig = require BASE_DIRECTORY . '/application/services.php';
    $servicesConfig($container);

    // Initialize global templates, need $container
    require BASE_DIRECTORY . '/application/globals.php';

    // Getting Database Instance from DI
    $database = isConfigDatabase() ? $container->get(DatabaseInterface::class) : null;

    // Bootstrapping module - option for CMS
    registerModules($container);

    // Start routing kernel
    runRoutingKernel($container);
} catch (Throwable $exception) {
    $env = getenv('APP_ENV');
    $env = is_string($env) ? $env : 'production';
    (new ExceptionHandler())->handle($exception, $env);
}
