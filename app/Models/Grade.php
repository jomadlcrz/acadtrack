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

    /**
     * A student only sees a mark once its grading sheet is FINALIZED (official and locked).
     * Marks that never went through a grading sheet stay visible. EXISTS (not JOIN) so that two
     * instructors on one subject can never duplicate a row.
     */
    private const PUBLISHED_SQL = "(
        EXISTS (SELECT 1 FROM grading_sheets gsp WHERE gsp.subject_id = g.subject_id AND gsp.grading_period_id = g.grading_period_id AND gsp.academic_term_id = g.academic_term_id AND gsp.status = 'FINALIZED')
        OR NOT EXISTS (SELECT 1 FROM grading_sheets gsn WHERE gsn.subject_id = g.subject_id AND gsn.grading_period_id = g.grading_period_id AND gsn.academic_term_id = g.academic_term_id)
    )";

    private const SHEET_STATUS_SQL = "(SELECT gss.status FROM grading_sheets gss WHERE gss.subject_id = g.subject_id AND gss.grading_period_id = g.grading_period_id AND gss.academic_term_id = g.academic_term_id ORDER BY (gss.status = 'FINALIZED') DESC, gss.id DESC LIMIT 1)";

    public static function getByStudent(int $studentId, int $academicTermId = 0): array
    {
        if ($academicTermId > 0) {
            $stmt = self::db()->prepare("
                SELECT g.*, 
                       s.subject_code, s.subject_code as code, s.subject_code as subject_code, 
                       s.descriptive_title, s.descriptive_title as name, s.descriptive_title as subject_name, 
                       gp.name as period_name, " . self::SHEET_STATUS_SQL . " as sheet_status
                FROM grades g
                JOIN subjects s ON g.subject_id = s.id
                JOIN grading_periods gp ON g.grading_period_id = gp.id
                WHERE g.student_id = :student_id 
                  AND g.academic_term_id = :academic_term_id
                  AND " . self::PUBLISHED_SQL . "
                ORDER BY gp.order_num, s.subject_code
            ");
            $stmt->execute(['student_id' => $studentId, 'academic_term_id' => $academicTermId]);
        } else {
            $stmt = self::db()->prepare("
                SELECT g.*, 
                       s.subject_code, s.subject_code as code, s.subject_code as subject_code, 
                       s.descriptive_title, s.descriptive_title as name, s.descriptive_title as subject_name, 
                       gp.name as period_name, " . self::SHEET_STATUS_SQL . " as sheet_status
                FROM grades g
                JOIN subjects s ON g.subject_id = s.id
                JOIN grading_periods gp ON g.grading_period_id = gp.id
                WHERE g.student_id = :student_id
                  AND " . self::PUBLISHED_SQL . "
                ORDER BY g.academic_term_id, gp.order_num, s.subject_code
            ");
            $stmt->execute(['student_id' => $studentId]);
        }
        return $stmt->fetchAll();
    }

    public static function getBySubjectAndPeriod(int $subjectId, int $gradingPeriodId, int $academicTermId): array
    {
        $stmt = self::db()->prepare("
            SELECT g.*, sd.first_name, sd.last_name, sd.student_number
            FROM grades g
            JOIN students st ON g.student_id = st.id
            JOIN users u ON st.user_id = u.id
            LEFT JOIN student_details sd ON sd.user_id = u.id
            WHERE g.subject_id = :subject_id 
              AND g.grading_period_id = :grading_period_id
              AND g.academic_term_id = :academic_term_id
            ORDER BY sd.last_name, sd.first_name
        ");
        $stmt->execute([
            'subject_id' => $subjectId,
            'grading_period_id' => $gradingPeriodId,
            'academic_term_id' => $academicTermId,
        ]);
        return $stmt->fetchAll();
    }

    public static function saveGrade(int $studentId, int $subjectId, int $gradingPeriodId, int $academicTermId, float $grade, ?int $changedBy = null, ?string $reason = null): int
    {
        $existing = self::db()->prepare("
            SELECT id, grade FROM grades
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
            $oldGrade = isset($row['grade']) ? (float)$row['grade'] : null;
            self::where('id', (int)$row['id'])->update(['grade' => $grade, 'updated_at' => date('Y-m-d H:i:s')]);

            // Ensure audit trail is recorded in grade_history_log (application-level fallback for hosts without TRIGGER privilege)
            if ($oldGrade !== null && abs($oldGrade - $grade) > 0.0001) {
                try {
                    $recentLog = GradeHistoryLog::where('grade_id', (int)$row['id'])
                        ->where('action_performed', 'UPDATE')
                        ->orderBy('log_id', 'desc')
                        ->first();
                    if (!$recentLog || abs((float)$recentLog->new_score - $grade) > 0.0001) {
                        GradeHistoryLog::create([
                            'grade_id' => (int)$row['id'],
                            'student_id' => $studentId,
                            'old_score' => $oldGrade,
                            'new_score' => $grade,
                            'action_performed' => 'UPDATE',
                            'changed_by' => $changedBy,
                            'reason' => $reason !== null ? mb_substr($reason, 0, 255) : null,
                            'changed_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                } catch (\Throwable $e) {
                    // Fail gracefully if audit log table is not available
                }

                // The history row may have been written by the database trigger, which cannot know who acted.
                // Attribute the newest matching row to the person (and reason) behind this change.
                if ($changedBy !== null) {
                    try {
                        $latest = GradeHistoryLog::where('grade_id', (int)$row['id'])
                            ->whereNull('changed_by')
                            ->orderBy('log_id', 'desc')
                            ->first();
                        if ($latest && abs((float)$latest->new_score - $grade) <= 0.0001) {
                            GradeHistoryLog::where('log_id', (int)$latest->log_id)->update([
                                'changed_by' => $changedBy,
                                'reason' => $reason !== null ? mb_substr($reason, 0, 255) : null,
                            ]);
                        }
                    } catch (\Throwable $e) {
                        // Attribution is best effort; the mark itself is already saved
                    }
                }
            }

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
