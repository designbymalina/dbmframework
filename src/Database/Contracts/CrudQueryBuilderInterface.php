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

namespace Dbm\Database\Contracts;

use Dbm\Database\ValueObject\QueryResult;

interface CrudQueryBuilderInterface
{
    /**
     * Build INSERT query fragment or full query.
     * Returns [string $queryOrColumns, array $params, string|null $placeholdersIfPartial]
     * Convention: if $table provided returns [$fullQuery, $params], otherwise [$columns, $placeholders, $params]
     *
     * @param array<string, mixed> $data
     */
    public function buildInsertQuery(array $data, string $table): QueryResult;

    /**
     * Build UPDATE SET clause or full UPDATE query.
     * If $table provided returns [$fullQuery, $params], otherwise [$setClause, $params]
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $params
     */
    public function buildUpdateQuery(array $data, string $table, string $where, array $params = []): QueryResult;

    /**
     * Build DELETE query fragment or full query.
     *
     * @param array<string, mixed> $params
     */
    public function buildDeleteQuery(string $table, string $where, array $params = []): QueryResult;
}
