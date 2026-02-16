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

use Dbm\Core\DependencyContainer;
use Dbm\Core\Module\Repository\InstallRepository;
use Mod\Installer\Constants\InstallerConstant;

final class DatabaseStep extends AbstractInstallerStep
{
    private InstallRepository $repository;

    public function __construct(DependencyContainer $container)
    {
        parent::__construct($container);
        $this->repository = $container->get(InstallRepository::class);
    }

    public function getName(): string
    {
        return 'database';
    }

    public function getTitle(): string
    {
        return 'installer.step.database.title';
    }

    public function getDescription(): string
    {
        return ''; // optional: 'installer.step.database.content'
    }

    public function boot(): void
    {
        if ($this->isCompleted()) {
            $this->setPayload([
                'type' => InstallerConstant::ALERT,
                'class' => 'info',
                'text' => 'installer.alert.installation_ready',
            ]);

            $this->setDescription(null);
            return;
        }

        if (!empty($this->getPayload())) {
            return;
        }

        // Domyślny stan – pokazujemy formularz lub info
        $this->setPayload([
            'type' => InstallerConstant::TEXT,
            'text' => 'installer.step.database.content',
        ]);
    }

    /**
     * @param array<string, mixed> $input
     */
    public function handle(array $input): void
    {
        if (!$this->checkDbSettings()) {
            return;
        }

        $this->markCompleted();
    }

    private function checkDbSettings(): bool
    {
        $dbHost = getenv('DB_HOST');
        $dbName = getenv('DB_NAME');
        $dbUser = getenv('DB_USER');
        // INFO! Tymczasowo, można rozbudować.
        // Zamiast zgadywać po tabelach, wprowadź np. installation.lock.
        $dbTable = 'dbm_user';

        $enabledModules = $this->getInstallStepNames();

        if (!$dbHost) {
            return $this->fail('installer.database.msg.host_missing');
        }

        if (!$dbName) {
            return $this->fail('installer.database.msg.name_missing');
        }

        if (!$dbUser) {
            return $this->fail('installer.database.msg.user_missing');
        }

        if (!$this->repository->connect()) {
            return $this->fail('installer.database.msg.connection_failed');
        }

        if (!$this->repository->databaseExists($dbName)) {
            return $this->fail('installer.database.msg.not_exists');
        }

        if (!in_array('authentication', $enabledModules, true)) {
            if ($this->repository->tableExists($dbTable)) {
                return $this->fail('installer.database.msg.table_exists');
            }
        } elseif (!in_array('admin', $enabledModules, true)) {
            if (!$this->repository->tableExists($dbTable)) {
                return $this->fail('installer.database.msg.table_not_exists');
            }
        }

        $this->repository->selectDatabase($dbName);

        return true;
    }

    /**
     * @param array<string, mixed> $params
     */
    private function fail(string $message, array $params = []): bool
    {
        $this->setPayload([
            'type' => InstallerConstant::ALERT,
            'class' => 'danger',
            'text' => $message,
            'params' => $params,
        ]);

        return false;
    }

    /**
     * @return array<int, string>
     */
    private function getInstallStepNames(): array
    {
        $configPath = BASE_DIRECTORY . '/config/modules.php';

        if (!is_file($configPath)) {
            return [];
        }

        $modules = require $configPath;

        if (!is_array($modules)) {
            return [];
        }

        $enabled = $modules['enabled'] ?? [];

        return array_values($enabled);
    }
}
