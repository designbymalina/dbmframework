<?php
/*
 * Application: DbM Framework v1.2
 * Author: Arthur Malinowsky (Design by Malina)
 * License: MIT
 * Web page: www.dbm.org.pl
 * Contact: biuro@dbm.org.pl
*/

declare(strict_types=1);

namespace Dbm\Classes;

use PDO;
use PDOException;
use PDOStatement;
use Dbm\Classes\ExceptionClass as DbmException;

class DatabaseClass extends PDO
{
    //private $connect;
    private $result;
    //public $pdo;

    public function __construct() // __construct(PDO $pdo)
    {
        try {
            $dbDSN = "mysql:host=" . DB_HOST . ";dbname=" . DB_DATABASE;
            $dbOptions = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
            ];

            //$this->connect = new PDO($dbDSN, DB_USER, DB_PASSWORD, $dbOptions);
            parent::__construct($dbDSN, DB_USER, DB_PASSWORD, $dbOptions);
        } catch (PDOException $exception) {
            throw new DbmException($exception->getMessage(), $exception->getCode());
        }
    }

    public function querySql(string $query, string $fetch = 'assoc'): PDOStatement
    {
        try {
            if ($fetch == 'assoc') {
                //return $this->connect->query($query, PDO::FETCH_ASSOC);
                return $this->query($query, PDO::FETCH_ASSOC);
            }

            //return $this->connect->query($query);
            return $this->query($query);
        } catch (PDOException $exception) {
            throw new DbmException($exception->getMessage(), $exception->errorInfo[1]);
        }
    }

    public function queryExecute(string $query, ?array $params = [], bool $reference = false): bool
    {
        // TODO! Czy $this->result jest ok?
        try {
            $this->result = $this->prepare($query);

            if (empty($params)) {
                return $this->result->execute();
            } else {
                $first = array_key_first($params);

                if (is_string($first)) {
                    foreach ($params as $key => &$value) {
                        is_int($value) ? $type = PDO::PARAM_INT : $type = PDO::PARAM_STR;

                        if (!$reference) {
                            $this->result->bindValue($key, $value, $type);
                        } else {
                            $this->result->bindParam($key, $value, $type);
                        }
                    }

                    return $this->result->execute();
                }

                return $this->result->execute($params);
            }
        } catch (PDOException $exception) {
            throw new DbmException($exception->getMessage(), $exception->errorInfo[1]);
        }
    }

    public function rowCount(): int
    {
        return $this->result->rowCount();
    }

    public function fetch(string $fetch = 'assoc'): array
    {
        if ($fetch == 'assoc') {
            return $this->result->fetch(PDO::FETCH_ASSOC);
        }

        return $this->result->fetch();
    }

    public function fetchAll(string $fetch = 'assoc'): array
    {
        if ($fetch == 'assoc') {
            return $this->result->fetchAll(PDO::FETCH_ASSOC);
        }

        return $this->result->fetchAll();
    }

    public function fetchObject(): object
    {
        return $this->result->fetch(PDO::FETCH_OBJ);
    }

    public function fetchAllObject(): array
    {
        return $this->result->fetchAll(PDO::FETCH_OBJ);
    }

    public function debugDumpParams(): ?string
    {
        return $this->result->debugDumpParams();
    }

    public function getLastInsertId(): ?string
    {
        //return $this->connect->lastInsertId();
        return $this->lastInsertId();
    }
}
