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

final class NullInstallerStep extends AbstractInstallerStep
{
    public function getName(): string
    {
        return 'null';
    }

    public function getTitle(): string
    {
        return 'installer.step.start.title';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function boot(): void {}

    /**
     * @param array<string, mixed> $input
     */
    public function handle(array $input): void {}

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return [];
    }

    public function hasPayload(): bool
    {
        return false;
    }

    public function isCompleted(): bool
    {
        return false;
    }

    public function isInstallStep(): bool
    {
        return false;
    }
}
