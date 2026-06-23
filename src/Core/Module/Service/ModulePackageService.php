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
use Dbm\Core\Module\Filesystem\PathResolver;
use Dbm\Core\Module\Package\PackageDescriptor;
use Dbm\Infrastructure\Filesystem\Filesystem;
use Psr\Log\LoggerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ModulePackageService
{
    public const DIR_CONFLICTS = '_conflicts';

    public function __construct(
        private Filesystem $filesystem,
        private FileMigrationService $fileMigration,
        private PathResolver $paths,
        private DatabaseMigrationService $dbMigration,
        private LoggerInterface $logger
    ) {}

    public function loadPackageDescriptor(
        string $moduleDir,
        string $zipPath
    ): PackageDescriptor {
        $manifest = $this->readManifest($moduleDir);
        $manifest = $this->normalizeManifest($manifest);

        $this->validateManifest($manifest);

        return new PackageDescriptor(
            $manifest['key'],
            $manifest,
            $zipPath,
        );
    }

    public function extractIfNeeded(string $zipPath): string
    {
        $extractRoot = $this->paths->documents('extracted');

        foreach ($this->filesystem->listDirs($extractRoot) as $dir) {
            try {
                $this->filesystem->deleteDir($dir);
            } catch (\Throwable $e) {
                $this->logger->warning(
                    '[ModuleInstaller] Failed to remove old extracted directory.',
                    ['path' => $dir, 'message' => $e->getMessage()]
                );
            }
        }

        $zipPath = $this->filesystem->normalizePath($zipPath);

        $hash = md5($zipPath);

        $target = $this->paths->documents('extracted/' . $hash);

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
     * @param array<string, array<int, string>> $excluded
     * @return array<int, string>
     */
    public function copyDirectoryFiles(
        string $prefix,
        string $packageRoot,
        string $timestamp,
        array $excluded = []
    ): array {
        $from = rtrim($packageRoot, '/') . '/' . $prefix;
        $to = $this->paths->basePath($prefix);

        if (!$this->filesystem->isDir($from)) {
            return [];
        }

        $conflicts = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($from, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $relative = substr($file->getPathname(), strlen($from) + 1);
            $relative = str_replace('\\', '/', $relative);

            foreach ($excluded[$prefix] ?? [] as $excludedPath) {
                $excludedPath = trim(str_replace('\\', '/', $excludedPath), '/');

                if ($relative === $excludedPath || str_starts_with($relative, $excludedPath . '/')) {
                    continue 2;
                }
            }

            $target = $to . '/' . $relative;

            $this->filesystem->ensureDir(dirname($target));

            if ($this->filesystem->fileExists($target)) {

                $existingHash = md5_file($target);
                $newHash = md5_file($file->getPathname());

                if ($existingHash !== $newHash) {

                    $projectPath = $prefix . '/' . $relative;

                    $this->backupConflict(
                        $projectPath,
                        $target,
                        $timestamp
                    );

                    $conflicts[] = $projectPath;
                }
            }

            $this->filesystem->copyFile($file->getPathname(), $target);
        }

        return $conflicts;
    }

    private function backupConflict(
        string $projectPath,
        string $target,
        string $timestamp
    ): void {

        $backupDir = $this->paths->backups(self::DIR_CONFLICTS . '/' . $timestamp);

        $backupFile = $backupDir . '/' . $projectPath;

        $this->filesystem->ensureDir(dirname($backupFile));

        if (!$this->filesystem->fileExists($backupFile)) {
            $this->filesystem->copyFile($target, $backupFile);
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

    public function resolveModuleDir(string $root): string
    {
        $dirs = glob($root . '/modules/*', GLOB_ONLYDIR);

        if (!$dirs) {
            throw new InvalidModulePackageException(
                'No module directories found in /modules'
            );
        }

        foreach ($dirs as $dir) {
            if (is_file($dir . '/module.json')) {
                return $dir;
            }
        }

        throw new InvalidModulePackageException(
            'No module.json found in modules directory'
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function readManifest(string $moduleDir): array
    {
        $file = $moduleDir . '/module.json';

        if (!$this->filesystem->fileExists($file)) {
            throw new InvalidModulePackageException(
                "Manifest not found: {$file}"
            );
        }

        $content = $this->filesystem->readFile($file);

        if (!is_string($content) || $content === '') {
            throw new InvalidModulePackageException(
                "Cannot read manifest file: {$file}"
            );
        }

        $data = json_decode($content, true);

        if (!is_array($data)) {
            throw new InvalidModulePackageException(
                "Invalid module.json structure in {$file}"
            );
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidModulePackageException(
                'Invalid module.json: ' . json_last_error_msg()
            );
        }

        return $data;
    }

    /**
     * Kopiowanie plików (flatfile) modułu do katalogów aplikacji.
     *
     * @param array<string, string> $migrations
     */
    public function fileMigrations(array $migrations, string $packageRoot): void
    {
        if (empty($migrations)) {
            return;
        }

        foreach ($migrations as $target => $relativePath) {
            $source = $packageRoot . '/' . ltrim($relativePath, '/');
            $destination = $this->paths->basePath('data/' . $target);

            if (!$this->filesystem->isDir($source)) {
                continue;
            }

            $this->filesystem->ensureDir($destination);

            $this->fileMigration->migrate($source, $destination);
        }
    }

    /**
     * Migracja plików bazy danych.
     *
     * @param array<string, string> $files
     */
    public function databaseMigrations(array $files, string $packageRoot): void
    {
        if (empty($files)) {
            return;
        }

        $this->dbMigration->migrate($files, $packageRoot);
    }

    /**
     * Zapisuje zmienne do pliku .env.
     *
     * @param array<string, mixed> $manifest
     */
    public function writeEnv(array $manifest): void
    {
        if (empty($manifest['env'])) {
            return;
        }

        $envFile = $this->paths->env();

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

        if (!str_contains($hashDir, $this->paths->documents('extracted/'))) {
            throw new \RuntimeException(
                'Refusing to cleanup non-extracted directory: ' . $hashDir
            );
        }

        if (!is_dir($hashDir)) {
            return;
        }

        // @INFO Problem prawdopodobnie po stronie Windowsa, który przez moment trzyma uchwyt do pliku.
        // Dodano pętlę do powtórzenia usunęcia zawartości katalogu.
        for ($i = 0; $i < 2; $i++) {
            try {
                $this->filesystem->deleteDir($hashDir);
                return;
            } catch (\Throwable $e) {
                usleep(100000); // 100 ms

                $this->logger->warning(
                    '[ModuleInstaller] Temporary extracted directory could not be removed.',
                    ['attempt' => $i + 1, 'hashDir' => $hashDir, 'message' => $e->getMessage()]
                );
            }
        }
    }

    /**
     * Zapisuje manifest instalacji modułu.
     *
     * @param array<string, mixed> $manifest
     */
    public function writeInstallManifest(array $manifest, string $packageRoot): void
    {
        $files = $this->collectInstalledFiles($packageRoot);

        // INFO! Powinno być zgodne z ModuleBootstrapper -> normalizeManifest()
        $installMeta = [
            'key' => $manifest['key'],
            'installed' => true,
            'enabled' => true,
            'installed_at' => date('Y-m-d H:i:s'),
            'files' => $files,
        ];

        $manifestPath = $this->paths->manifest($manifest['key']);

        $content = json_encode(
            $installMeta,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        $this->filesystem->saveFile($manifestPath, $content);
    }

    // --- Helpers ---

    /**
     * Waliduje manifest modułu.
     *
     * @param array<string, mixed> $manifest
     */
    private function validateManifest(array $manifest): void
    {
        if (empty($manifest['key'])) {
            throw new InvalidModulePackageException('Missing module key.');
        }

        if (!preg_match('/^[a-z0-9_-]+$/', $manifest['key'])) {
            throw new InvalidModulePackageException('Invalid module key format.');
        }

        if (!empty($manifest['type']) && !in_array($manifest['type'], ['core', 'plugin'], true)) {
            throw new InvalidModulePackageException('Invalid module type.');
        }
    }

    /**
     * Zbiera informacje o plikach, które zostały zainstalowane w projekcie.
     *
     * @return array<array{path: string, hash: string}>
     */
    private function collectInstalledFiles(string $packageRoot): array
    {
        $files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($packageRoot, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {

            if (!$file->isFile()) {
                continue;
            }

            $relativeFromPackage = substr($file->getPathname(), strlen($packageRoot) + 1);

            // Docelowa ścieżka w projekcie
            $absoluteInstalledPath = $this->paths->basePath($relativeFromPackage);

            if (!is_file($absoluteInstalledPath)) {
                continue;
            }

            $files[] = [
                'path' => str_replace('\\', '/', $relativeFromPackage),
                'hash' => md5_file($absoluteInstalledPath),
            ];
        }

        return $files;
    }

    /**
     * @param array<string, mixed> $manifest
     * @return array<string, mixed>
     */
    private function normalizeManifest(array $manifest): array
    {
        $manifest['env'] ??= [];

        if (!is_array($manifest['env'])) {
            throw new InvalidModulePackageException('env must be object');
        }

        $manifest['file_migrations'] ??= [];

        if (!is_array($manifest['file_migrations'])) {
            throw new InvalidModulePackageException('file_migrations must be object');
        }

        $manifest['database']['migrations'] ??= [];

        if (!is_array($manifest['database']['migrations'])) {
            throw new InvalidModulePackageException('database.migrations must be array');
        }

        return $manifest;
    }
}
