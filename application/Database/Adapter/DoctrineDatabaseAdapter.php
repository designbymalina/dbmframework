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
 * Example for parmeter $types in DBAL - when to use?
 * $types = ['ids' => \Doctrine\DBAL\Connection::PARAM_INT_ARRAY];
 * $this->database->fetchAll('SELECT * FROM t WHERE id IN (:ids)', ['ids' => [1,2,3]], $types);
 */

declare(strict_types=1);

namespace Dbm\Database\Adapter;

use Dbm\Database\Builder\CrudQueryBuilder;
use Dbm\Database\Builder\DoctrineSelectQueryBuilder;
use Dbm\Database\Exceptions\QueryException;
use Dbm\Database\Hydrator\RowHydrator;
use Dbm\Database\Contracts\DatabaseInterface;
use Dbm\Database\Contracts\QueryBuilderInterface;
use Dbm\Database\Contracts\SelectQueryBuilderInterface;
use Dbm\Infrastructure\Log\Logger;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;

class DoctrineDatabaseAdapter implements DatabaseInterface
{
    private Connection $conn;
    private CrudQueryBuilder $builder;
    private RowHydrator $hydrator;
    private Logger $logger;

    public function __construct(Connection $connection, ?RowHydrator $hydrator = null)
    {
        $this->conn = $connection;
        $this->builder = new CrudQueryBuilder();
        $this->hydrator = $hydrator ?? new RowHydrator();
        $this->logger = new Logger();
    }

    public function databaseExists(string $database): bool
    {
        $platform = get_class($this->conn->getDatabasePlatform());

        return match (true) {
            str_contains($platform, 'MySQL') => (bool) $this->conn
                ->executeQuery(
                    'SHOW DATABASES LIKE ?',
                    [$database]
                )
                ->fetchOne(),

            str_contains($platform, 'PostgreSQL') => (bool) $this->conn
                ->executeQuery(
                    'SELECT 1 FROM pg_database WHERE datname = ?',
                    [$database]
                )
                ->fetchOne(),

            default => throw new \RuntimeException(
                "databaseExists not supported for platform {$platform}"
            ),
        };
    }

    public function selectDatabase(string $database): void
    {
        $this->conn->executeStatement("USE `$database`");
    }

    /** @inheritDoc */
    public function builder(): QueryBuilderInterface
    {
        return new CrudQueryBuilder();
        // or -> return $this->builder;
    }

    /** @inheritDoc */
    public function createQueryBuilder(): SelectQueryBuilderInterface // or -> QueryBuilder
    {
        return new DoctrineSelectQueryBuilder(
            $this->conn->createQueryBuilder()
        );
        // or -> return $this->conn->createQueryBuilder();
    }

    /** @inheritDoc */
    public function query(string $sql, array $params = [], array $types = []): Result
    {
        try {
            return $this->conn->executeQuery($sql, $params, $types);
        } catch (\Throwable $exception) {
            $this->logger->critical("DBAL fetchAll: " . $exception->getMessage(), [
                'sql' => $sql,
                'params' => $params,
                'exception' => $exception,
            ]);
            throw new QueryException($sql, $params, $exception);
        }
    }

    /** @inheritDoc */
    public function fetch(string $sql, array $params = [], array $types = []): ?array
    {
        $result = $this->conn->executeQuery($sql, $params, $types);
        $row = $result->fetchAssociative();
        return $row ?: null;
    }

    /** @inheritDoc */
    public function fetchAll(string $sql, array $params = [], array $types = []): array
    {
        $result = $this->conn->executeQuery($sql, $params, $types);
        return $result->fetchAllAssociative() ?: [];
    }

    /** @inheritDoc */
    public function execute(string $sql, array $params = [], array $types = []): bool
    {
        $this->conn->executeStatement($sql, $params, $types);
        return true;
    }

    /** @inheritDoc */
    public function hydrate(?array $row, ?string $class = null): ?object
    {
        return $this->hydrator->hydrate($row, $class);
    }

    public function hydrateAll(array $rows): array
    {
        $objects = [];

        foreach ($rows as $row) {
            $objects[] = $this->hydrate($row);
        }

        return $objects;
    }

    /** @inheritDoc */
    public function getLastInsertId(): ?string
    {
        return $this->conn->lastInsertId();
    }

    /** @inheritDoc */
    public function beginTransaction(): void
    {
        $this->conn->beginTransaction();
    }

    /** @inheritDoc */
    public function inTransaction(): bool
    {
        return $this->conn->isTransactionActive();
    }

    /** @inheritDoc */
    public function commit(): void
    {
        $this->conn->commit();
    }

    /** @inheritDoc */
    public function rollback(): void
    {
        $this->conn->rollBack();
    }

    /** @inheritDoc */
    public function close(): void
    {
        $this->conn->close();
    }

    /** @inheritDoc */
    public function importSqlFile(string $filePath): bool
    {
        $sql = file_get_contents($filePath);
        $this->conn->executeStatement($sql);
        return true;
    }
}
