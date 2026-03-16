<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 *
 * INFO! Można uprościć:
 * strtolower(getenv('APP_ENV')) === 'development' na APP_DEBUG
 * $error = getenv('APP_DEBUG') ? $e->getMessage() : null;
 */

declare(strict_types=1);

namespace Mod\Installer\Steps\Helper;

use Dbm\Core\Module\Exception\InvalidModulePackageException;
use Dbm\Core\Module\Lifecycle\ModuleLifecycleManager;
use Mod\Installer\Constants\InstallerConstant;

final class AlertHelper
{
    public static function installOrFail(
        ModuleLifecycleManager $installer,
        object $step
    ): bool {
        try {
            $installer->installFromZip($step->getZipPath());
            return true;
        } catch (InvalidModulePackageException $e) {
            $step->setPayload([
                'type' => InstallerConstant::ALERT,
                'class' => 'danger',
                'text' => 'installer.alert.invalid_package_structure',
                'placeholder' => [
                    'file' => $step->getZipFile(),
                    'error' => strtolower(getenv('APP_ENV')) === 'development'
                        ? $e->getMessage()
                        : null,
                ],
            ]);

            return false;
        }
    }
}
