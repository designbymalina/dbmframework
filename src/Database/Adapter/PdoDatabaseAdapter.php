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

namespace Dbm\Database\Adapter;

use Dbm\Database\Builder\CrudQueryBuilder;
use Dbm\Database\Builder\PdoSelectQueryBuilder;
use Dbm\Database\Contracts\CrudQueryBuilderInterface;
use Dbm\Database\Exceptions\QueryException;
use Dbm\Database\Hydrator\RowHydrator;
use Dbm\Database\Contracts\DatabaseInterface;
use Dbm\Database\Contracts\ResultInterface;
use Dbm\Database\Contracts\SelectQueryBuilderInterface;
use Dbm\Database\Hydrator\SmartHydrator;
use Dbm\Debug\DebugRegistry;
use Dbm\Infrastructure\Log\Logger;
use PDO;
use PDOException;

final class PdoDatabaseAdapter implements DatabaseInterface
{
    private PDO $pdo;
    private Logger $logger;
    private CrudQueryBuilder $builder;
    private RowHydrator $hydrator;

    public function __construct(
        string $dbHost,
        string $dbUser,
        string $dbPassword,
        string $dbPort = '3306',
        string $dbCharset = 'utf8mb4',
        string $driver = 'mysql',
        ?string $dbName = null,
    ) {
        $this->logger = new Logger();
        $this->builder = new CrudQueryBuilder();

        $this->hydrator = new RowHydrator(
            new SmartHydrator()
        );

        $dsn = match ($driver) {
            'sqlite' => "sqlite::memory:",
            default  => $dbName
                ? "$driver:host=$dbHost;port=$dbPort;dbname=$dbName;charset=$dbCharset"
                : "$driver:host=$dbHost;port=$dbPort;charset=$dbCharset",
        };

        if ($driver === 'sqlite') {
            $this->pdo = new PDO($dsn);
            return;
        }

        try {
            $this->pdo = new PDO(
                $dsn,
                $dbUser,
                $dbPassword,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => true,
                ]
            );
        } catch (PDOException $exception) {
            $this->logger->critical(
                'PDO connection failed',
                ['exception' => $exception]
            );
            throw $exception;
        }
    }

    /* ========================
     * DATABASE CONTROL
     * ======================== */
    public function databaseExists(string $database): bool
    {
        $sql = 'SHOW DATABASES LIKE ' . $this->pdo->quote($database);

        try {
            $stmt = $this->pdo->query($sql);
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->wrapQueryException($e, $sql);
        }
    }

    public function selectDatabase(string $database): void
    {
        $sql = "USE `$database`";

        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            $this->wrapQueryException($e, $sql);
        }
    }

    /* ========================
     * QUERY BUILDERS
     * ======================== */

    public function builder(): CrudQueryBuilderInterface
    {
        return $this->builder;
    }

    public function createQueryBuilder(): SelectQueryBuilderInterface
    {
        return new PdoSelectQueryBuilder();
    }

    /* ========================
     * QUERY EXECUTION
     * ======================== */

    public function query(string $sql, array $params = [], array $types = []): ResultInterface
    {
        $start = microtime(true);

        try {
            $stmt = $this->pdo->prepare($this->cleanSql($sql));
            $stmt->execute($params);

            $time = (microtime(true) - $start) * 1000;

            if ($toolbar = DebugRegistry::getToolbar()) {
                $toolbar->collectSQL($sql, $time);
            }

            return new PdoResultAdapter($stmt);
        } catch (PDOException $exception) {
            $this->wrapQueryException($exception, $sql, $params);
        }
    }

    public function fetch(string $sql, array $params = [], array $types = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $row  = $stmt->fetch();
        return $row ?: null;
    }

    public function fetchAll(string $sql, array $params = [], array $types = []): array
    {
        return $this->query($sql, $params)->fetchAll() ?: [];
    }

    public function execute(string $sql, array $params = [], array $types = []): bool
    {
        try {
            $stmt = $this->pdo->prepare($this->cleanSql($sql));
            return $stmt->execute($params);
        } catch (PDOException $exception) {
            $this->wrapQueryException($exception, $sql, $params);
        }
    }

    /* ========================
     * TRANSACTIONS
     * ======================== */

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    /* ========================
     * HYDRATION
     * ======================== */

    /**
     * Hydrate database row into an object.
     *
     * Hydration is provided by DBM Framework and is independent
     * of the underlying database adapter or ORM.
     *
     * Database adapters must support DBM hydration regardless
     * of whether they use PDO, Doctrine or another database layer.
     *
     * @param array<string,mixed>|null $row
     */
    public function hydrate(?array $row, ?string $class = null): ?object
    {
        return $this->hydrator->hydrate($row, $class);
    }

    /**
     * Hydrate multiple database rows into objects.
     *
     * Hydration is provided by DBM Framework and is independent
     * of the underlying database adapter or ORM.
     *
     * @param array<int,array<string,mixed>> $rows
     * @return array<int,object|null>
     */
    public function hydrateAll(array $rows, ?string $class = null): array
    {
        return $this->hydrator->hydrateAll($rows, $class);
    }

    /* ========================
     * UTILITIES
     * ======================== */

    public function getLastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    public function importSqlFile(string $filePath): bool
    {
        if (!is_file($filePath)) {
            return false;
        }

        return $this->pdo->exec(file_get_contents($filePath)) !== false;
    }

    public function close(): void
    {
        unset($this->pdo);
    }

    private function cleanSql(string $sql): string
    {
        return preg_replace('/\s+/', ' ', trim($sql));
    }

    /**
     * @param array<string, mixed> $params
     */
    private function wrapQueryException(PDOException $e, string $sql, array $params = []): never
    {
        $this->logger->critical('PDO query failed', [
            'sql' => $sql,
            'params' => $params,
            'exception' => $e,
        ]);

        throw new QueryException($sql, $params, $e);
    }
}
