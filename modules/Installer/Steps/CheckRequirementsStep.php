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

final class CheckRequirementsStep extends AbstractInstallerStep
{
    public function getName(): string
    {
        return 'requirements';
    }

    public function getTitle(): string
    {
        return 'installer.step.requirements.title';
    }

    public function boot(): void
    {
        if (!empty($this->getPayload())) {
            return;
        }

        $this->setPayload([
            'type' => InstallerConstant::LIST,
            'items' => $this->checkRequirements(),
        ]);
    }

    public function handle(array $input): void
    {
        $this->markCompleted();
    }

    private function checkRequirements(): array
    {
        // Minimal requirements
        $messages[] = [
            'type' => 'info',
            'text' => 'installer.requirements.msg.min_requirements',
        ];

        // Required PHP version
        (PHP_VERSION_ID >= 80100)
            ? $messages[] = [
                'type' => 'success',
                'text' => 'installer.requirements.msg.php_ok',
                'placeholder' => [
                    'php' => InstallerConstant::PHP_VERSION,
                ],
            ] : $messages[] = [
                'type' => 'danger',
                'text' => 'installer.requirements.msg.php_fail',
                'placeholder' => [
                    'php' => InstallerConstant::PHP_VERSION,
                ],
            ];

        // Directories
        $notWritableDirs = $this->areNotWritableDirectories(BASE_DIRECTORY);

        empty($notWritableDirs)
            ? $messages[] = [
                'type' => 'success',
                'text' => 'installer.requirements.msg.directories_ok',
            ]
            : $messages[] = [
                'type' => 'danger',
                'text' => 'installer.requirements.msg.directories_fail',
                'placeholder' => [
                    'files' => implode(', ', $notWritableDirs),
                ],
            ];

        // Translations config
        try {
            if ($this->isConfigLanguage()) {
                $messages[] = [
                    'type' => 'success',
                    'text' => 'installer.requirements.msg.language_ok',
                ];
            }
        } catch (ExceptionHandler $e) {
            $messages[] = [
                'type' => 'danger',
                'text' => 'installer.requirements.msg.language_fail',
                'placeholder' => [
                    'error' => $e->getMessage(),
                ],
            ];
        }

        return $messages;
    }

    /**
     * Returns a list of directories that do not exist or are not writable.
     */
    private function areNotWritableDirectories(string $baseDir): array
    {
        $requiredDirs = ['', '/modules', '/public', '/templates', '/translations'];

        $invalid = [];

        foreach ($requiredDirs as $dir) {
            $path = rtrim($baseDir, DIRECTORY_SEPARATOR) . $dir;

            if (!is_dir($path) || !is_writable($path)) {
                $invalid[] = basename($path);
            }
        }

        return $invalid;
    }

    /**
     * Checks if APP_LANGUAGES config is valid.
     */
    private function isConfigLanguage(): bool
    {
        $appLanguages = getenv('APP_LANGUAGES');

        if ($appLanguages !== false && trim($appLanguages) !== '') {
            if (!preg_match('/^([A-Z]{2})(\\|[A-Z]{2})*$/', $appLanguages)) {
                $message = "Configuration APP_LANGUAGES contains an invalid format. Expected e.g. 'PL' or 'PL|EN|DE'.";
                $this->logger->error($message);
                throw new ExceptionHandler($message);
            }
        }

        return true;
    }
}
