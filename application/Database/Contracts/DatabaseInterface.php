<?php

/**
 * Module: DbM DataTables
 * PHP library for efficient CRUD operations and high-performance database management.
 *
 * This software is proprietary and licensed.
 * Use of this software is subject to the terms of the DbM Platform License.
 *
 * @author Artur Malinowski
 * @copyright Design by Malina
 * @license Proprietary
 *
 * @see /LICENSE_DBM_PLATFORM.txt
 * @link https://www.dbm.org.pl
 *
 * ==========================================================================
 *  DATABASE INTERFACE — USAGE EXAMPLES
 * ==========================================================================
 *
 * This interface defines two different types of query builders:
 *
 *   1) CRUD Query Builder (driver-specific)
 *      - Used for: INSERT, UPDATE, DELETE
 *      - Returned by: builder()
 *
 *   2) SELECT Query Builder (unified abstraction)
 *      - Used for: SELECT queries with fluent API
 *      - Returned by: createQueryBuilder()
 *
 * ==========================================================================
 *  EXAMPLE 1: CRUD Query Builder (INSERT / UPDATE / DELETE)
 * ==========================================================================
 *
 * // Insert example
 * [$sql, $params] = $this->database->builder()
 *     ->buildInsertQuery([
 *         'login'      => 'john',
 *         'email'      => 'john@example.com',
 *         'created_at' => date('Y-m-d H:i:s')
 *     ], 'dbm_user');
 *
 * $this->database->execute($sql, $params);
 *
 *
 * // Update example
 * [$sql, $params] = $this->database->builder()
 *     ->buildUpdateQuery(
 *         ['email' => 'new-email@example.com'],
 *         'dbm_user',
 *         'id = :id',
 *         ['id' => 5]
 *     );
 *
 * $this->database->execute($sql, $params);
 *
 *
 * ==========================================================================
 *  EXAMPLE 2: SELECT Query Builder (Doctrine DBAL / PDO Unified)
 * ==========================================================================
 *
 * public function test(): array
 * {
 *     $qb = $this->database->createQueryBuilder();
 *
 *     $qb->select('u.*')
 *         ->from('dbm_user', 'u')
 *         ->where('u.id = :id')
 *         ->andWhere('u.active = 1')
 *         ->setParameter('id', 1);
 *
 *     return $this->database->fetchAll(
 *         $qb->getSQL(),
 *         $qb->getParameters()
 *     );
 * }
 *
 * // Example generated SQL:
 * // SELECT u.* FROM dbm_user u WHERE u.id = :id AND u.active = 1
 *
 * ==========================================================================
 *  EXAMPLE 3: SELECT Query Builder
 * ==========================================================================
 *
 * public function userAccount(int $id): ?object
 * {
 *     $qb = $this->database->createQueryBuilder();
 *     $qb->select(['details.*', 'user.id','user.login', 'user.email', 'user.created_at'])
 *         ->from('dbm_user_details', 'details')
 *         ->join('details', 'dbm_user', 'user', 'details.user_id = user.id')
 *         ->where('user.id = :id')
 *         ->setParameter('id', $id);
 *     $row = $this->database->fetch(
 *         $qb->getSQL(),
 *         $qb->getParameters()
 *     );
 *     return $row ? $this->database->hydrate($row) : null;
 * }
 *
 * ==========================================================================
 */

declare(strict_types=1);

namespace Dbm\Database\Contracts;

interface DatabaseInterface
{
    public function databaseExists(string $database): bool;

    public function selectDatabase(string $database): void;

    /**
     * Returns the CRUD Query Builder (driver-specific).
     *
     * Use this for creating INSERT / UPDATE / DELETE SQL queries.
     *
     * Example:
     *     [$sql, $params] = $db->builder()->buildInsertQuery($data, 'dbm_user');
     */
    public function builder(): QueryBuilderInterface;

    /**
     * Returns a Query Builder for SELECT queries.
     *
     * This builder provides a fluent API identical across drivers (PDO, Doctrine).
     *
     * Example:
     *     $qb = $db->createQueryBuilder();
     *     $qb->select('id')->from('dbm_user')->where('id = :id')->setParameter('id', 1);
     */
    public function createQueryBuilder(): mixed;

    /**
     * Prepare & execute SQL query.
     *
     * @param string $sql
     * @param array  $params
     * @param array  $types  Driver-specific param types (Doctrine only).
     * @return mixed PDOStatement|Result depending on driver
     */
    public function query(string $sql, array $params = [], array $types = []): mixed;

    /**
     * Fetch single row as associative array.
     *
     * @return array|null
     */
    public function fetch(string $sql, array $params = [], array $types = []): ?array;

    /**
     * Fetch all rows as associative array list.
     *
     * @return array
     */
    public function fetchAll(string $sql, array $params = [], array $types = []): array;

    /**
     * Execute INSERT/UPDATE/DELETE.
     *
     * @return bool TRUE on success
     */
    public function execute(string $sql, array $params = [], array $types = []): bool;

    /**
     * Hydrate associative array into object (default: stdClass)
     *
     * @param array|null  $row
     * @param string|null $class Fully qualified class name or null
     */
    public function hydrate(?array $row, ?string $class = null): ?object;

    /**
     * Hydrate all rows into objects (default: stdClass)
     *
     * @param array $rows
     */
    public function hydrateAll(array $rows): array;

    /**
     * Get last auto-increment ID.
     *
     * @return string|null
     */
    public function getLastInsertId(): ?string;

    public function beginTransaction(): void;
    public function inTransaction(): bool;
    public function commit(): void;
    public function rollback(): void;

    /**
     * Close database connection.
     */
    public function close(): void;

    /**
     * Load & execute SQL file.
     */
    public function importSqlFile(string $filePath): bool;
}
