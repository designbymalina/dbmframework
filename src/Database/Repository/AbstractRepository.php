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
use Dbm\Database\ValueObject\QueryResult;

abstract class AbstractRepository
{
    protected string $table; // Dla metod: find(), insert(), update(), delete().

    public function __construct(
        protected DatabaseInterface $database
    ) {
        $this->database = $database;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $qb = $this->database->createQueryBuilder();

        $qb->select('*')
            ->from($this->table)
            ->where('id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1);

        $row = $this->database->fetch(
            $qb->getSQL(),
            $qb->getParameters()
        );

        return $row ?: null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(array $data): bool
    {
        /** @var \Dbm\Database\ValueObject\QueryResult $query */
        $query = $this->database->builder()
            ->buildInsertQuery($data, $this->table);

        return $this->database->execute($query->sql, $query->params);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $params
     */
    public function update(array $data, string $where, array $params = []): bool
    {
        /** @var \Dbm\Database\ValueObject\QueryResult $query */
        $query = $this->database->builder()
            ->buildUpdateQuery($data, $this->table, $where, $params);

        return $this->database->execute($query->sql, $query->params);
    }

    /**
     * @param array<string, mixed> $params
     */
    public function delete(string $where, array $params = []): bool
    {
        /** @var \Dbm\Database\ValueObject\QueryResult $query */
        $query = $this->database->builder()
            ->buildDeleteQuery($this->table, $where, $params);

        return $this->database->execute($query->sql, $query->params);
    }

    protected function executeQuery(QueryResult $query): bool
    {
        return $this->database->execute($query->sql, $query->params);
    }
}
