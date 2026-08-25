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
use Dbm\Database\Contracts\SelectQueryBuilderInterface;

class PdoSelectQueryBuilder implements SelectQueryBuilderInterface
{
    private string $from = '';
    private int $counter = 0;
    private ?int $limit = null;
    private ?int $offset = null;

    /** @var array<int, string> */
    private array $select = [];
    /** @var array<int, string> */
    private array $joins = [];
    /** @var array<int, string> */
    private array $where = [];
    /** @var array<string, mixed> */
    private array $params = [];
    /** @var array<int, string> */
    private array $orderBy = [];

    private PdoExpressionBuilder $expr;

    public function __construct()
    {
        $this->expr = new PdoExpressionBuilder();
    }

    /**
     * @param string|array<string>|array<array<string>> ...$cols
     */
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

    /**
     * @param list<mixed> $values
     */
    public function whereIn(string $field, array $values): self
    {
        return $this->where($this->buildInExpression($field, $values));
    }

    /**
     * @param list<mixed> $values
     */
    public function andWhereIn(string $field, array $values): self
    {
        return $this->andWhere($this->buildInExpression($field, $values));
    }

    public function orderBy(string $sort, ?string $order = null): self
    {
        $this->orderBy = [];

        return $this->addOrderBy($sort, $order);
    }

    public function addOrderBy(string $sort, ?string $order = null): self
    {
        $this->orderBy[] = $order
            ? "$sort " . strtoupper($order)
            : $sort;

        return $this;
    }

    public function setMaxResults(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function setFirstResult(int $offset): self
    {
        $this->offset = $offset;
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

        if ($this->orderBy) {
            $sql .= " ORDER BY " . implode(', ', $this->orderBy);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    public function setParameter(string $key, mixed $value): self
    {
        $this->params[$key] = $value;
        return $this;
    }

    /** @param list<mixed>|array<string, mixed> $params */
    public function setParameters(array $params): self
    {
        foreach ($params as $key => $value) {
            $this->params[$key] = $value;
        }

        return $this;
    }

    /** @return array<string, mixed> */
    public function getParameters(): array
    {
        return $this->params;
    }

    public function expr(): ExpressionBuilderInterface
    {
        return $this->expr;
    }

    // ===== Private =====

    /**
     * Builds a portable IN() expression.
     *
     * The DBM DatabaseInterface is based on SQL + named parameters,
     * therefore the expression is expanded into individual placeholders
     * instead of relying on database-specific array binding.
     *
     * This implementation may be simplified in the future if the
     * abstraction layer evolves beyond the current SQL + parameters model.
     *
     * @param list<mixed> $values
     */
    private function buildInExpression(string $field, array $values): string
    {
        if ($values === []) {
            return '1 = 0';
        }

        $group = ++$this->counter;

        $placeholders = [];

        foreach ($values as $i => $value) {
            $name = "in_{$group}_{$i}";

            $placeholders[] = ':' . $name;
            $this->params[$name] = $value;
        }

        return sprintf('%s IN (%s)', $field, implode(', ', $placeholders));
    }
}
