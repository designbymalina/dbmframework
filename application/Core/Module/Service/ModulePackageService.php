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

namespace Dbm\Core\Module\Service;

use Dbm\Core\Module\Exception\InvalidModulePackageException;
use Dbm\Core\Module\Package\PackageDescriptor;
use Dbm\Support\Config\ModuleConfigWriter;
use Lib\Files\FileSystem;

final class ModulePackageService
{
    public function __construct(
        private FileSystem $filesystem,
        private FileMigrationService $fileMigration,
        private DatabaseMigrationService $dbMigration,
        private ModuleConfigWriter $configWriter
    ) {}

    public function loadPackageDescriptor(
        string $moduleDir,
        string $zipPath
    ): PackageDescriptor {
        $manifest = $this->readManifest($moduleDir);
        $this->validateManifest($manifest);

        return new PackageDescriptor(
            $manifest['key'],
            $zipPath,
            $manifest
        );
    }

    public function extractIfNeeded(string $zipPath): string
    {
        $zipPath = $this->filesystem->normalizePath($zipPath);

        $hash = md5($zipPath);
        $target = BASE_DIRECTORY . '/_Documents/extracted/' . $hash;

        if ($this->filesystem->isDir($target)) {
            return $target;
        }

        $this->filesystem->ensureDir($target);

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Cannot open ZIP: ' . $zipPath);
        }

        $zip->extractTo($target);
        $zip->close();

        return $target;
    }

    /**
     * Kopiuje pliki z katalogu źródłowego do docelowego, tworząc kopię zapasową tylko plików nadpisywanych w katalogu docelowym.
     * Wykonywana jest tylko kopia oryginału do rollbacku, jeśli plik istnieje już w kopii nie zmieniamy go (zostaje pierwszy oryginał).
     */
    public function copyWithBackup(string $from, string $to, string $moduleKey): void
    {
        if (!$this->filesystem->isDir($from)) {
            return;
        }

        foreach ($this->filesystem->listFilesRecursively($from) as $file) {

            // ścieżka względna względem źródła modułu
            $relative = substr($file, strlen($from));

            // docelowa ścieżka w aplikacji
            $target = rtrim($to, '/') . $relative;

            if ($this->filesystem->fileExists($target)) {

                // backup zachowuje katalog docelowy
                $backup = BASE_DIRECTORY . '/_Documents/backup/' . $moduleKey
                    . str_replace(BASE_DIRECTORY, '', $target);

                if (!$this->filesystem->fileExists($backup)) {
                    $this->filesystem->ensureDir(dirname($backup));
                    $this->filesystem->copyFile($target, $backup);
                }
            }

            $this->filesystem->ensureDir(dirname($target));
            $this->filesystem->copyFile($file, $target);
        }
    }

    /**
     * Rozpakowuje pakiet modułu.
     */
    public function resolvePackageRoot(string $path): ?string
    {
        $path = rtrim(str_replace('\\', '/', $path), '/');

        if (!$this->filesystem->isDir($path)) {
            return null;
        }

        if ($this->filesystem->isDir($path . '/modules')) {
            return $path;
        }

        foreach ($this->filesystem->listDirs($path) as $dir) {
            $found = $this->resolvePackageRoot($dir);
            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    /**
     * Zwraca katalog modułu w pakiecie.
     */
    public function resolveModuleDir(string $root): string
    {
        $dirs = glob($root . '/modules/*', GLOB_ONLYDIR);

        if (!$dirs) {
            throw new InvalidModulePackageException('No module directories found.');
        }

        // Assumes one module per package - INFO! Można dopisać -> count($dirs) === 1
        return $dirs[0];
    }

    /**
     * Odczytuje manifest modułu (module.json).
     */
    public function readManifest(string $moduleDir): array
    {
        $file = $moduleDir . '/module.json';

        if (!$this->filesystem->fileExists($file)) {
            throw new InvalidModulePackageException('Manifest file module.json not found');
        }

        $content = $this->filesystem->readFile($file);

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidModulePackageException('Invalid module.json: ' . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Kopiowanie plików (flatfile) modułu do katalogów aplikacji.
     */
    public function fileMigrations(array $migrations, string $packageRoot): void
    {
        if (empty($migrations)) {
            return;
        }

        foreach ($migrations as $target => $relativePath) {
            $source = $packageRoot . '/' . ltrim($relativePath, '/');
            $destination = BASE_DIRECTORY . '/data/' . $target;

            if (!$this->filesystem->isDir($source)) {
                continue;
            }

            $this->filesystem->ensureDir($destination);

            $this->fileMigration->migrate($source, $destination);
        }
    }

    /**
     * Migracja plików bazy danych.
     */
    public function databaseMigrations(array $files, string $packageRoot): void
    {
        if (empty($files)) {
            return;
        }

        $this->dbMigration->migrate($files, $packageRoot);
    }

    /**
     * Zapisuje konfiguracje modułu.
     */
    public function writeConfig(array $manifest): void
    {
        $key = $manifest['key'];
        $class = $manifest['class'];
        $type = $manifest['type'] ?? 'plugin';

        if ($type === 'core') {
            $this->configWriter->addCore($key, $class);
        } else {
            $this->configWriter->addPlugin($key, $class);
        }
    }

    /**
     * Zapisuje zmienne do pliku .env.
     */
    public function writeEnv(array $manifest): void
    {
        if (empty($manifest['env'])) {
            return;
        }

        $envFile = BASE_DIRECTORY . '/.env';

        $env = $this->filesystem->fileExists($envFile)
            ? $this->filesystem->readFile($envFile)
            : '';

        $moduleName = $manifest['name'] ?? $manifest['key'];
        $header = "### {$moduleName}";

        if (!str_contains($env, $header)) {
            $env .= PHP_EOL . $header . PHP_EOL;
        }

        foreach ($manifest['env'] as $key => $value) {
            $pattern = "/^{$key}=.*$/m";

            if (preg_match($pattern, $env)) {
                // update istniejącej wartości
                $env = preg_replace($pattern, "{$key}={$value}", $env);
            } else {
                // dopisanie nowej
                $env .= "{$key}={$value}" . PHP_EOL;
            }
        }

        $env = rtrim($env) . PHP_EOL;

        if ($this->filesystem->fileExists($envFile)) {
            $this->filesystem->editFile($envFile, $env);
        } else {
            $this->filesystem->saveFile($envFile, $env, 0o644);
        }
    }

    /**
     * Usuwa rozpakowany katalog pakietu modułu.
     */
    public function cleanupExtracted(string $packageRoot): void
    {
        $hashDir = dirname($packageRoot);

        if (!str_contains($hashDir, '/_Documents/extracted/')) {
            throw new \RuntimeException(
                'Refusing to cleanup non-extracted directory: ' . $hashDir
            );
        }

        if (is_dir($hashDir)) {
            $this->filesystem->deleteDir($hashDir);
        }
    }

    // --- Helpers ---

    /**
     * Waliduje manifest modułu.
     */
    private function validateManifest(array $manifest): void
    {
        if (empty($manifest['key'])) {
            throw new InvalidModulePackageException('Missing module key.');
        }

        if (!preg_match('/^[a-z0-9_-]+$/', $manifest['key'])) {
            throw new InvalidModulePackageException('Invalid module key format.');
        }

        if (empty($manifest['class']) || !class_exists($manifest['class'])) {
            throw new InvalidModulePackageException('Invalid or missing module class.');
        }

        if (!empty($manifest['type']) && !in_array($manifest['type'], ['core', 'plugin'], true)) {
            throw new InvalidModulePackageException('Invalid module type.');
        }

        if (!empty($manifest['stage']) && !in_array(
            $manifest['stage'],
            ['pre-install', 'install', 'post-install'],
            true
        )) {
            throw new InvalidModulePackageException('Invalid module stage.');
        }
    }
}
