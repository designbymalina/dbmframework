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
 */

declare(strict_types=1);

namespace Dbm\Database\Repository;

use Dbm\Database\Contracts\DatabaseInterface;

abstract class AbstractRepository
{
    protected DatabaseInterface $database;
    protected string $table;

    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    /**
     * Pobranie rekordu po ID.
     *
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $row = $this->database->fetch(
            "SELECT * FROM {$this->table} WHERE id = :id",
            ['id' => $id]
        );

        // return $this->database->hydrate($row); // TODO! Co lepiej tablica czy obiekt?
        return $row ?: null;
    }

    /**
     * Insert
     */
    public function insert(array $data): bool
    {
        [$sql, $params] = $this->database->builder()
            ->buildInsertQuery($data, $this->table);

        return $this->database->execute($sql, $params);
    }

    /**
     * Update
     */
    public function update(array $data, string $where, array $extra = []): bool
    {
        [$sql, $params] = $this->database->builder()
            ->buildUpdateQuery($data, $this->table, $where);

        return $this->database->execute($sql, array_merge($params, $extra));
    }
}
