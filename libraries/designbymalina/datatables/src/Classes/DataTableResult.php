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

namespace Dbm\DataTables\Classes;

use Dbm\DataTables\DataTableParams;

final class DataTableResult
{
    /** @var array<int, array<string, mixed>|object> */
    public array $records;
    /** @var array<string, int|string> */
    public array $sider;

    /**
    * @param array<int, array<string, mixed>|object> $records
    * @param int $total
    */
    public function __construct(array $records, int $total, DataTableParams $params)
    {
        $pages = (int) ceil($total / max(1, $params->perPage));
        $this->records = $records;
        $this->sider = [
            'page' => $params->page,
            'perPage' => $params->perPage,
            'total' => $total,
            'pages' => $pages,
            'sort' => $params->sort,
            'dir' => $params->dir,
        ];
    }
}
