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

interface SelectQueryBuilderInterface
{
    public function select(string|array ...$cols): self;
    public function from(string $table, ?string $alias = null): self;

    public function join(string $fromAlias, string $joinTable, string $joinAlias, string $on): self;
    public function leftJoin(string $fromAlias, string $joinTable, string $joinAlias, string $on): self;
    public function rightJoin(string $fromAlias, string $joinTable, string $joinAlias, string $on): self;

    public function where(string $expr): self;
    public function andWhere(string $expr): self;

    public function setParameter(string $key, mixed $value): self;

    public function getSQL(): string;
    public function getParameters(): array;
}
