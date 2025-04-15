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

use Dbm\Classes\ExceptionHandler;
use Dbm\Classes\Helpers\DebugHelper;

function setupErrorHandling(): void
{
    error_reporting(E_ALL);

    ini_set('display_errors', getenv('APP_ENV') === 'production' ? '0' : '1');

    set_error_handler('reportingErrorHandler');

    set_exception_handler(function (Throwable $exception) {
        (new ExceptionHandler())->handle($exception, getenv('APP_ENV') ?: 'production');
    });
}

function reportingErrorHandler(int $errLevel, string $errMessage, string $errFile, int $errLine): void
{
    logErrorToFile($errLevel, $errMessage, $errFile, $errLine);

    $exceptionHandler = new ExceptionHandler();
    $exception = new ErrorException($errMessage, $errLevel, 0, $errFile, $errLine);
    $exceptionHandler->handle($exception, getenv('APP_ENV') ?: 'production');
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
    $dir = BASE_DIRECTORY . 'var' . DS . 'log' . DS;
    $path = $dir . $file;

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $errorHandler = "DATE: $date, level: $errLevel\n File: $errFile on line $errLine\n Message: $errMessage\n";

    file_put_contents($path, $errorHandler, FILE_APPEND);
}

function configurationSettings(string $pathConfig): void
{
    if (!file_exists($pathConfig)) {
        die('CONFIGURATION! Configure the application to run the program, then rename the .env.example file to .env.');
    }
}

function autoloadingWithWithoutComposer(string $pathAutoload): void
{
    if (file_exists($pathAutoload)) {
        require $pathAutoload;
    } else {
        spl_autoload_register(function ($className) {
            // Mapa przestrzeni nazw do katalogów
            $namespaceMap = [
                'App' => "src",
                'Dbm' => "application",
                'Psr' => "application/Psr",
                'Lib' => 'application/Libraries',
                'Mod' => "modules",
            ];

            // Wbudowane biblioteki i klasy (wyjątki)
            if (is_dir(BASE_DIRECTORY . 'libraries')) {
                static $loadedLibraries = []; // Flaga unikania wielokrotnego ładowania

                $namespaceLibraries = [
                    'PHPMailer\\PHPMailer\\PHPMailer' => "libraries/phpmailer/src",
                ];

                // Obsługa wyjątków (biblioteki zdefiniowane w $namespaceLibraries)
                foreach ($namespaceLibraries as $keySpace => $libraryPath) {
                    if (!isset($loadedLibraries[$keySpace]) && !class_exists($keySpace)) {
                        $librarySegments = explode("\\", $keySpace);
                        $fileName = end($librarySegments) . '.php';
                        $libraryPath = str_replace('/', DS, $libraryPath);
                        $filePathLib = BASE_DIRECTORY . $libraryPath . DS . $fileName;

                        if (file_exists($filePathLib)) {
                            require $filePathLib;
                            $loadedLibraries[$keySpace] = true;
                            return;
                        }

                        error_log("Autoloader: Nie znaleziono pliku dla {$keySpace} w ścieżce {$filePathLib}");
                    }
                }
            }

            // Obsługa mapowanych przestrzeni nazw
            $arrayClassName = explode("\\", $className);
            $namespaceRoot = $arrayClassName[0] ?? '';

            if (!isset($namespaceMap[$namespaceRoot])) {
                error_log("Autoloader: Nieobsługiwany namespace {$namespaceRoot}");
                return;
            }

            // Budowanie ścieżki pliku dla mapowanej przestrzeni
            $mappedPath = str_replace('/', DS, $namespaceMap[$namespaceRoot]);
            unset($arrayClassName[0]);

            $relativePath = implode(DS, $arrayClassName);
            $filePath = BASE_DIRECTORY . $mappedPath . DS . $relativePath . '.php';

            // Załaduj plik
            if (file_exists($filePath)) {
                require $filePath;
            } else {
                error_log("Autoloader: Nie znaleziono pliku dla klasy {$className} w ścieżce {$filePath}");
            }
        });
    }
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

// ### Rejestrowanie funkcji pomocniczych
if (!function_exists('dump')) {
    function dump(mixed ...$vars): void
    {
        foreach ($vars as $var) {
            DebugHelper::dump($var);
        }
    }
}
