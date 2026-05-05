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
    /** @param string|array<string> ...$cols */
    public function select(string|array ...$cols): self;

    public function from(string $table, ?string $alias = null): self;

    public function join(string $fromAlias, string $joinTable, string $joinAlias, string $on): self;

    public function leftJoin(string $fromAlias, string $joinTable, string $joinAlias, string $on): self;

    public function rightJoin(string $fromAlias, string $joinTable, string $joinAlias, string $on): self;

    public function where(string $expr): self;

    public function andWhere(string $expr): self;

    public function orderBy(string $sort, ?string $order = null): self;

    public function addOrderBy(string $sort, ?string $order = null): self;

    public function setMaxResults(int $limit): self;

    public function setFirstResult(int $offset): self;

    public function getSQL(): string;

    public function setParameter(string $key, mixed $value): self;

    /** @param list<mixed>|array<string, mixed> $params */
    public function setParameters(array $params): self;

    /** @return array<string, mixed> */
    public function getParameters(): array;

    public function expr(): ExpressionBuilderInterface;
}
