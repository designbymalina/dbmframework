<?php
/*
 * Application: DbM Framework v2.1
 * Author: Arthur Malinowsky (Design by Malina)
 * License: MIT
 * Web page: www.dbm.org.pl
 * Contact: biuro@dbm.org.pl
*/

declare(strict_types=1);

namespace Dbm\Classes;

//namespace Dbm\Interfaces; // Dbm\Contracts ?

class DatabaseInterface
{
    private $connect;
    private $statement;

    public function __construct()
    {
        $this->connect;
    }

    public function querySql(string $query, string $fetch = 'assoc')
    {
    }

    public function queryExecute(string $query, ?array $params = [], bool $reference = false)
    {
        return $this->statement;
    }

    public function rowCount()
    {
    }

    public function fetchAll(string $fetch = 'assoc')
    {
    }

    public function fetchObject()
    {
    }

    public function fetchAllObject(): object
    {
        return $this->statement;
    }

    public function debugDumpParams()
    {
    }

    public function getLastInsertId()
    {
    }

    //private function paramType() {}
}
