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

namespace Dbm\Database\Contracts;

interface QueryBuilderInterface
{
    /**
     * Build INSERT query fragment or full query.
     * Returns [string $queryOrColumns, array $params, string|null $placeholdersIfPartial]
     * Convention: if $table provided returns [$fullQuery, $params], otherwise [$columns, $placeholders, $params]
     */
    public function buildInsertQuery(array $data, string $table): array;

    /**
     * Build UPDATE SET clause or full UPDATE query.
     * If $table provided returns [$fullQuery, $params], otherwise [$setClause, $params]
     */
    public function buildUpdateQuery(array $data, string $table, string $where, array $params = []): array;

    /**
     * Build DELETE query fragment or full query.
     */
    public function buildDeleteQuery(string $table, string $where, array $params = []): array;
}
