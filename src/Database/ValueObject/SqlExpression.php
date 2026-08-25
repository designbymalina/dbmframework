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

namespace Dbm\Database\ValueObject;

final class SqlExpression
{
    private function __construct(
        public readonly string $sql
    ) {}

    /**
     * @NOTE This method is not used to transmit user data.
     */
    public static function raw(string $sql): self
    {
        return new self($sql);
    }

    // ===== Expression helpers =====

    public static function increment(
        string $column,
        int $amount = 1
    ): self {
        return new self(
            $amount === 1
                ? "{$column} + 1"
                : "{$column} + {$amount}"
        );
    }
}
