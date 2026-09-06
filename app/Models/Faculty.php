<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Faculty extends Model
{
    protected static function table(): string
    {
        return 'faculty';
    }

    public static function findByUserId(int $userId): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM faculty WHERE user_id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function getAssignedSubjects(int $facultyId, int $academicTermId): array
    {
        $stmt = self::db()->prepare("
            SELECT fs.*, s.code, s.name, s.year_level, s.semester
            FROM faculty_subjects fs
            JOIN subjects s ON fs.subject_id = s.id
            WHERE fs.faculty_id = :faculty_id AND fs.academic_term_id = :academic_term_id
            ORDER BY s.code
        ");
        $stmt->execute(['faculty_id' => $facultyId, 'academic_term_id' => $academicTermId]);
        return $stmt->fetchAll();
    }
}
