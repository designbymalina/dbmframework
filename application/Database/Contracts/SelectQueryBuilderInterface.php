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
