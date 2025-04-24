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

namespace App\Utility;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class InstallerUtility
{
    private const DIR_INSTALL = '_Documents';
    private const PATH_ROUTES = 'application' . DS . 'routes.php';
    private const PATH_CONSTANT = 'src' . DS . 'Config' . DS . 'ConstantConfig.php';
    private const PATH_MODULE_ROUTES = 'files' . DS . 'routes.php';
    private const PATH_MODULE_CONSTANT = 'files' . DS . 'constants.php';
    private const PATH_BACKUP_BASE = self::DIR_INSTALL . DS . 'BackupBaseFiles';

    public function installModule(string $dirModule, string $pathManifest, string $pathZip): array
    {
        $result = $this->unpackModule($dirModule, $pathZip);
        return $result['type'] === 'success' ? $this->prepareModuleInstallation($dirModule, $pathManifest) : $result;
    }

    public function uninstallModule(string $dirModule, string $pathManifest): array
    {
        if (!file_exists($pathManifest)) {
            return [
                'type' => 'error',
                'message' => 'Install manifest not found.',
            ];
        }

        $manifest = json_decode(file_get_contents($pathManifest), true);

        if (!isset($manifest['target'], $manifest['files'])) {
            return [
                'type' => 'error',
                'message' => 'Invalid manifest structure.',
            ];
        }

        $this->removeRoutes($dirModule);
        $this->removeConstants($dirModule);
        $this->removeTranslations();
        $this->deleteModuleFiiles($manifest['target'], $manifest['files']);
        $this->restoreBackupBaseFiles($manifest['files'], $manifest['target']);

        $this->waitForModuleState($pathManifest, false);

        return [
            'type' => 'success',
            'message' => 'The installer and configuration files have been completely removed.',
        ];
    }

    private function prepareModuleInstallation(string $dirModule, string $pathManifest): array
    {
        if (!file_exists($pathManifest)) {
            return [
                'type' => "error",
                'message' => "Install manifest not found: <code>$pathManifest</code>",
            ];
        }

        $manifest = json_decode(file_get_contents($pathManifest), true);
        if (!$manifest || !isset($manifest['files'], $manifest['target'])) {
            return [
                'type' => "error",
                'message' => "Invalid install manifest structure.",
            ];
        }

        $this->backupBaseFiles($manifest['files'], $manifest['target']);
        $this->copyModuleFiles($manifest['files'], $manifest['target']);
        $this->modifyTranslations();
        $this->modifyConstants($dirModule);
        $this->modifyRoutes(BASE_DIRECTORY . self::PATH_ROUTES, $dirModule . DS . self::PATH_MODULE_ROUTES);

        $this->waitForModuleState($pathManifest, true, 5.0);

        return [
            'type' => "success",
            'message' => 'The installer has been successfully loaded! <a href="./install">Click here to continue &rsaquo;&rsaquo;</a>',
        ];
    }

    /**
     * INFO! Metoda nie działa poprawnie na 100%. Problem z czasem sprawdzania kopiowania/usuwania plików na różnych dyskach i systemach.
     */
    private function waitForModuleState(string $manifestPath, bool $shouldExist, float $timeout = 3.0, float $interval = 0.1, array $important = []): void
    {
        if (!file_exists($manifestPath)) {
            return;
        }

        $json = file_get_contents($manifestPath);
        $data = json_decode($json, true);

        if (!is_array($data) || !isset($data['target']) || !isset($data['register'])) {
            return;
        }

        $targets = array_merge(
            array_values($data['target']),
            array_values($data['register'])
        );

        if ($shouldExist) {
            usleep(3_000_000); // 3.0s

            if (!empty($important)) {
                foreach ($important as $key) {
                    if (isset($data['target'][$key])) {
                        $checkPath = BASE_DIRECTORY . $data['target'][$key];
                        $maxTries = 30;
                        while (!file_exists($checkPath) && $maxTries-- > 0) {
                            usleep(100_000);
                        }
                    }
                }
            }
        }

        $start = microtime(true);

        while ((microtime(true) - $start) < $timeout) {
            $allMatched = true;

            foreach ($targets as $relativePath) {
                $fullPath = BASE_DIRECTORY . $relativePath;
                $exists = file_exists($fullPath);

                if ($exists !== $shouldExist) {
                    $allMatched = false;
                    break;
                }
            }

            if ($allMatched) {
                return;
            }

            usleep((int) ($interval * 1_000_000));
        }
    }

    private function unpackModule(string $dirModule, string $pathZip): array
    {
        if (file_exists($dirModule)) {
            return [
                'type' => 'success',
                'message' => 'Install module already extracted.'
            ];
        }

        if (!file_exists($pathZip)) {
            return [
                'type' => 'info',
                'message' => 'Installation archive not found in ' . self::DIR_INSTALL . ' directory. Download installer from project home page.'
            ];
        }

        $zip = new ZipArchive();
        if ($zip->open($pathZip) === true) {
            $zip->extractTo(BASE_DIRECTORY . self::DIR_INSTALL);
            $zip->close();

            return [
                'type' => 'success',
                'message' => 'Install module extracted successfully.'
            ];
        }

        return [
            'type' => 'error',
            'message' => 'Unable to open install.zip archive.'
        ];
    }

    private function copyModuleFiles(array $arrayFiles, array $arrayTargets): void
    {
        foreach ($arrayFiles as $key => $src) {
            if (!isset($arrayTargets[$key])) {
                continue;
            }

            $source = BASE_DIRECTORY . $src;
            $target = BASE_DIRECTORY . $arrayTargets[$key];

            if (is_dir($source)) {
                $this->copyRecursive($source, $target);
            } elseif (is_file($source)) {
                if (!is_dir(dirname($target))) {
                    mkdir(dirname($target), 0777, true);
                }
                copy($source, $target);
            }
        }
    }

    private function modifyConstants(string $dirModule): void
    {
        $constantPath = BASE_DIRECTORY . self::PATH_CONSTANT;
        $constantsFile = $dirModule . DS . self::PATH_MODULE_CONSTANT;

        if (file_exists($constantPath) && file_exists($constantsFile)) {
            $content = file_get_contents($constantPath);
            include $constantsFile;

            if (isset($installSteps) && !str_contains($content, 'ARRAY_INSTALL_STEPS')) {
                $content = str_replace("//-INSTALL_POINT_ADD_CONSTANT", trim($installSteps) . "\n\n    //-INSTALL_POINT_ADD_CONSTANT", $content);
                file_put_contents($constantPath, $content);
            }
        }
    }

    private function modifyRoutes(string $routesPath, string $routesFile): void
    {
        if (!file_exists($routesPath) || !file_exists($routesFile)) {
            return;
        }

        $content = file_get_contents($routesPath);
        include $routesFile;

        if (!isset($installUses, $installRoutes, $installClasses)) {
            return;
        }

        $alreadyExists = array_reduce(
            $installClasses,
            fn ($carry, $class) => $carry || str_contains($content, trim($class)),
            false
        );

        if (!$alreadyExists) {
            $content = preg_replace(
                [
                    '/(\/\/\-INSTALL_POINT_ADD_USE)/',
                    '/(\/\/\-INSTALL_POINT_ADD_ROUTES)/'
                ],
                [
                    trim($installUses) . "\n\$1",
                    trim($installRoutes) . "\n    \$1"
                ],
                $content
            );

            file_put_contents($routesPath, $content);
        }
    }

    private function modifyTranslations(): void
    {
        $envPath = BASE_DIRECTORY . '.env';

        if (!file_exists($envPath)) {
            return;
        }

        $lines = file($envPath);
        $updated = false;

        foreach ($lines as &$line) {
            if (str_starts_with(trim($line), 'APP_LANGUAGES=')) {
                $line = "APP_LANGUAGES=EN|PL\n";
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            $lines[] = "APP_LANGUAGES=EN|PL\n";
        }

        file_put_contents($envPath, implode('', $lines));
    }

    private function copyRecursive(string $source, string $destination): void
    {
        $source = rtrim($source, DS);
        $destination = rtrim($destination, DS);

        if (!is_dir($source)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $targetPath = $destination . DS . substr($item->getPathname(), strlen($source) + 1);

            if ($item->isDir()) {
                mkdir($targetPath, 0777, true);
            } else {
                if (!is_dir(dirname($targetPath))) {
                    mkdir(dirname($targetPath), 0777, true);
                }
                copy($item->getPathname(), $targetPath);
            }
        }
    }

    private function deleteModuleFiiles(array $arrayTargets, array $arrayFiles): void
    {
        foreach ($arrayTargets as $key => $targetRel) {
            $sourceRel = $arrayFiles[$key] ?? null;

            $target = BASE_DIRECTORY . $targetRel;
            $source = $sourceRel ? BASE_DIRECTORY . $sourceRel : null;

            if (is_file($target)) {
                unlink($target);
                $parentDir = dirname($target);
                if (is_dir($parentDir) && count(glob($parentDir . '/*')) === 0) {
                    rmdir($parentDir);
                }
            } elseif (is_dir($target) && $source && is_dir($source)) {
                $this->removeMatchingFiles($source, $target);
                if (is_dir($target) && count(glob($target . '/*')) === 0) {
                    rmdir($target);
                }
            }
        }
    }

    private function removeRoutes(string $dirModule): void
    {
        $routesPath = BASE_DIRECTORY . self::PATH_ROUTES;
        $routesFile = $dirModule . DS . self::PATH_MODULE_ROUTES;

        if (!file_exists($routesPath) || !file_exists($routesFile)) {
            return;
        }

        include $routesFile;
        $content = file_get_contents($routesPath);

        if (isset($installUses)) {
            $content = str_replace(trim($installUses) . "\n", '', $content);
        }

        if (isset($installRoutes)) {
            $routesLines = explode("\n", trim($installRoutes));
            foreach ($routesLines as $line) {
                $content = str_replace(trim($line) . "\n", '', $content);
            }
        }

        $content = preg_replace('/^[ \\t]+(\\/\\/\\-INSTALL_POINT_ADD_ROUTES)/m', '$1', $content);
        $content = preg_replace('/^[ \\t]+(\\/\\/\\-INSTALL_POINT_ADD_USE)/m', '$1', $content);

        file_put_contents($routesPath, $content);
    }

    private function removeConstants(string $dirModule): void
    {
        $constantsPath = BASE_DIRECTORY . self::PATH_CONSTANT;
        $constantsFile = $dirModule . DS . self::PATH_MODULE_CONSTANT;

        if (!file_exists($constantsPath) || !file_exists($constantsFile)) {
            return;
        }

        include $constantsFile;
        $content = file_get_contents($constantsPath);

        if (isset($installKey) && isset($installKey['steps'])) {
            $pattern = "/\n{2,}?[ \t]*" . preg_quote($installKey['steps'], '/') . ".*?\];/s";
            $content = preg_replace($pattern, '', $content);
        }

        file_put_contents($constantsPath, $content);
    }

    private function removeTranslations(): void
    {
        $envPath = BASE_DIRECTORY . '.env';

        if (!file_exists($envPath)) {
            return;
        }

        $lines = file($envPath);

        foreach ($lines as &$line) {
            if (str_starts_with(trim($line), 'APP_LANGUAGES=')) {
                $line = "APP_LANGUAGES=\n";
                break;
            }
        }

        file_put_contents($envPath, implode('', $lines));
    }

    private function removeMatchingFiles(string $sourceDir, string $targetDir): void
    {
        $items = scandir($sourceDir);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $source = $sourceDir . DS . $item;
            $target = $targetDir . DS . $item;

            if (is_dir($source)) {
                if (is_dir($target)) {
                    $this->removeMatchingFiles($source, $target);
                    if (count(glob($target . '/*')) === 0) {
                        rmdir($target);
                    }
                }
            } elseif (is_file($source) && file_exists($target)) {
                unlink($target);
            }
        }
    }

    private function backupBaseFiles(array $arrayFiles, array $arrayTargets): void
    {
        foreach ($arrayFiles as $key => $srcRelPath) {
            if (!isset($arrayTargets[$key])) {
                continue;
            }

            $source = BASE_DIRECTORY . $srcRelPath;
            $targetBase = BASE_DIRECTORY . $arrayTargets[$key];
            $backupBase = BASE_DIRECTORY . self::PATH_BACKUP_BASE . DS . $arrayTargets[$key];

            if (is_file($source)) {
                if (file_exists($targetBase)) {
                    if (!is_dir(dirname($backupBase))) {
                        mkdir(dirname($backupBase), 0777, true);
                    }
                    copy($targetBase, $backupBase);
                }

            } elseif (is_dir($source)) {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($iterator as $file) {
                    $relative = substr($file->getPathname(), strlen($source) + 1);
                    $targetPath = $targetBase . DS . $relative;
                    $backupPath = $backupBase . DS . $relative;

                    if (file_exists($targetPath) && !file_exists($backupPath)) {
                        if (!is_dir(dirname($backupPath))) {
                            mkdir(dirname($backupPath), 0777, true);
                        }
                        copy($targetPath, $backupPath);
                    }
                }
            }
        }
    }

    private function restoreBackupBaseFiles(array $arrayFiles, array $arrayTargets): void
    {
        foreach ($arrayFiles as $key => $_) {
            if (!isset($arrayTargets[$key])) {
                continue;
            }

            $target = BASE_DIRECTORY . $arrayTargets[$key];
            $backup = BASE_DIRECTORY . self::PATH_BACKUP_BASE . DS . $arrayTargets[$key];

            if (is_file($backup)) {
                if (!is_dir(dirname($target))) {
                    mkdir(dirname($target), 0777, true);
                }
                copy($backup, $target);
            } elseif (is_dir($backup)) {
                $this->copyRecursive($backup, $target);
            }
        }
    }
}
