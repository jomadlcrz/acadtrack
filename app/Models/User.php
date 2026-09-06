<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected static function table(): string
    {
        return 'users';
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function findByStudentNumber(string $studentNumber): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM users WHERE student_number = :student_number LIMIT 1");
        $stmt->execute(['student_number' => $studentNumber]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function getStudents(): array
    {
        $stmt = self::db()->query("SELECT * FROM users WHERE role = 'Student' ORDER BY last_name, first_name");
        return $stmt->fetchAll();
    }

    public static function getFaculty(): array
    {
        $stmt = self::db()->query("SELECT * FROM users WHERE role = 'Faculty' ORDER BY last_name, first_name");
        return $stmt->fetchAll();
    }
}
