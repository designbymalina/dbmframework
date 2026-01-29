<?php

declare(strict_types=1);

/**
 * Application: DbM Framework (bootstrap application)
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 */

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

/* Optional - future helper
function basePath(string $path = ''): string {
    return BASE_DIRECTORY . ($path ? '/' . ltrim($path, '/') : '');
}*/

function configurationSettings(string $pathConfig): void
{
    if (!file_exists($pathConfig)) {
        die('CONFIGURATION! Configure the application to run the program, then rename the .env.example file to .env.');
    }
}

/**
 * Minimal path helper for bootstrap autoloader, compatible with Dbm\Support\Normalize\Path:join()
 */
function pathJoin(string ...$parts): string
{
    return implode('/', array_map(static fn($p) => trim(str_replace('\\', '/', $p), '/'), $parts));
}

/**
 * Autoloading with without Composer
 *
 * @param string $pathComposerAutoload
 * @return void
 */
function autoloadingWithWithoutComposer(string $pathComposerAutoload): void
{
    if (is_file($pathComposerAutoload)) {
        require $pathComposerAutoload;
        return;
    }

    spl_autoload_register(static function (string $className): void {

        // Ignore template-generated classes
        if (str_starts_with($className, '__Tpl_')) {
            return;
        }

        /**
         * === PSR-4 namespace, directory map
         */
        $namespaceMap = [
            'App' => 'src',
            'Dbm' => 'application',
            'Lib' => 'application/Libraries',
            'Mod' => 'modules',
        ];

        foreach ($namespaceMap as $prefix => $baseDir) {
            if (!str_starts_with($className, $prefix)) {
                continue;
            }

            $relativeClass = ltrim(substr($className, strlen($prefix)), '\\');

            $filePath = pathJoin(
                BASE_DIRECTORY,
                $baseDir,
                str_replace('\\', '/', $relativeClass)
            ) . '.php';

            if (is_file($filePath)) {
                require_once $filePath;
                return;
            }

            error_log("Autoloader: Class {$className} not found at {$filePath}");
            return;
        }

        /**
         * === External / bundled libraries (PSR, PHPMailer, Guzzle)
         */
        $librariesRoot = pathJoin(BASE_DIRECTORY, 'libraries');
        if (!is_dir($librariesRoot)) {
            return;
        }

        static $loadedLibraries = [];

        $namespaceLibraries = [
            'Psr\\Http\\Message\\' => 'libraries/psr/http-message/src',
            'Psr\\Http\\Client\\' => 'libraries/psr/http-client/src',
            'Psr\\Log\\' => 'libraries/psr/log/src',
            'PHPMailer\\PHPMailer' => 'libraries/phpmailer/src',
            'GuzzleHttp\\Promise' => 'libraries/guzzlehttp/promise/src',
            'GuzzleHttp\\Psr7' => 'libraries/guzzlehttp/psr7/src',
            'GuzzleHttp' => 'libraries/guzzlehttp/guzzle/src',
        ];

        // longest prefix first
        uksort($namespaceLibraries, fn($a, $b) => strlen($b) <=> strlen($a));

        foreach ($namespaceLibraries as $prefix => $libraryPath) {
            if (!str_starts_with($className, $prefix)) {
                continue;
            }

            $relative = ltrim(substr($className, strlen($prefix)), '\\');

            $filePath = pathJoin(
                BASE_DIRECTORY,
                $libraryPath,
                str_replace('\\', '/', $relative)
            ) . '.php';

            if (is_file($filePath)) {
                require_once $filePath;
                return;
            }

            // Fallback: load entire library once
            if (!isset($loadedLibraries[$prefix])) {
                $dir = pathJoin(BASE_DIRECTORY, $libraryPath);

                if (is_dir($dir)) {
                    $it = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
                    );

                    foreach ($it as $file) {
                        if ($file->isFile() && $file->getExtension() === 'php') {
                            require_once $file->getPathname();
                        }
                    }

                    $loadedLibraries[$prefix] = true;
                } else {
                    error_log("Autoloader: Library directory missing: {$dir}");
                }
            }

            return;
        }
    });
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
    $router = $container->get(Router::class);

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
    $allRegistered = iterator_to_array($moduleRegistry->all());

    if (!empty($allRegistered)) {
        foreach ($allRegistered as $module) {
            $moduleRegistry->enable($module->getKey());
        }
    }

    // Dodaj trasy modułów (last wins)
    $moduleRegistry->registerRoutes($routeBuilder);

    // === 3. Zapisz cache, jeśli jest włączony ===
    if (!$cache->isFresh($sources) && getenv('CACHE_ENABLED') === 'true') {
        $cache->write($routes);
    }

    // === 4. Middleware ===
    loadMiddleware($middleware);

    // === 5. Dispatch ===
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
