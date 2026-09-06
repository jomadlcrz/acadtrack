<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Faculty extends Model
{
    protected $table = 'faculty';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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
            SELECT s.id as id, s.id as subject_id, s.code, s.name, s.nature, s.year_level, s.semester,
                   fs.faculty_id, fs.academic_term_id, fs.assigned_at,
                   COALESCE(gs.grading_method, 'zero_based') as grading_method,
                   COALESCE(gs.prelim_weight, 20.00) as prelim_weight,
                   COALESCE(gs.midterm_weight, 20.00) as midterm_weight,
                   COALESCE(gs.semi_final_weight, 20.00) as semi_final_weight,
                   COALESCE(gs.final_weight, 40.00) as final_weight
            FROM faculty_subjects fs
            JOIN subjects s ON fs.subject_id = s.id
            LEFT JOIN grading_settings gs ON gs.subject_id = s.id AND gs.academic_term_id = fs.academic_term_id
            WHERE fs.faculty_id = :faculty_id AND fs.academic_term_id = :academic_term_id
            ORDER BY s.code
        ");
        $stmt->execute(['faculty_id' => $facultyId, 'academic_term_id' => $academicTermId]);
        return $stmt->fetchAll();
    }
}
