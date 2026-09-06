<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Grade extends Model
{
    protected $table = 'grades';

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function gradingPeriod()
    {
        return $this->belongsTo(GradingPeriod::class, 'grading_period_id');
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }

    public static function getByStudent(int $studentId, int $academicTermId = 0): array
    {
        if ($academicTermId > 0) {
            $stmt = self::db()->prepare("
                SELECT g.*, s.code as subject_code, s.name as subject_name, gp.name as period_name
                FROM grades g
                JOIN subjects s ON g.subject_id = s.id
                JOIN grading_periods gp ON g.grading_period_id = gp.id
                WHERE g.student_id = :student_id AND g.academic_term_id = :academic_term_id
                ORDER BY gp.order_num, s.code
            ");
            $stmt->execute(['student_id' => $studentId, 'academic_term_id' => $academicTermId]);
        } else {
            $stmt = self::db()->prepare("
                SELECT g.*, s.code as subject_code, s.name as subject_name, gp.name as period_name
                FROM grades g
                JOIN subjects s ON g.subject_id = s.id
                JOIN grading_periods gp ON g.grading_period_id = gp.id
                WHERE g.student_id = :student_id
                ORDER BY g.academic_term_id, gp.order_num, s.code
            ");
            $stmt->execute(['student_id' => $studentId]);
        }
        return $stmt->fetchAll();
    }

    public static function getBySubjectAndPeriod(int $subjectId, int $gradingPeriodId, int $academicTermId): array
    {
        $stmt = self::db()->prepare("
            SELECT g.*, u.first_name, u.last_name, u.student_number
            FROM grades g
            JOIN students st ON g.student_id = st.id
            JOIN users u ON st.user_id = u.id
            WHERE g.subject_id = :subject_id 
              AND g.grading_period_id = :grading_period_id
              AND g.academic_term_id = :academic_term_id
            ORDER BY u.last_name, u.first_name
        ");
        $stmt->execute([
            'subject_id' => $subjectId,
            'grading_period_id' => $gradingPeriodId,
            'academic_term_id' => $academicTermId,
        ]);
        return $stmt->fetchAll();
    }

    public static function saveGrade(int $studentId, int $subjectId, int $gradingPeriodId, int $academicTermId, float $grade): int
    {
        $existing = self::db()->prepare("
            SELECT id FROM grades
            WHERE student_id = :student_id AND subject_id = :subject_id 
              AND grading_period_id = :grading_period_id AND academic_term_id = :academic_term_id
        ");
        $existing->execute([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'grading_period_id' => $gradingPeriodId,
            'academic_term_id' => $academicTermId,
        ]);
        $row = $existing->fetch();

        if ($row) {
            self::where('id', (int)$row['id'])->update(['grade' => $grade, 'updated_at' => date('Y-m-d H:i:s')]);
            return (int)$row['id'];
        }

        $newGrade = self::create([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'grading_period_id' => $gradingPeriodId,
            'academic_term_id' => $academicTermId,
            'grade' => $grade,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $newGrade->id;
    }
}
