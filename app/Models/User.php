<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected $table = 'users';

    protected $casts = [
        'force_password_change' => 'boolean',
    ];

    public function student()
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    public function faculty()
    {
        return $this->hasOne(Faculty::class, 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /**
     * Generate a cryptographically secure, random temporary password.
     */
    public static function generateRandomPassword(int $length = 10): string
    {
        $lower = 'abcdefghjkmnpqrstuvwxyz';
        $upper = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        $digits = '23456789';
        $specials = '@#$%&*';

        $password = [
            $lower[random_int(0, strlen($lower) - 1)],
            $upper[random_int(0, strlen($upper) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
            $specials[random_int(0, strlen($specials) - 1)],
        ];

        $all = $lower . $upper . $digits . $specials;
        for ($i = count($password); $i < $length; $i++) {
            $password[] = $all[random_int(0, strlen($all) - 1)];
        }

        shuffle($password);
        return implode('', $password);
    }

    public static function findByEmail(string $email): ?array
    {
        $user = self::where('email', $email)->first();
        return $user ? $user->toArray() : null;
    }

    public static function findByStudentNumber(?string $studentNumber): ?array
    {
        if (empty($studentNumber)) {
            return null;
        }
        $user = self::where('student_number', $studentNumber)->first();
        return $user ? $user->toArray() : null;
    }

    public static function getStudents(): array
    {
        return self::where('role', 'Student')->orderBy('last_name')->orderBy('first_name')->get()->toArray();
    }

    public static function getFaculty(): array
    {
        return self::where('role', 'Faculty')->orderBy('last_name')->orderBy('first_name')->get()->toArray();
    }
}
