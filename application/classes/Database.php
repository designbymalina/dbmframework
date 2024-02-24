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
use Dbm\Interfaces\DatabaseInterface;
use PDO;
use PDOException;
use PDOStatement;

class Database implements DatabaseInterface
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
                if (is_int($value)) {
                    $type = PDO::PARAM_INT;
                } elseif (is_bool($value)) {
                    $type = PDO::PARAM_BOOL;
                } elseif (is_string($value)) {
                    $type = PDO::PARAM_STR;
                } elseif (is_null($value)) {
                    $type = PDO::PARAM_NULL;
                } else {
                    $type = false;
                }

                if ($type) {
                    if (!$reference) {
                        $this->statement->bindValue($key, $value, $type);
                    } else {
                        $this->statement->bindParam($key, $value, $type);
                    }
                } else {
                    throw new ExceptionHandler('No param type for bindValue() in queryExecute().', 400);
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
}
