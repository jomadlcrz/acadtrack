<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public function __construct(
        private string $host,
        private string $database,
        private string $username,
        private string $password
    ) {
        self::connect();
    }

    private function connect(): void
    {
        if (self::$pdo !== null) {
            return;
        }

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4";
            self::$pdo = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            throw new \RuntimeException("Database not connected.");
        }
        return self::$pdo;
    }
}
