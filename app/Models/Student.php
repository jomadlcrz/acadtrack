<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Student extends Model
{
    protected static function table(): string
    {
        return 'students';
    }

    public static function findByUserId(int $userId): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM students WHERE user_id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function getBySection(int $sectionId): array
    {
        $stmt = self::db()->prepare("
            SELECT s.*, u.first_name, u.last_name, u.email, u.student_number
            FROM students s
            JOIN users u ON s.user_id = u.id
            WHERE s.section_id = :section_id
            ORDER BY u.last_name, u.first_name
        ");
        $stmt->execute(['section_id' => $sectionId]);
        return $stmt->fetchAll();
    }

    public static function getBySubject(int $subjectId, int $academicTermId): array
    {
        $stmt = self::db()->prepare("
            SELECT s.*, u.first_name, u.last_name, u.email, u.student_number
            FROM students s
            JOIN users u ON s.user_id = u.id
            JOIN enrollments e ON e.student_id = s.id
            WHERE e.subject_id = :subject_id AND e.academic_term_id = :academic_term_id
            ORDER BY u.last_name, u.first_name
        ");
        $stmt->execute(['subject_id' => $subjectId, 'academic_term_id' => $academicTermId]);
        return $stmt->fetchAll();
    }
}
