<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

class Database
{
    private static ?PDO $pdo = null;
    private static ?Capsule $capsule = null;

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
        if (self::$capsule !== null && self::$pdo !== null) {
            return;
        }

        try {
            $capsule = new Capsule();
            $capsule->addConnection([
                'driver'    => 'mysql',
                'host'      => $this->host,
                'database'  => $this->database,
                'username'  => $this->username,
                'password'  => $this->password,
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix'    => '',
            ]);

            $capsule->setEventDispatcher(new Dispatcher(new Container));
            $capsule->setAsGlobal();
            $capsule->bootEloquent();

            self::$capsule = $capsule;
            self::$pdo = $capsule->getConnection()->getPdo();
        } catch (\Throwable $e) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4";
                self::$pdo = new PDO($dsn, $this->username, $this->password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $pe) {
                throw new \RuntimeException("Database connection failed: " . $pe->getMessage());
            }
        }
    }

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            throw new \RuntimeException("Database not connected.");
        }
        return self::$pdo;
    }

    public static function getCapsule(): ?Capsule
    {
        return self::$capsule;
    }
}

