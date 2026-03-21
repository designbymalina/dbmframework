<?php

/**
 * Library: DbM DataTables PHP
 * Efficient backend CRUD system for easy database record management and table handling.
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

namespace Dbm\DataTables\Interfaces;

use Dbm\DataTables\Classes\TestBuiltQuery;

interface RepositoryInterface
{
    /**
    * Returns rows for given constraints.
    * @param array<string, mixed> $filters
    * @param string $sortKey Whitelisted logical sort key
    * @param 'ASC'|'DESC' $dir
    * @return array<int, array<string, mixed>|object>
    */
    public function list(int $limit, int $offset, array $filters, string $sortKey, string $dir): array;

    /**
    * Returns total rows for given filters (without limit/offset).
    * @param array<string, mixed> $filters
    */
    public function count(array $filters): int;

    /**
     * For testing purposes - download the most recently created query
     */
    public function getLastBuiltQuery(): ?TestBuiltQuery;
}
