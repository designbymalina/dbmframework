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

namespace App\Service;

class IndexService
{
    public function getMetaIndex(): array
    {
        return [
            'meta.title' => "Your Web Application Name",
            'meta.description' => "Web application description...",
            'meta.keywords' => "application keywords",
        ];
    }

    public function getMetaStart(): array
    {
        return [
            'meta.title' => "Welcome to DbM Framework!",
            'meta.description' => "Your lightweight and flexible framework for building powerful web applications.",
            'meta.keywords' => "high performance, easy configuration, comprehensive documentation",
            'meta.robots' => "noindex,nofollow",
        ];
    }

    public function getMetaInstaller(): array
    {
        return [
            'meta.title' => "DbM Framework Installer",
            'meta.description' => "A tool for installing modules.",
            'meta.keywords' => "installer, modules, tool",
            'meta.robots' => "noindex,nofollow",
        ];
    }

    public function waitForFileState(string $path, bool $shouldExist, float $timeout = 3.0, float $interval = 0.1): void
    {
        $start = microtime(true);
        while ((file_exists($path) !== $shouldExist) && (microtime(true) - $start) < $timeout) {
            usleep((int) ($interval * 1_000_000));
        }
    }
}
