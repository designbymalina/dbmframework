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

    public function installModule(string $dirModule, string $pathZip, string $pathManifest): array
    {
        $result = $this->unpackModule($dirModule, $pathZip);
        return $result['status'] === 'success' ? $this->prepareModuleInstallation($dirModule, $pathManifest) : $result;
    }

    public function uninstallModule(string $pathManifest): array
    {
        if (!file_exists($pathManifest)) {
            return [
                'status' => 'error',
                'message' => 'Install manifest not found.',
            ];
        }

        $manifest = json_decode(file_get_contents($pathManifest), true);

        if (!isset($manifest['target'], $manifest['files'])) {
            return [
                'status' => 'error',
                'message' => 'Invalid manifest structure.',
            ];
        }

        $this->removeRoutes(BASE_DIRECTORY . $manifest['register']['routes']);
        $this->removeConstants(BASE_DIRECTORY . $manifest['register']['constant']);
        $this->removeTranslations();
        $this->deleteModuleFiiles($manifest['target'], $manifest['files']);

        return [
            'status' => 'success',
            'message' => 'The installer and configuration files have been completely removed.',
        ];
    }

    private function unpackModule(string $dirModule, string $pathZip): array
    {
        if (file_exists($dirModule)) {
            return [
                'status' => 'success',
                'message' => 'Install module already extracted.'
            ];
        }

        if (!file_exists($pathZip)) {
            return [
                'status' => 'error',
                'message' => 'Installation archive not found in ' . self::DIR_INSTALL . ' directory. Download installer from project home page.'
            ];
        }

        $zip = new ZipArchive();
        if ($zip->open($pathZip) === true) {
            $zip->extractTo(BASE_DIRECTORY . self::DIR_INSTALL);
            $zip->close();

            return [
                'status' => 'success',
                'message' => 'Install module extracted successfully.'
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Unable to open install.zip archive.'
        ];
    }

    private function prepareModuleInstallation(string $dirModule, string $pathManifest): array
    {
        $routesPath = BASE_DIRECTORY . self::PATH_ROUTES;

        if (!file_exists($pathManifest)) {
            return [
                'status' => "error",
                'message' => "Install manifest not found: <code>$pathManifest</code>",
            ];
        }

        $manifest = json_decode(file_get_contents($pathManifest), true);
        if (!$manifest || !isset($manifest['files'], $manifest['target'])) {
            return [
                'status' => "error",
                'message' => "Invalid install manifest structure.",
            ];
        }

        $this->copyModuleFiles($manifest['files'], $manifest['target']);
        $this->modifyConstants($dirModule);
        $this->modifyTranslations();
        $this->modifyRoutes($routesPath);

        return [
            'status' => "success",
            'message' => 'The installer has been successfully loaded! <a href="./install">Click here to continue &rsaquo;&rsaquo;</a>',
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
        $constantsFile = $dirModule . DS . 'files/constants.php';

        if (file_exists($constantPath) && file_exists($constantsFile)) {
            $content = file_get_contents($constantPath);
            include $constantsFile;

            if (isset($installSteps) && !str_contains($content, 'ARRAY_INSTALL_STEPS')) {
                $content = str_replace("//-INSTALL_POINT_ADD_CONSTANT", trim($installSteps) . "\n\n    //-INSTALL_POINT_ADD_CONSTANT", $content);
                file_put_contents($constantPath, $content);
            }
        }
    }

    private function modifyRoutes(string $routesPath): void
    {
        if (!file_exists($routesPath)) {
            return;
        }

        $content = file_get_contents($routesPath);
        $installUse = "use App\\Controller\\InstallController;";
        $installRoute = "\$router->addRoute('/install', [InstallController::class, 'install'], 'install');";

        if (!str_contains($content, 'InstallController::class')) {
            $content = preg_replace(
                [
                    '/(\/\/\-INSTALL_POINT_ADD_USE)/',
                    '/(\/\/\-INSTALL_POINT_ADD_ROUTES)/'
                ],
                [
                    "$installUse\n\$1",
                    "// Install routes\n    $installRoute\n    \$1"
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
        $source = rtrim($source, DIRECTORY_SEPARATOR);
        $destination = rtrim($destination, DIRECTORY_SEPARATOR);

        if (!is_dir($source)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $targetPath = $destination . DIRECTORY_SEPARATOR . substr($item->getPathname(), strlen($source) + 1);

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

    private function removeRoutes(string $file): void
    {
        if (!file_exists($file)) {
            return;
        }

        $content = file_get_contents($file);
        $content = preg_replace('/^.*Install routes.*$(\r?\n)?/m', '', $content);
        $content = preg_replace('/^.*InstallController.*$(\r?\n)?/m', '', $content);
        file_put_contents($file, $content);
    }

    private function removeConstants(string $file): void
    {
        if (!file_exists($file)) {
            return;
        }

        $content = file_get_contents($file);
        $content = preg_replace("/\n{2,}?[ \t]*\/\/\-INSTALL_STEPS.*?\];/s", "", $content);
        file_put_contents($file, $content);
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

            $source = $sourceDir . DIRECTORY_SEPARATOR . $item;
            $target = $targetDir . DIRECTORY_SEPARATOR . $item;

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
}
