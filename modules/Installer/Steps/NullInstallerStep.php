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

namespace Mod\Installer\Steps;

use Mod\Installer\Contracts\InstallerStepInterface;

final class NullInstallerStep implements InstallerStepInterface
{
    public function getName(): string
    {
        return '';
    }

    public function getTitle(): string
    {
        return '';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function boot(): void {}

    public function handle(array $input): void {}

    public function isCompleted(): bool
    {
        return false;
    }

    public function getPayload(): array
    {
        return [];
    }

    public function hasPayload(): bool
    {
        return false;
    }
}
