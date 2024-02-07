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

use Dbm\Classes\ExceptionHandler;
use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private $connect;
    private $statement;

    public function __construct()
    {
        try {
            $dbDSN = "mysql:host=" . DB_HOST . ";dbname=" . DB_DATABASE;
            $dbOptions = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
            ];

            $this->connect = new PDO($dbDSN, DB_USER, DB_PASSWORD, $dbOptions);
        } catch (PDOException $exception) {
            throw new ExceptionHandler($exception->getMessage(), $exception->getCode());
        }
    }

    public function querySql(string $query, string $fetch = 'assoc'): PDOStatement
    {
        try {
            if ($fetch == 'assoc') {
                return $this->connect->query($query, PDO::FETCH_ASSOC);
            }

            return $this->connect->query($query);
        } catch (PDOException $exception) {
            throw new ExceptionHandler($exception->getMessage(), $exception->errorInfo[1]);
        }
    }

    public function queryExecute(string $query, ?array $params = [], bool $reference = false): bool
    {
        try {
            $this->statement = $this->connect->prepare($query);

            if (empty($params)) {
                return $this->statement->execute();
            }

            $first = array_key_first($params);

            if (!is_string($first)) {
                return $this->statement->execute($params);
            }

            foreach ($params as $key => &$value) {
                $type = $this->paramType($value);

                if (!$reference) {
                    $this->statement->bindValue($key, $value, $type);
                } else {
                    $this->statement->bindParam($key, $value, $type);
                }
            }

            return $this->statement->execute();
        } catch (PDOException $exception) {
            throw new ExceptionHandler($exception->getMessage(), $exception->errorInfo[1]);
        }
    }

    public function rowCount(): int
    {
        return $this->statement->rowCount();
    }

    public function fetch(string $fetch = 'assoc'): array
    {
        if ($fetch == 'assoc') {
            return $this->statement->fetch(PDO::FETCH_ASSOC);
        }

        return $this->statement->fetch();
    }

    public function fetchAll(string $fetch = 'assoc'): array
    {
        if ($fetch == 'assoc') {
            return $this->statement->fetchAll(PDO::FETCH_ASSOC);
        }

        return $this->statement->fetchAll();
    }

    public function fetchObject(): object
    {
        return $this->statement->fetch(PDO::FETCH_OBJ);
    }

    public function fetchAllObject(): array
    {
        return $this->statement->fetchAll(PDO::FETCH_OBJ);
    }

    public function debugDumpParams(): ?string
    {
        return $this->statement->debugDumpParams();
    }

    public function getLastInsertId(): ?string
    {
        return $this->connect->lastInsertId();
    }

    /* TODO! Sprawdz metode i rozne opcje */
    private function paramType($value) // value type? : Result
    {
        switch (true) {
            case is_null($value):
                return PDO::PARAM_NULL;
                break;
            case is_int($value):
                return PDO::PARAM_INT;
                break;
            case is_bool($value):
                return PDO::PARAM_BOOL;
                break;
            default:
                return PDO::PARAM_STR;
        }
    }
}
