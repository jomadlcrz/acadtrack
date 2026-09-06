<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Grade;
use App\Models\GradingSheet;

class GradeRepository
{
    public function findById(int $id): ?array
    {
        $grade = Grade::find($id);
        return $grade ? $grade->toArray() : null;
    }

    public function getByStudent(int $studentId, int $academicTermId): array
    {
        return Grade::getByStudent($studentId, $academicTermId);
    }

    public function getBySubjectAndPeriod(int $subjectId, int $gradingPeriodId, int $academicTermId): array
    {
        return Grade::getBySubjectAndPeriod($subjectId, $gradingPeriodId, $academicTermId);
    }

    public function saveGrade(int $studentId, int $subjectId, int $gradingPeriodId, int $academicTermId, float $grade): int
    {
        return Grade::saveGrade($studentId, $subjectId, $gradingPeriodId, $academicTermId, $grade);
    }

    public function bulkSave(array $grades): void
    {
        $pdo = \App\Core\Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO grades (student_id, subject_id, grading_period_id, academic_term_id, grade, created_at)
            VALUES (:student_id, :subject_id, :grading_period_id, :academic_term_id, :grade, :created_at)
            ON DUPLICATE KEY UPDATE grade = VALUES(grade), updated_at = VALUES(updated_at)
        ");

        foreach ($grades as $grade) {
            $stmt->execute([
                'student_id' => $grade['student_id'],
                'subject_id' => $grade['subject_id'],
                'grading_period_id' => $grade['grading_period_id'],
                'academic_term_id' => $grade['academic_term_id'],
                'grade' => $grade['grade'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
