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

namespace Dbm\Database\Builder;

use Dbm\Database\Contracts\ExpressionBuilderInterface;

class PdoExpressionBuilder implements ExpressionBuilderInterface
{
    public function and(string ...$conditions): string
    {
        return '(' . implode(' AND ', $conditions) . ')';
    }

    public function or(string ...$conditions): string
    {
        return '(' . implode(' OR ', $conditions) . ')';
    }

    public function eq(string $x, string $y): string
    {
        return "$x = $y";
    }

    public function like(string $x, string $y): string
    {
        return "$x LIKE $y";
    }

    /**
     * @INFO Nie jest bezpieczne przy PDO,
     * docelowo generuj placeholdery: :id1, :id2, :id3
     */
    public function in(string $x, string|array $y): string
    {
        return "$x IN (" . implode(', ', $y) . ")";
    }
}
