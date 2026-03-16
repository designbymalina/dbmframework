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

namespace App\Console\Command;

use Dbm\Console\AbstractCommand;

final class SyncBundlesToComposerCommand extends AbstractCommand
{
    public function execute(): void
    {
        $base = realpath(BASE_DIRECTORY);

        if ($base === false) {
            $this->error('Invalid BASE_DIRECTORY.');
            return;
        }

        $base = rtrim($base, DIRECTORY_SEPARATOR);

        $bundlesFile = $base . '/storage/framework/bundles.php';
        $bundles = [];

        // ===== Load runtime bundles =====
        if (is_file($bundlesFile)) {
            $data = require $bundlesFile;

            if (is_array($data)) {
                $bundles = $data;
            }
        }

        if (empty($bundles)) {
            $this->warning('No runtime bundles found. Cleaning runtime namespaces.', true);
        }

        $composerFile = $base . '/composer.json';

        if (!is_file($composerFile)) {
            $this->error('composer.json not found.');
            return;
        }

        $composer = json_decode(file_get_contents($composerFile), true);

        if (!is_array($composer)) {
            $this->error('Invalid composer.json.');
            return;
        }

        $composer['autoload']['psr-4'] ??= [];
        $existingPsr4 = $composer['autoload']['psr-4'];

        /*
        |--------------------------------------------------------------------------
        | Remove ALL runtime bundle namespaces
        |--------------------------------------------------------------------------
        | Usuwamy wszystko poza core PSR-4: App\, Dbm\, Mod\
        |--------------------------------------------------------------------------
        */
        $coreNamespaces = ['App\\', 'Dbm\\', 'Mod\\'];

        foreach ($existingPsr4 as $namespace => $path) {
            if (in_array($namespace, $coreNamespaces, true)) {
                continue;
            }

            unset($existingPsr4[$namespace]);
        }

        /*
        |--------------------------------------------------------------------------
        | Add current runtime bundles
        |--------------------------------------------------------------------------
        */
        foreach ($bundles as $namespace => $absolutePath) {
            // Walidacja namespace
            if (!str_ends_with($namespace, '\\')) {
                $this->error("Invalid namespace (must end with \\): {$namespace}");
                continue;
            }

            $absolutePath = realpath($absolutePath);

            if ($absolutePath === false) {
                $this->error("Invalid path for bundle: {$namespace}");
                continue;
            }

            // Bezpieczeństwo – nie pozwalamy wychodzić poza projekt
            if (!str_starts_with($absolutePath, $base)) {
                $this->error("Bundle path outside project: {$namespace}");
                continue;
            }

            // Zamiana na ścieżkę względną
            $relativePath = ltrim(
                str_replace($base, '', $absolutePath),
                DIRECTORY_SEPARATOR
            ) . DIRECTORY_SEPARATOR;

            $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

            // Dopisujemy na koniec
            $existingPsr4[$namespace] = $relativePath;
        }

        $composer['autoload']['psr-4'] = $existingPsr4;

        file_put_contents(
            $composerFile,
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
        );

        $this->success('Composer synchronized successfully.', true);
        $this->info('Run: composer dump-autoload');
    }
}
