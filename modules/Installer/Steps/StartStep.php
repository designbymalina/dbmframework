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

use Mod\Installer\Constants\InstallerConstant;
use Mod\Installer\Contracts\InstallerStepInterface;
use RuntimeException;

final class StartStep extends AbstractInstallerStep implements InstallerStepInterface
{
    public function getName(): string
    {
        return 'start';
    }

    public function getTitle(): string
    {
        return 'installer.step.start.title';
    }

    /**
     * ? Logika wyświetlenia "Content"
     */
    public function boot(): void
    {
        $this->checkStart();

        if (!empty($this->getPayload())) {
            return;
        }

        $this->setPayload([
            'type' => InstallerConstant::TEXT,
            'text' => 'installer.step.start.content',
        ]);
    }

    /**
     * ? Logika po kliknęciu "Next"
     */
    public function handle(array $input): void
    {
        $this->markCompleted();
    }

    private function checkStart(): void
    {
        $appLanguages = getenv('APP_LANGUAGES');

        if (trim($appLanguages) === '') {
            $message = "Configuration APP_LANGUAGES is required in the .env file. Expected configuration value is 'PL' or 'PL|EN|DE' etc.";
            $this->logger->error($message);
            throw new RuntimeException($message);
        }
    }
}
