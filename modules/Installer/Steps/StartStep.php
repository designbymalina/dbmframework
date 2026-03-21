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

use Dbm\Exceptions\ExceptionHandler;
use Mod\Installer\Constants\InstallerConstant;

final class StartStep extends AbstractInstallerStep
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
     * Wyświetlenie, pokazuje stan + przycisk
     */
    public function boot(): void
    {
        if ($this->isCompleted()) {
            $this->setPayload([
                'type' => InstallerConstant::ALERT,
                'class' => 'info',
                'text' => 'installer.alert.installation_ready',
            ]);
            return;
        }

        $this->setPayload([
            'type' => InstallerConstant::TEXT,
            'text' => 'installer.step.start.content',
        ]);
    }

    /**
     * Obsługa danych wejściowych (instaluje)
     *
     * @param array<string, mixed> $input
     */
    public function handle(array $input): void
    {
        if ($this->isCompleted()) {
            return;
        }

        $this->isConfigLanguage();
        $this->markCompleted();
    }

    /**
     * Checks if APP_LANGUAGES config is valid.
     */
    private function isConfigLanguage(): bool
    {
        $appLanguages = getenv('APP_LANGUAGES');

        if (trim($appLanguages) === '') {
            if (!preg_match('/^([A-Z]{2})(\\|[A-Z]{2})*$/', $appLanguages)) {
                $message = "Configuration APP_LANGUAGES is required in the .env file. Expected value is `EN` or `PL|EN|DE` etc.";
                $this->logger->error($message);
                throw new ExceptionHandler($message);
            }
        }

        return true;
    }
}
