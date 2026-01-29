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

namespace Dbm\Core\Module\Repository;

use Dbm\Database\Contracts\DatabaseInterface;
use Dbm\Database\DatabaseFactory;

final class InstallRepository
{
    private ?DatabaseInterface $database = null;

    public function connect(): bool
    {
        try {
            $this->database = DatabaseFactory::createDatabase();
            return true;
        } catch (\Throwable) {
            $this->database = null;
            return false;
        }
    }

    public function isConnected(): bool
    {
        return $this->database !== null;
    }

    public function databaseExists(string $name): bool
    {
        if ($this->database === null) {
            return false;
        }

        return $this->database->databaseExists($name);
    }

    public function selectDatabase(string $name): void
    {
        if ($this->database === null) {
            throw new \RuntimeException('Database not connected');
        }

        $this->database->selectDatabase($name);
    }

    public function getDatabase(): DatabaseInterface
    {
        if ($this->database === null) {
            throw new \RuntimeException('Database not connected');
        }

        return $this->database;
    }

    /**
     * Import SQL
     */
    public function importDataFromFile(string $filePath): bool
    {
        if ($this->database === null) {
            return false;
        }

        return $this->database->importSqlFile($filePath);
    }

    /**
     * Sprawdza istnienie tabeli
     */
    public function tableExists(string $table): bool
    {
        if ($this->database === null) {
            return false;
        }

        $row = $this->database->fetch(
            'SHOW TABLES LIKE :table',
            ['table' => $table]
        );

        return $row !== null;
    }
}
