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

namespace Dbm\Database\Exceptions;

class QueryException extends \RuntimeException
{
    public function __construct(
        public readonly string $sql,
        public readonly array $params,
        \Throwable $previous
    ) {
        parent::__construct(
            $previous->getMessage(),
            (int) $previous->getCode(),
            $previous
        );
    }
}
