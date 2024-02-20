<?php
/*
 * Application: DbM Framework v2.1
 * Author: Arthur Malinowsky (Design by Malina)
 * License: MIT
 * Web page: www.dbm.org.pl
 * Contact: biuro@dbm.org.pl
*/

declare(strict_types=1);

namespace Dbm\Interfaces;

use PDOStatement;

interface DatabaseInterface
{
    public function querySql(string $query, string $fetch = 'assoc'): PDOStatement;

    public function queryExecute(string $query, ?array $params = [], bool $reference = false): bool;

    public function rowCount(): int;

    public function fetch(string $fetch = 'assoc'): array;

    public function fetchAll(string $fetch = 'assoc'): array;

    public function fetchObject(): object;

    public function fetchAllObject(): array;

    public function debugDumpParams(): ?string;

    public function getLastInsertId(): ?string;
}
