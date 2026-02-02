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
        return 'installer.step.database.content';
    }

    public function boot(): void
    {
        if ($this->isDone()) {
            $this->setPayload([
                'type' => InstallerConstant::ALERT,
                'class' => 'info',
                'text' => 'installer.alert.already_installed',
            ]);

            $this->setDescription(null);
            return;
        }

        if (!empty($this->getPayload())) {
            return;
        }

        if (!$this->repository->connect()) {
            $this->setPayload([
                'type' => InstallerConstant::ALERT,
                'class' => 'danger',
                'text' => 'installer.alert.database_connection_failed',
            ]);
            return;
        }

        $dbName = getenv('DB_NAME');

        if (!$dbName) {
            $this->setPayload([
                'type' => InstallerConstant::ALERT,
                'class' => 'danger',
                'text' => 'installer.alert.database_name_missing',
            ]);
            return;
        }

        if (!$this->repository->databaseExists($dbName)) {
            $this->setPayload([
                'type' => InstallerConstant::ALERT,
                'class' => 'danger',
                'text' => 'installer.alert.database_not_exists',
                'params' => ['database' => $dbName],
            ]);
            return;
        }

        // [? Załóżenie - naciągane] Poprzedni krok został pomyślnie ukończony
        $this->setPayload([
            'type' => InstallerConstant::ALERT,
            'class' => 'success',
            'text' => 'installer.alert.installation_success_cmslite',
        ]);

        $this->repository->selectDatabase($dbName);

        $this->markCompleted();
    }

    public function handle(array $input): void
    {
        // default null
    }
}
