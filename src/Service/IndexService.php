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

    public function waitForModuleState(string $manifestPath, bool $shouldExist, float $timeout = 10.0, float $interval = 0.1): void
    {
        if (!file_exists($manifestPath)) {
            return;
        }

        $json = file_get_contents($manifestPath);
        $data = json_decode($json, true);

        if (!isset($data['target']) || !is_array($data['target'])) {
            return;
        }

        $targets = array_values($data['target']);
        $start = microtime(true);

        while ((microtime(true) - $start) < $timeout) {
            $allMatched = true;

            foreach ($targets as $relativePath) {
                $path = BASE_DIRECTORY . $relativePath;
                $exists = file_exists($path);

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
}
