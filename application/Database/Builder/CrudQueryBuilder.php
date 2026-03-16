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

namespace Dbm\Database\Builder;

use Dbm\Database\Contracts\QueryBuilderInterface;

class CrudQueryBuilder implements QueryBuilderInterface
{
    public function buildInsertQuery(array $data, ?string $table = null): array
    {
        $filteredData = array_filter($data, fn($v) => !is_null($v));
        $columns = implode(", ", array_keys($filteredData));
        $placeholders = ':' . implode(", :", array_keys($filteredData));

        if ($table) {
            $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
            return [$query, $filteredData];
        }

        return [$columns, $placeholders, $filteredData];
    }

    public function buildUpdateQuery(array $data, string $table, string $where, array $params = []): array
    {
        // 1. Pobierz nazwy parametrów z where (`col=:col`)
        preg_match_all('/\b([a-zA-Z0-9_]+)\s*=\s*:([a-zA-Z0-9_]+)/', $where, $matches);

        $conditionKeys = $matches[1] ?? [];

        // 2. Dane do WHERE (np. user_id = 5)
        $whereData = [];
        foreach ($conditionKeys as $col) {
            if (array_key_exists($col, $params)) {
                $whereData[$col] = $params[$col];
            }
        }

        // 3. Dane do SET
        $updateData = array_diff_key($data, $whereData);

        // 4. Usuń null, żeby nie generować `col=NULL`
        $updateData = array_filter($updateData, fn($v) => !is_null($v));

        // 5. Zbuduj SET col=:col
        $setClause = implode(
            ', ',
            array_map(fn($col) => "$col = :$col", array_keys($updateData))
        );

        // 6. Finalne SQL
        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s",
            $table,
            $setClause,
            $where
        );

        // 7. Połącz parametry SET i WHERE
        $finalParams = array_merge($updateData, $whereData);

        return [$sql, $finalParams];
    }

    public function buildDeleteQuery(string $table, string $where, array $params = []): array
    {
        if (trim($where) === '') {
            throw new \InvalidArgumentException("DELETE query requires WHERE clause.");
        }

        $sql = sprintf("DELETE FROM %s WHERE %s", $table, $where);

        return [$sql, $params];
    }
}
