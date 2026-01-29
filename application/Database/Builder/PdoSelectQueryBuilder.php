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

use Dbm\Database\Contracts\SelectQueryBuilderInterface;

class PdoSelectQueryBuilder implements SelectQueryBuilderInterface
{
    private array $select = [];
    private string $from = '';
    private array $joins = [];
    private array $where = [];
    private array $params = [];

    public function select(string|array ...$cols): self
    {
        foreach ($cols as $col) {
            if (is_array($col)) {
                $this->select = array_merge($this->select, $col);
            } else {
                $this->select[] = $col;
            }
        }
        return $this;
    }

    public function from(string $table, ?string $alias = null): self
    {
        $this->from = $alias ? "$table AS $alias" : $table;
        return $this;
    }

    public function join(string $fromAlias, string $joinTable, string $joinAlias, string $on): self
    {
        $this->joins[] = "JOIN $joinTable AS $joinAlias ON $on";
        return $this;
    }

    public function leftJoin(string $fromAlias, string $joinTable, string $joinAlias, string $on): self
    {
        $this->joins[] = "LEFT JOIN $joinTable AS $joinAlias ON $on";
        return $this;
    }

    public function rightJoin(string $fromAlias, string $joinTable, string $joinAlias, string $on): self
    {
        $this->joins[] = "RIGHT JOIN $joinTable AS $joinAlias ON $on";
        return $this;
    }

    public function where(string $expr): self
    {
        $this->where = [$expr];
        return $this;
    }

    public function andWhere(string $expr): self
    {
        $this->where[] = $expr;
        return $this;
    }

    public function setParameter(string $key, mixed $value): self
    {
        $this->params[$key] = $value;
        return $this;
    }

    public function getSQL(): string
    {
        $sql = "SELECT " . implode(', ', $this->select)
            . " FROM {$this->from}";

        if ($this->joins) {
            $sql .= " " . implode(" ", $this->joins);
        }

        if ($this->where) {
            $sql .= " WHERE " . implode(" AND ", $this->where);
        }

        return $sql;
    }

    public function getParameters(): array
    {
        return $this->params;
    }
}
