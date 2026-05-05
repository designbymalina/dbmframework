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

interface ExpressionBuilderInterface
{
    public function and(string ...$conditions): string;

    public function or(string ...$conditions): string;

    public function eq(string $x, string $y): string;

    public function like(string $x, string $y): string;

    /** @param string|string[] $y */
    public function in(string $x, string|array $y): string;
}
