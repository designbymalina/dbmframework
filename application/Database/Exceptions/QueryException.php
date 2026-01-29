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
