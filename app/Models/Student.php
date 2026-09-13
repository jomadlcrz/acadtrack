<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Student extends Model
{
    protected $table = 'students';

    protected $fillable = [
        'user_id',
        'set_id',
        'year_level',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function set()
    {
        return $this->belongsTo(Set::class, 'set_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class, 'student_id');
    }

    public static function findByUserId(int $userId): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM students WHERE user_id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function getBySet(int $setId): array
    {
        $stmt = self::db()->prepare("
            SELECT s.*, sd.first_name, sd.last_name, u.email, sd.student_number
            FROM students s
            JOIN users u ON s.user_id = u.id
            LEFT JOIN student_details sd ON sd.user_id = u.id
            WHERE s.set_id = :set_id
            ORDER BY sd.last_name, sd.first_name
        ");
        $stmt->execute(['set_id' => $setId]);
        return $stmt->fetchAll();
    }

    public static function getBySubject(int $subjectId, int $academicTermId, ?int $setId = null): array
    {
        $setClause = $setId ? "AND s.set_id = :set_id" : "";
        $stmt = self::db()->prepare("
            SELECT s.*, sd.first_name, sd.last_name, u.email, sd.student_number,
                   sec.set_name AS set_name
            FROM students s
            JOIN users u ON s.user_id = u.id
            LEFT JOIN student_details sd ON sd.user_id = u.id
            JOIN enrollments e ON e.student_id = s.id
            LEFT JOIN sets sec ON sec.id = s.set_id
            WHERE e.subject_id = :subject_id AND e.academic_term_id = :academic_term_id
            {$setClause}
            ORDER BY sd.last_name, sd.first_name
        ");
        $params = ['subject_id' => $subjectId, 'academic_term_id' => $academicTermId];
        if ($setId) {
            $params['set_id'] = $setId;
        }
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function enroll(int $studentId, int $subjectId, int $academicTermId): bool
    {
        $stmt = self::db()->prepare("
            INSERT IGNORE INTO enrollments (student_id, subject_id, academic_term_id, enrolled_at)
            VALUES (:student_id, :subject_id, :academic_term_id, NOW())
        ");
        return $stmt->execute([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'academic_term_id' => $academicTermId,
        ]);
    }

    public static function getAllAvailable(): array
    {
        $stmt = self::db()->query("
            SELECT s.*, sd.first_name, sd.last_name, u.email, sd.student_number,
                   sec.set_name AS set_name
            FROM students s
            JOIN users u ON s.user_id = u.id
            LEFT JOIN student_details sd ON sd.user_id = u.id
            JOIN user_roles ur ON ur.user_id = u.id
            LEFT JOIN sets sec ON sec.id = s.set_id
            WHERE ur.role_id = 4
            ORDER BY sd.last_name, sd.first_name
        ");
        return $stmt->fetchAll();
    }
}
