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

namespace Dbm\Database;

use Dbm\Database\Adapter\DoctrineDatabaseAdapter;
use Dbm\Database\Adapter\PdoDatabaseAdapter;
use Dbm\Database\Validator\DatabaseEnvValidator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PDO;

final class DatabaseFactory
{
    public static function createDatabase(): PdoDatabaseAdapter|DoctrineDatabaseAdapter
    {
        $driverRaw = getenv('DB_DRIVER') ?: 'PDO|pdo_mysql';

        // Format: MAIN|SUB, np. PDO|pdo_mysql
        $parts = explode('|', $driverRaw);

        if (count($parts) !== 2) {
            $main = 'PDO';
            $sub = 'pdo_mysql';
        } else {
            [$main, $sub] = $parts;
            $main = strtoupper(trim($main));
            $sub = strtolower(trim($sub));
        }

        return match ($main) {
            'DOCTRINE' => self::createDoctrineFromEnv($sub),
            'PDO' => self::createPdoFromEnv($sub),
            default => self::createPdoFromEnv('pdo_mysql'),
        };
    }

    private static function createPdoFromEnv(string $sub): PdoDatabaseAdapter
    {
        $pdoDriver = match ($sub) {
            'pdo_pgsql' => 'pgsql',
            'pdo_sqlite' => 'sqlite',
            default => 'mysql',
        };

        if (!in_array($pdoDriver, PDO::getAvailableDrivers(), true)) {
            throw new \RuntimeException(
                "PDO driver '{$pdoDriver}' is not available. Installed drivers: "
                . implode(', ', PDO::getAvailableDrivers())
            );
        }

        DatabaseEnvValidator::validate(requireDatabase: $pdoDriver !== 'sqlite');

        return new PdoDatabaseAdapter(
            dbHost: getenv('DB_HOST'),
            dbUser: getenv('DB_USER'),
            dbPassword: getenv('DB_PASSWORD'),
            dbPort: getenv('DB_PORT') ?: '3306',
            dbCharset: getenv('DB_CHARSET') ?: 'utf8mb4',
            driver: $pdoDriver,
            dbName: getenv('DB_NAME') ?: null,
        );
    }

    private static function createDoctrineFromEnv(string $sub): DoctrineDatabaseAdapter
    {
        $connectionParams = [
            'host' => getenv('DB_HOST'),
            'user' => getenv('DB_USER'),
            'password' => getenv('DB_PASSWORD'),
            'driver' => $sub, // np. pdo_mysql, pdo_pgsql, pdo_sqlite
            'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
        ];

        if ($dbName = getenv('DB_NAME')) {
            $connectionParams['dbname'] = $dbName;
        }

        $connection = DriverManager::getConnection($connectionParams);

        return new DoctrineDatabaseAdapter($connection);
    }

    public static function createPdo(): PdoDatabaseAdapter
    {
        return self::createPdoFromEnv('pdo_mysql');
    }

    public static function createDoctrine(Connection $connection): DoctrineDatabaseAdapter
    {
        return new DoctrineDatabaseAdapter($connection);
    }
}
