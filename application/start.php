<?php

/**
 * Application: DbM Framework (bootstrap application)
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 *
 * INFO! Plik (bootstrap) rozrósł się i robi za dużo,
 * dobrze byłoby przenieść zawartość do klas.
 */

// Strict typing
declare(strict_types=1);

// Importing required classes from namespace
use Dbm\Core\DependencyContainer;
use Dbm\Core\Module\Filesystem\PathResolver;
use Dbm\Core\Module\ModuleBootstrapper;
use Dbm\Core\Module\ModuleRegistry;
use Dbm\Core\Module\Package\PackageScanner;
use Dbm\Exceptions\ExceptionHandler;
use Dbm\Infrastructure\Session\SessionManager;
use Dbm\Routing\MiddlewareStack;
use Dbm\Routing\RouteBuilder;
use Dbm\Routing\Router;
use Dbm\Support\Helpers\DebugHelper;

### Error / debug functions ###

function setupErrorHandling(): void
{
    error_reporting(E_ALL);

    $env = getenv('APP_ENV');
    $env = is_string($env) ? $env : 'production';

    ini_set('display_errors', $env === 'production' ? '0' : '1');

    set_error_handler('reportingErrorHandler');

    set_exception_handler(
        static function (\Throwable $exception) use ($env): void {
            (new ExceptionHandler())->handle($exception, $env);
        }
    );
}

function reportingErrorHandler(int $errLevel, string $errMessage, string $errFile, int $errLine): bool
{
    logErrorToFile($errLevel, $errMessage, $errFile, $errLine);

    $exceptionHandler = new ExceptionHandler();
    $exception = new \ErrorException($errMessage, $errLevel, 0, $errFile, $errLine);
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
    // If there is a Composer, we only use him
    if (is_file($pathComposerAutoload)) {
        require_once $pathComposerAutoload;
        return;
    }

    $base = rtrim(BASE_DIRECTORY, '/');

    // ===== CORE PSR-4 MAP =====
    $psr4 = [
        'App\\' => $base . '/src/',
        'Dbm\\' => $base . '/application/',
        'Mod\\' => $base . '/modules/',
    ];

    // ===== BUNDLED LIBRARIES =====

    // Vendor-like libraries (can be removed in Composer install mode)
    $libraries = [
        'Psr\\Http\\Message\\' => $base . '/libraries/psr/http-message/src/',
        'Psr\\Http\\Client\\' => $base . '/libraries/psr/http-client/src/',
        'Psr\\Log\\' => $base . '/libraries/psr/log/src/',
        'PHPMailer\\PHPMailer\\' => $base . '/libraries/phpmailer/src/',
        'GuzzleHttp\\Promise\\' => $base . '/libraries/guzzlehttp/promise/src/',
        'GuzzleHttp\\Psr7\\' => $base . '/libraries/guzzlehttp/psr7/src/',
        'GuzzleHttp\\' => $base . '/libraries/guzzlehttp/guzzle/src/',
    ];

    $bundles = [];

    // Auto bundle discovery (if libraries have their own bundle.php file)
    foreach (glob($base . '/libraries/*/bundle.php') as $bundleFile) {
        $bundles += require $bundleFile;
    }

    // Runtime bundles (modifiable - can be transferred to the Database)
    $runtimeBundles = $base . '/storage/framework/bundles.php';

    if (is_file($runtimeBundles)) {
        $bundles += require $runtimeBundles;
    }

    // Merge maps (priority: bundles > libraries > core)
    // Order matters: more specific first
    $maps = $bundles + $libraries + $psr4;

    spl_autoload_register(
        static function (string $class) use ($maps): void {

            if ($class === '' || $class[0] === '_') {
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
    $routeBuilder = $container->get(RouteBuilder::class);
    $middleware = $container->get(MiddlewareStack::class);

    loadRoutes($routeBuilder, $container);

    /** @var ModuleRegistry $moduleRegistry */
    $moduleRegistry = $container->get(ModuleRegistry::class);
    $moduleRegistry->registerRoutes($routeBuilder);

    loadMiddleware($middleware); // routes from modules

    $router = $container->get(Router::class);

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
 *
 * @param DependencyContainer $container
 */
function registerModules(DependencyContainer $container): void
{
    $installedLock = PathResolver::installerLock();

    $bootstrapper = $container->get(ModuleBootstrapper::class);
    $scanner = $container->get(PackageScanner::class);
    $session = $container->get(SessionManager::class);

    // Boot modułów
    if (is_dir(BASE_DIRECTORY . '/modules')) {
        $bootstrapper->bootModules();
    }

    // Boot instalatora
    if (
        !is_file($installedLock)
        || $scanner->hasPendingPackages()
        || $session->getSession('dbmInstallerActive')
    ) {
        $bootstrapper->bootInstaller();
    }
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
