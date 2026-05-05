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

namespace Dbm\Database\Builder;

use Dbm\Database\Contracts\CrudQueryBuilderInterface;
use Dbm\Database\ValueObject\QueryResult;

class CrudQueryBuilder implements CrudQueryBuilderInterface
{
    public function buildInsertQuery(array $data, string $table): QueryResult
    {
        $filtered = array_filter($data, static fn($v) => $v !== null);

        $columns = implode(', ', array_keys($filtered));
        $placeholders = ':' . implode(', :', array_keys($filtered));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";

        return new QueryResult($sql, $filtered);
    }

    public function buildUpdateQuery(array $data, string $table, string $where, array $params = []): QueryResult
    {
        $filtered = array_filter($data, static fn($v) => $v !== null);

        $set = implode(
            ', ',
            array_map(static fn($col) => "{$col} = :{$col}", array_keys($filtered))
        );

        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";

        return new QueryResult(
            $sql,
            array_merge($filtered, $params)
        );
    }

    public function buildDeleteQuery(string $table, string $where, array $params = []): QueryResult
    {
        if (trim($where) === '') {
            throw new \InvalidArgumentException('DELETE requires WHERE');
        }

        return new QueryResult(
            "DELETE FROM {$table} WHERE {$where}",
            $params
        );
    }
}
