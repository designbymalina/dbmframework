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

namespace Dbm\Database\Adapter;

use Dbm\Database\Contracts\ResultInterface;

class PdoResultAdapter implements ResultInterface
{
    public function __construct(
        private \PDOStatement $stmt
    ) {}

    public function fetch(): ?array
    {
        $row = $this->stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function fetchAll(): array
    {
        return $this->stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
