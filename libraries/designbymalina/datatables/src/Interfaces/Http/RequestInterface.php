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

namespace Dbm\DataTables\Interfaces\Http;

interface RequestInterface
{
    public function getQuery(string $key, mixed $default = null): mixed;

    /**
     * @return array<string, mixed>
     */
    public function getQueryParams(): array;

    /**
     * @return array<string, mixed>
     */
    public function getServerParams(): array;
}
