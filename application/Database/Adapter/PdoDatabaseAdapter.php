<?php

/**
 * Application: DbM Framework
 * A lightweight PHP framework for building web applications.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina (All Rights Reserved)
 * @license MIT
 * @link https://www.dbm.org.pl
 *
 * NOTE: Parameter $types are not used in PDO - they are ignored, but according to the interface they must be present.
 */

declare(strict_types=1);

namespace Dbm\Database\Adapter;

use Dbm\Database\Builder\CrudQueryBuilder;
use Dbm\Database\Builder\PdoSelectQueryBuilder;
use Dbm\Database\Exceptions\QueryException;
use Dbm\Database\Hydrator\RowHydrator;
use Dbm\Database\Contracts\DatabaseInterface;
use Dbm\Database\Contracts\QueryBuilderInterface;
use Dbm\Database\Contracts\SelectQueryBuilderInterface;
use Dbm\Infrastructure\Log\Logger;
use PDO;
use PDOException;
use PDOStatement;

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
        $this->hydrator = new RowHydrator();

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
        $stmt = $this->pdo->query(
            "SHOW DATABASES LIKE " . $this->pdo->quote($database)
        );

        return (bool) $stmt->fetchColumn();
    }

    public function selectDatabase(string $database): void
    {
        $this->pdo->exec("USE `$database`");
    }

    /* ========================
     * QUERY BUILDERS
     * ======================== */

    public function builder(): QueryBuilderInterface
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

    public function query(string $sql, array $params = [], array $types = []): \PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($this->cleanSql($sql));
            $stmt->execute($params);
            return $stmt;
        } catch (\Throwable $exception) {
            $this->logger->critical('PDO query failed', [
                'sql' => $sql,
                'params' => $params,
                'exception' => $exception,
            ]);
            throw new QueryException($sql, $params, $exception);
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
        $stmt = $this->pdo->prepare($this->cleanSql($sql));
        return $stmt->execute($params);
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

    public function hydrate(?array $row, ?string $class = null): ?object
    {
        return $this->hydrator->hydrate($row, $class);
    }

    public function hydrateAll(array $rows): array
    {
        return array_map(fn($row) => $this->hydrate($row), $rows);
    }

    /* ========================
     * UTILITIES
     * ======================== */

    public function getLastInsertId(): ?string
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
}
