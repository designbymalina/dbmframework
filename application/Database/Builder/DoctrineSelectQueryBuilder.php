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

namespace Dbm\Database\Builder;

use Dbm\Database\Contracts\SelectQueryBuilderInterface;
use Doctrine\DBAL\Query\QueryBuilder;

class DoctrineSelectQueryBuilder implements SelectQueryBuilderInterface
{
    public function __construct(private QueryBuilder $qb) {}

    public function select(string|array ...$cols): self
    {
        $flat = [];
        foreach ($cols as $col) {
            if (is_array($col)) {
                $flat = array_merge($flat, $col);
            } else {
                $flat[] = $col;
            }
        }

        $this->qb->select(...$flat);
        return $this;
    }

    public function from(string $table, ?string $alias = null): self
    {
        $this->qb->from($table, $alias);
        return $this;
    }

    public function join(string $fromAlias, string $joinTable, string $joinAlias, string $on): self
    {
        $this->qb->join($fromAlias, $joinTable, $joinAlias, $on);
        return $this;
    }

    public function leftJoin(string $fromAlias, string $joinTable, string $joinAlias, string $on): self
    {
        $this->qb->leftJoin($fromAlias, $joinTable, $joinAlias, $on);
        return $this;
    }

    public function rightJoin(string $fromAlias, string $joinTable, string $joinAlias, string $on): self
    {
        $this->qb->rightJoin($fromAlias, $joinTable, $joinAlias, $on);
        return $this;
    }

    public function where(string $expr): self
    {
        $this->qb->where($expr);
        return $this;
    }

    public function andWhere(string $expr): self
    {
        $this->qb->andWhere($expr);
        return $this;
    }

    public function setParameter(string $key, mixed $value): self
    {
        $this->qb->setParameter($key, $value);
        return $this;
    }

    public function getSQL(): string
    {
        return $this->qb->getSQL();
    }

    public function getParameters(): array
    {
        return $this->qb->getParameters();
    }
}
