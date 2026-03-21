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
 *
 * Usage example - query preview in controller (test of the built query):
 * $dtService = $this->dataTable->withParams($dtParams);
 * 1. Typical option
 * $dtResult = $dtService->paginate($this->configDataTable);
 * 2. Option RAW
 * $sql = $this->configDataTable->getSql();
 * $maps = $this->configDataTable->getMaps();
 * $dtResult = $dtService->paginateRaw($sql, $maps);
 * 3. Show Query
 * dump($dtService->getLastBuiltQuery());
 */

declare(strict_types=1);

namespace Dbm\DataTables\Classes;

final class TestBuiltQuery
{
    public function __construct(
        public string $sql,
        /** @var array<string,mixed> */
        public array $params = []
    ) {}

    public function __toString(): string
    {
        return $this->sql . ' | PARAMS: ' . json_encode($this->params, JSON_UNESCAPED_UNICODE);
    }
}
