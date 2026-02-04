<?php

/**
 * Application: DbM Framework (bootstrap application)
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
use Dbm\Core\Module\InstallerContext;
use Dbm\Core\Module\ModuleBootstrapper;
use Dbm\Core\Module\ModuleRegistry;
use Dbm\Core\Module\Package\PackageScanner;
use Dbm\Exceptions\ExceptionHandler;
use Dbm\Routing\MiddlewareStack;
use Dbm\Routing\RouteBuilder;
use Dbm\Routing\RouteCache;
use Dbm\Routing\RouteCollection;
use Dbm\Routing\Router;
use Dbm\Support\Helpers\DebugHelper;
use Dbm\Views\TemplateEngine;

### Error / debug functions ###

function setupErrorHandling(): void
{
    error_reporting(E_ALL);

    $env = getenv('APP_ENV');
    $env = is_string($env) ? $env : 'production';

    ini_set('display_errors', $env === 'production' ? '0' : '1');

    set_error_handler('reportingErrorHandler');

    set_exception_handler(
        static function (Throwable $exception) use ($env): void {
            (new ExceptionHandler())->handle($exception, $env);
        }
    );
}

function reportingErrorHandler(int $errLevel, string $errMessage, string $errFile, int $errLine): bool
{
    logErrorToFile($errLevel, $errMessage, $errFile, $errLine);

    $exceptionHandler = new ExceptionHandler();
    $exception = new ErrorException($errMessage, $errLevel, 0, $errFile, $errLine);
    $exceptionHandler->handle($exception, getenv('APP_ENV') ?: 'production');

    return true;
}

function logErrorToFile(int $errLevel, string $errMessage, string $errFile, int $errLine): void
{
    $basename = 'index';
    $uri = $_SERVER["REQUEST_URI"];
    $dir = str_replace('public', '', dirname($_SERVER['PHP_SELF']));

    if ($uri !== $dir) {
        $basename = str_replace('.html', '', basename($uri));
        $basename = preg_replace('/[\/\\\\\:\*\?\"\<\>\|\=\&]/', '_', $basename);
        $basename = preg_replace('/\s+/', '_', $basename);
        $basename = preg_replace('/[^\w\.\-]/', '', $basename);
    }

    $date = date('Y-m-d H:i:s');
    $file = date('Ymd') . '_' . strtolower($basename) . '.log';
    $dir = BASE_DIRECTORY . '/var/log/';
    $path = $dir . $file;

    if (!is_dir($dir)) {
        mkdir($dir, 0o755, true);
    }

    $errorHandler = "DATE: $date, level: $errLevel\n File: $errFile on line $errLine\n Message: $errMessage\n";

    file_put_contents($path, $errorHandler, FILE_APPEND);
}

### Bootstrap / env functions ###

function configurationSettings(string $pathConfig): void
{
    if (!file_exists($pathConfig)) {
        die('CONFIGURATION! Configure the application to run the program, then rename the .env.example file to .env.');
    }
}

/**
 * Autoloading with without Composer
 *
 * @param string $pathComposerAutoload
 * @return void
 *
 * PSR-4 compliant autoloader
 * - absolute paths only
 * - Linux / Windows safe
 * - supports bundled libraries (PSR, Guzzle, PHPMailer)
 */
function autoloadingWithWithoutComposer(string $pathComposerAutoload): void
{
    if (is_file($pathComposerAutoload)) {
        require_once $pathComposerAutoload;
        return;
    }

    $base = rtrim(BASE_DIRECTORY, '/');

    // --- MAIN PSR-4 MAP ---
    $psr4 = [
        'App\\' => $base . '/src/',
        'Dbm\\' => $base . '/application/',
        'Mod\\' => $base . '/modules/',
        'Lib\\' => $base . '/application/Libraries/', // fallback
    ];

    // --- BUNDLED / EXTERNAL LIBRARIES ---
    $libraries = [
        'Psr\\Http\\Message\\' => $base . '/libraries/psr/http-message/src/',
        'Psr\\Http\\Client\\' => $base . '/libraries/psr/http-client/src/',
        'Psr\\Log\\' => $base . '/libraries/psr/log/src/',
        'PHPMailer\\PHPMailer\\' => $base . '/libraries/phpmailer/src/',
        'GuzzleHttp\\Promise\\' => $base . '/libraries/guzzlehttp/promise/src/',
        'GuzzleHttp\\Psr7\\' => $base . '/libraries/guzzlehttp/psr7/src/',
        'GuzzleHttp\\' => $base . '/libraries/guzzlehttp/guzzle/src/',
    ];

    // --- FRAMEWORK-BUNDLED PACKAGES ---
    $bundles = [
        'Lib\\DataTables\\' => $base . '/application/Libraries/DataTables/src/',
        'Lib\\Search\\' => $base . '/application/Libraries/Search/src/',
    ];

    // order matters: more specific first
    $maps = $bundles + $libraries + $psr4;

    spl_autoload_register(
        static function (string $class) use ($maps): void {

            if ($class[0] === '_') {
                return;
            }

            foreach ($maps as $prefix => $dir) {
                if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
                    continue;
                }

                $file = $dir
                    . str_replace('\\', '/', substr($class, strlen($prefix)))
                    . '.php';

                if (is_file($file)) {
                    require $file;
                }

                return;
            }
        },
        true,
        true
    );
}

function initializeSession(): void
{
    $isProduction = getenv('APP_ENV') === 'production';

    session_start([
        'cookie_lifetime' => 0,
        'cookie_secure' => $isProduction,
        'cookie_httponly' => true,
        'use_strict_mode' => true,
        'use_only_cookies' => true,
    ]);
}

function isConfigDatabase(): bool
{
    return !empty(getenv('DB_HOST')) && !empty(getenv('DB_NAME')) && !empty(getenv('DB_USER'));
}

### Routing kernel functions ###

/**
 * Routing Kernel - INFO: Można zrobić HTTP Kernel jako klasę (PSR-15 style).
 *
 * @param DependencyContainer $container
 * @return void
 */
function runRoutingKernel(DependencyContainer $container): void
{
    $cache = new RouteCache(BASE_DIRECTORY . '/var/cache/__routes.php');

    $routes = $container->get(RouteCollection::class);
    $routeBuilder = $container->get(RouteBuilder::class);
    $middleware = $container->get(MiddlewareStack::class);

    $sources = [
        BASE_DIRECTORY . '/application/web.php',
        BASE_DIRECTORY . '/application/api.php',
    ];

    // === 1. Załaduj standardowe trasy z web.php + api.php ===
    if (!InstallerContext::isRunning() || !$cache->isFresh($sources)) {
        loadRoutes($routeBuilder, $container);
    } else {
        $routes->import($cache->load());
    }

    // === 2. Rejestracja modułów ===
    /** @var ModuleRegistry $moduleRegistry */
    $moduleRegistry = $container->get(ModuleRegistry::class);
    foreach (iterator_to_array($moduleRegistry->all()) as $module) {
        $moduleRegistry->enable($module->getKey());
    }

    // Dodaj trasy modułów (last wins)
    $moduleRegistry->registerRoutes($routeBuilder);

    // === 3. Zapisz cache, jeśli jest włączony ===
    if (!$cache->isFresh($sources) && getenv('CACHE_ENABLED') === 'true') {
        $cache->write($routes);
    }

    // === 4. Middleware ===
    loadMiddleware($middleware);

    // === 5. Router (po middleware) ===
    $router = $container->get(Router::class);

    // === 6. Dispatch ===
    dispatchRequest($router);
}

function loadRoutes(RouteBuilder $routes, DependencyContainer $container): void
{
    $webRoutes = require BASE_DIRECTORY . '/application/web.php';
    $apiRoutes = require BASE_DIRECTORY . '/application/api.php';

    $webRoutes($routes, $container);

    $routes->group('/api', function (RouteBuilder $routes) use ($apiRoutes, $container): void {
        $apiRoutes($routes, $container);
    });
}

function loadMiddleware(MiddlewareStack $middleware): void
{
    $middlewares = require BASE_DIRECTORY . '/application/middleware.php';
    $middlewares($middleware);
}

function dispatchRequest(Router $router): void
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $router->dispatch($uri);
}

/**
 * Registering modules
 * INFO: Opcjonalnie można rozbudować, modules.php (manifest), cache listy modułów, tryb APP_ENV=prod -> bez skanowania FS
 *
 * @param DependencyContainer $container
 */
function registerModules(DependencyContainer $container): void
{
    $modulesDir = BASE_DIRECTORY . '/modules';
    $pathConfig = BASE_DIRECTORY . '/config/modules.php';
    $installedLock = BASE_DIRECTORY . '/modules/installed.lock';

    if (!is_dir($modulesDir)) {
        return;
    }

    $registry = $container->get(ModuleRegistry::class);
    $bootstrapper = $container->get(ModuleBootstrapper::class);
    $scanner = $container->get(PackageScanner::class);

    /** BOOTSTRAP MODUŁÓW */
    if (is_file($pathConfig)) {
        $bootstrapper->bootFromConfig(require $pathConfig);
    }

    // Installer dostępny ZAWSZE
    if (!is_file($installedLock) || $scanner->hasPendingPackages()) {
        $bootstrapper->bootInstaller();
    }

    $registry->bootAll(
        $container->get(RouteBuilder::class),
        $container->get(TemplateEngine::class)
    );
}

### Registering helper functions ###

if (!function_exists('dump')) {
    function dump(mixed ...$vars): void
    {
        foreach ($vars as $var) {
            DebugHelper::dump($var);
        }
    }
}
