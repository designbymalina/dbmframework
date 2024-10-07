<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOException;

class DatabaseConnectionTest extends TestCase
{
    private $pdo;

    public function testConnection()
    {
        $this->assertNotNull($this->pdo, 'PDO instance is null');
        $this->assertInstanceOf(PDO::class, $this->pdo, 'PDO instance is not of type PDO');
    }

    protected function setUp(): void
    {
        $dbHost = getenv('DB_HOST');
        $dbName = getenv('DB_NAME');
        $dbUser = getenv('DB_USER');
        $dbPass = getenv('DB_PASSWORD');

        try {
            $dbDSN = "mysql:host=" . $dbHost . ";dbname=" . $dbName . ";charset=utf8";

            $this->pdo = new PDO($dbDSN, $dbUser, $dbPass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            $this->fail("ERROR! Connection failed: " . $exception->getMessage());
        }
    }
}
