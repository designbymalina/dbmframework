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
use Dbm\Database\ValueObject\SqlExpression;

class CrudQueryBuilder implements CrudQueryBuilderInterface
{
    public function buildInsertQuery(array $data, string $table): QueryResult
    {
        $filtered = array_filter($data, static fn($v) => $v !== null);

        $columns = [];
        $values = [];
        $params = [];

        foreach ($filtered as $column => $value) {
            $columns[] = $column;

            if ($value instanceof SqlExpression) {
                $values[] = $value->sql;
                continue;
            }

            $values[] = ':' . $column;
            $params[$column] = $value;
        }

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', $values)
        );

        return new QueryResult($sql, $params);
    }

    public function buildUpdateQuery(array $data, string $table, string $where, array $params = []): QueryResult
    {
        $filtered = array_filter(
            $data,
            static fn($v) => $v !== null
        );

        $set = [];
        $values = [];

        foreach ($filtered as $column => $value) {
            if ($value instanceof SqlExpression) {
                $set[] = "{$column} = {$value->sql}";
                continue;
            }

            $set[] = "{$column} = :{$column}";
            $values[$column] = $value;
        }

        $sql = "UPDATE {$table} SET " . implode(', ', $set) . " WHERE {$where}";

        return new QueryResult(
            $sql,
            array_merge($values, $params)
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
