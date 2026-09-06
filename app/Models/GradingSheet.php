<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class GradingSheet extends Model
{
    protected static function table(): string
    {
        return 'grading_sheets';
    }

    const STATUS_DRAFT = 'DRAFT';
    const STATUS_SUBMITTED = 'SUBMITTED';
    const STATUS_UNDER_REVIEW = 'UNDER_REVIEW';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_FINALIZED = 'FINALIZED';
    const STATUS_RETURNED = 'RETURNED';

    public static function getByFaculty(int $facultyId, int $academicTermId): array
    {
        $stmt = self::db()->prepare("
            SELECT gs.*, s.code as subject_code, s.name as subject_name, gp.name as period_name
            FROM grading_sheets gs
            JOIN subjects s ON gs.subject_id = s.id
            JOIN grading_periods gp ON gs.grading_period_id = gp.id
            WHERE gs.faculty_id = :faculty_id AND gs.academic_term_id = :academic_term_id
            ORDER BY gs.updated_at DESC
        ");
        $stmt->execute(['faculty_id' => $facultyId, 'academic_term_id' => $academicTermId]);
        return $stmt->fetchAll();
    }

    public static function getPendingReview(int $academicTermId): array
    {
        $stmt = self::db()->prepare("
            SELECT gs.*, s.code as subject_code, s.name as subject_name, 
                   gp.name as period_name, u.first_name, u.last_name
            FROM grading_sheets gs
            JOIN subjects s ON gs.subject_id = s.id
            JOIN grading_periods gp ON gs.grading_period_id = gp.id
            JOIN users u ON gs.faculty_id = u.id
            WHERE gs.academic_term_id = :academic_term_id 
              AND gs.status IN ('SUBMITTED', 'UNDER_REVIEW')
            ORDER BY gs.submitted_at DESC
        ");
        $stmt->execute(['academic_term_id' => $academicTermId]);
        return $stmt->fetchAll();
    }

    public static function findByComposite(int $facultyId, int $subjectId, int $gradingPeriodId, int $academicTermId): ?array
    {
        $stmt = self::db()->prepare("
            SELECT * FROM grading_sheets
            WHERE faculty_id = :faculty_id AND subject_id = :subject_id
              AND grading_period_id = :grading_period_id AND academic_term_id = :academic_term_id
            LIMIT 1
        ");
        $stmt->execute([
            'faculty_id' => $facultyId,
            'subject_id' => $subjectId,
            'grading_period_id' => $gradingPeriodId,
            'academic_term_id' => $academicTermId,
        ]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function updateStatus(int $id, string $status): bool
    {
        return self::update($id, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
