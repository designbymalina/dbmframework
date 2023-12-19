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

/*
 * TODO! Dbm Exception Class dla zapytan SQL -> patrz do queryExecute()
*/
class DatabaseClass
{
    private $connect;
    private $result;

    public function __construct()
    {
        try {
            $this->connect = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_DATABASE, DB_USER, DB_PASSWORD, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
            $this->connect->exec("SET NAMES utf8");

            return $this->connect;
        } catch (PDOException $exception) {
            throw new DbmException($exception->getMessage(), $exception->getCode());
        }
    }

    public function querySql(string $sql, ?string $fetch = null): PDOStatement
    {
        if ($fetch == 'assoc') {
            $stmt = $this->connect->query($sql, PDO::FETCH_ASSOC);
        } else {
            $stmt = $this->connect->query($sql);
        }

        if (!$stmt) {
            throw new DbmException($this->connect->errorInfo()[2], $this->connect->errorInfo()[1]);
        } else {
            return $stmt;
        }
    }

    public function queryExecute(string $sql, ?array $params = []): bool
    {
        // TODO! Czy $this->result jest ok?
        $this->result = $this->connect->prepare($sql);

        if (empty($params)) {
            return $this->result->execute();
        } else {
            $first = array_key_first($params);

            if (is_string($first)) {
                foreach ($params as $key => &$value) {
                    $this->result->bindParam($key, $value);
                }

                return $this->result->execute();
            }

            return $this->result->execute($params);
        }

        // TODO! Jak wyswietlic blad w zapytaniu (SQL Exception)?
        // $this->result->debugDumpParams();
    }

    public function rowCount(): int
    {
        return $this->result->rowCount();
    }

    public function fetch(?string $fetch = null): array
    {
        if ($fetch == 'assoc') {
            return $this->result->fetch(PDO::FETCH_ASSOC);
        } else {
            return $this->result->fetch();
        }
    }

    public function fetchAll(?string $fetch = null): array
    {
        if ($fetch == 'assoc') {
            return $this->result->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return $this->result->fetchAll();
        }
    }

    public function fetchObject(): object
    {
        return $this->result->fetch(PDO::FETCH_OBJ);
    }

    public function fetchAllObject(): array
    {
        return $this->result->fetchAll(PDO::FETCH_OBJ);
    }

    public function errorInfo(): array
    {
        return $this->result->errorInfo();
    }

    public function lastInsertId(): ?string
    {
        return $this->connect->lastInsertId();
    }
}
