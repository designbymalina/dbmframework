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
    public function querySql(string $query, string $fetch = 'assoc'): PDOStatement; // TODO!

    public function queryExecute(string $query, ?array $params = [], bool $reference = false): bool; // TODO! Check in controllers and models DatabaseInterface -> Database

    public function rowCount(): int;

    public function fetch(string $fetch = 'assoc'): array; // TODO!

    public function fetchAll(string $fetch = 'assoc'): array; // TODO!

    public function fetchObject(): object;

    public function fetchAllObject(): array;

    public function debugDumpParams(): ?string;

    public function getLastInsertId(): ?string;
}
