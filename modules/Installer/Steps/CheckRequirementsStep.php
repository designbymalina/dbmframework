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
        if ($this->isCompleted()) {
            $this->setPayload([
                'type' => InstallerConstant::ALERT,
                'class' => 'info',
                'text' => 'installer.alert.installation_ready',
            ]);
            return;
        }

        $this->setPayload([
            'type' => InstallerConstant::LIST,
            'items' => $this->checkRequirements(),
        ]);
    }

    /**
     * @param array<string, mixed> $input
     */
    public function handle(array $input): void
    {
        $this->markCompleted();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function checkRequirements(): array
    {
        $messages = [];

        $messages = array_merge($messages, $this->checkCoreRequirements());

        /** @var string[] $pending */
        $pending = $this->state->get('pending_packages', []);

        // CMS Lite
        if (in_array('cmslite', $pending, true)) {
            $messages = array_merge($messages, $this->checkCmsRequirements());
        }

        // Admin-related modules
        $adminRelated = ['authentication', 'admin'];

        if (!empty(array_intersect($adminRelated, $pending))) {
            $messages = array_merge($messages, $this->checkAdminRequirements());
        }

        return $messages;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function checkCoreRequirements(): array
    {
        // Requirements
        $messages[] = [
            'type' => 'info',
            'text' => 'installer.requirements.msg.core_requirements',
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

        return $messages;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function checkCmsRequirements(): array
    {
        // Requirements
        $messages[] = [
            'type' => 'info',
            'text' => 'installer.requirements.msg.cms_requirements',
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

        return $messages;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function checkAdminRequirements(): array
    {
        // Requirements
        $messages[] = [
            'type' => 'info',
            'text' => 'installer.requirements.msg.admin_requirements',
        ];

        // Required extensions
        $requiredExtensions = ['pdo', 'pdo_mysql', 'session', 'json'];

        foreach ($requiredExtensions as $ext) {
            $messages[] = extension_loaded($ext) ? [
                'type' => 'success',
                'text' => 'installer.requirements.msg.extension_ok',
                'placeholder' => [
                    'ext' => $ext,
                ],
            ] : [
                'type' => 'danger',
                'text' => 'installer.requirements.msg.extension_fail',
                'placeholder' => [
                    'ext' => $ext,
                ],
            ];
        }

        return $messages;
    }

    /**
     * Returns a list of directories that do not exist or are not writable.
     *
     * @return array<int, string>
     */
    private function areNotWritableDirectories(string $baseDir): array
    {
        $requiredDirs = ['/modules', '/public', '/templates', '/translations'];

        $invalid = [];

        foreach ($requiredDirs as $dir) {
            $path = rtrim($baseDir, DIRECTORY_SEPARATOR) . $dir;

            if (!is_dir($path) || !is_writable($path)) {
                $invalid[] = basename($path);
            }
        }

        return $invalid;
    }
}
