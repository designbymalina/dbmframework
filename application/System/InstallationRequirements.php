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

namespace Dbm\System;

final class InstallationRequirements
{
    private const PHP_VERSION = '8.1';
    private const DIR_PERMISSIONS = [
        BASE_DIRECTORY . '/var',
        BASE_DIRECTORY . '/storage',
        BASE_DIRECTORY . '/storage/cache',
    ];

    /** @var array<int, string> */
    private array $issues = [];

    public function check(string $pathConfig): void
    {
        $this->checkPhpVersion(self::PHP_VERSION);

        $this->checkConfig($pathConfig);

        $this->checkDirectories(self::DIR_PERMISSIONS);

        if ($this->hasIssues()) {
            $this->renderAndExit();
        }
    }

    // ===== Private =====

    private function checkConfig(string $pathConfig): void
    {
        if (!is_file($pathConfig)) {
            $this->issues[] = '.env file not found (rename .env.example to .env)';
        }
    }

    /**
     * @param array<int, string> $paths
     */
    private function checkDirectories(array $paths): void
    {
        foreach ($paths as $path) {
            if (!is_dir($path)) {
                if (!@mkdir($path, 0o755, true) && !is_dir($path)) {
                    $this->issues[] = "Cannot create directory: {$path}";
                    continue;
                }
            }

            if (!$this->isReallyWritable($path)) {
                $this->issues[] = "Directory not writable: {$path} (chmod -R 775 storage)";
            }
        }
    }

    private function checkPhpVersion(string $required): void
    {
        if (version_compare(PHP_VERSION, $required, '<')) {
            $this->issues[] = "PHP {$required}+ required, current: " . PHP_VERSION;
        }
    }

    /*
     * Optional method.
     *
     * @param array<int, string> $extensions
     *
    private function checkExtensions(array $extensions): void
    {
        foreach ($extensions as $ext) {
            if (!extension_loaded($ext)) {
                $this->issues[] = "Missing PHP extension: {$ext}";
            }
        }
    } */

    private function isReallyWritable(string $path): bool
    {
        if (!is_writable($path)) {
            return false;
        }

        $testFile = $path . '/.__writable_test';

        if (@file_put_contents($testFile, 'test') === false) {
            return false;
        }

        @unlink($testFile);

        return true;
    }

    private function hasIssues(): bool
    {
        return $this->issues !== [];
    }

    private function renderAndExit(): void
    {
        http_response_code(503);

        echo $this->render();

        exit;
    }

    private function render(): string
    {
        $items = '';

        foreach ($this->issues as $issue) {
            $items .= '<li>' . htmlspecialchars($issue) . '</li>';
        }

        return <<<HTML
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <title>DbM Framework - Installation Requirements</title>
                <style>
                    body { margin: 2rem; font-family: Arial, sans-serif; font-size: 16px; background: #f4f4f4; color: #333; }
                    p { margin: 0; padding: 0; }
                    .container { max-width: 992px; margin: auto; background: #fff; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                    .header { display: flex; justify-content: space-between; padding: 1rem; background-color: #e11d48; color: #fff; }
                    .main { padding: 2rem; }
                    .navigation { font-size: 1.5rem; }
                    .logo { font-size: 0.9rem; color: #cbd5e1; }
                    .title { text-align: right; }
                    .title a { font-size: 0.7rem; color: #cbd5e1; text-decoration: none; text-transform: uppercase; }
                    .info { word-break: break-word; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <div class="navigation">Installation Requirements</div>
                        <div>
                            <div class="logo">DbM Framework</div>
                            <div class="title"><a href="https://dbm.org.pl/">Go To Project</a></div>
                        </div>
                    </div>
                    <div class="main">
                        <div class="info">
                            <h1>System requirements not met</h1>
                            <ul>{$items}</ul>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            HTML;
    }
}
