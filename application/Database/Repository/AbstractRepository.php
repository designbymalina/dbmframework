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
     */
    public function find(int $id): ?object
    {
        $row = $this->database->fetch(
            "SELECT * FROM {$this->table} WHERE id = :id",
            ['id' => $id]
        );

        return $this->database->hydrate($row);
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
