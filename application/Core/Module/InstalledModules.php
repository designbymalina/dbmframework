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

namespace Dbm\Core\Module;

final class InstalledModules
{
    private array $installed = [];

    public function __construct()
    {
        $configPath = BASE_DIRECTORY . '/config/modules.php';

        if (!is_file($configPath)) {
            return;
        }

        $config = require $configPath;

        foreach ($config as $section => $modules) {
            if (!is_array($modules)) {
                continue;
            }

            foreach ($modules as $moduleKey => $definition) {
                // tylko poprawne klucze modułów
                if (is_string($moduleKey)) {
                    $this->installed[$moduleKey] = true;
                }
            }
        }
    }

    public function isInstalled(string $key): bool
    {
        return isset($this->installed[$key]);
    }

    public function all(): array
    {
        return array_keys($this->installed);
    }
}
