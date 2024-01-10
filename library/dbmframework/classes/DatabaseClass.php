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

class DatabaseClass // implements SingletonInterface
{
    //private static $instance = null;
    protected static $connect;
    private $statement;

    //private function __construct()
    public function __construct()
    {
        try {
            if (!self::$connect) {
                $dbDSN = "mysql:host=" . DB_HOST . ";dbname=" . DB_DATABASE;
                $dbOptions = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                ];
 
                self::$connect = new PDO($dbDSN, DB_USER, DB_PASSWORD, $dbOptions);
            }
            // TDDO! Co jesli nie If ?
        } catch (PDOException $exception) {
            throw new DbmException($exception->getMessage(), $exception->getCode());
        }
    }

    /* public static function getInstance() //: Result ?
    {
        if (!self::$instance) {
            self::$instance = new DatabaseClass();
        }

        return self::$instance;
    }

    public function getConnection() //: Result ?
    {
        return self::$connect; // $this->connect;
    } */

    public function querySql(string $query, string $fetch = 'assoc'): PDOStatement
    {
        try {
            if ($fetch == 'assoc') {
                return self::$connect->query($query, PDO::FETCH_ASSOC);
            }

            return self::$connect->query($query);
        } catch (PDOException $exception) {
            throw new DbmException($exception->getMessage(), $exception->errorInfo[1]);
        }
    }

    public function queryExecute(string $query, ?array $params = [], bool $reference = false): bool
    {
        try {
            $this->statement = self::$connect->prepare($query);
 
            if (empty($params)) {
                return $this->statement->execute();
            }
 
            $first = array_key_first($params);
 
            if (!is_string($first)) {
                return $this->statement->execute($params);
            }
 
            foreach ($params as $key => &$value) {
                //is_int($value) ? $type = PDO::PARAM_INT : $type = PDO::PARAM_STR;
                $type = $this->paramType($value);
 
                if (!$reference) {
                    $this->statement->bindValue($key, $value, $type);
                } else {
                    $this->statement->bindParam($key, $value, $type);
                }
            }
 
            return $this->statement->execute();
        } catch (PDOException $exception) {
            throw new DbmException($exception->getMessage(), $exception->errorInfo[1]);
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
        return self::$connect->lastInsertId();
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
