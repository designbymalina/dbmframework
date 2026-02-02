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

namespace Mod\Installer\Steps\Helper;

use Dbm\Core\Module\PackageInstaller;
use Dbm\Core\Module\Exception\InvalidModulePackageException;
use Mod\Installer\Constants\InstallerConstant;

final class AlertHelper
{
    public static function installOrFail(
        PackageInstaller $installer,
        object $step
    ): bool {
        try {
            $installer->installPackage($step->getZipPath());
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
