<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

class Model
{
    protected static ?PDO $db = null;

    protected static function db(): PDO
    {
        if (self::$db === null) {
            self::$db = Database::getConnection();
        }
        return self::$db;
    }

    protected static function table(): string
    {
        $class = basename(str_replace('\\', '/', static::class));
        return strtolower($class) . 's';
    }

    public static function find(int $id): ?array
    {
        $table = static::table();
        $stmt = self::db()->prepare("SELECT * FROM {$table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function all(): array
    {
        $table = static::table();
        $stmt = self::db()->query("SELECT * FROM {$table}");
        return $stmt->fetchAll();
    }

    public static function where(string $column, mixed $value): static
    {
        $instance = new static();
        $instance->query = "SELECT * FROM " . static::table() . " WHERE {$column} = :value";
        $instance->bindings[':value'] = $value;
        return $instance;
    }

    public static function create(array $data): int
    {
        unset($data['_token']);
        $table = static::table();
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $stmt = self::db()->prepare("INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($data);
        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        unset($data['_token']);
        $table = static::table();
        $setClause = implode(', ', array_map(fn($col) => "{$col} = :{$col}", array_keys($data)));

        $data['id'] = $id;
        $stmt = self::db()->prepare("UPDATE {$table} SET {$setClause} WHERE id = :id");
        return $stmt->execute($data);
    }

    public static function delete(int $id): bool
    {
        $table = static::table();
        $stmt = self::db()->prepare("DELETE FROM {$table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
