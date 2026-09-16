<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class AttendanceRecord extends Model
{
    protected $table = 'attendance_records';

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }

    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    /**
     * Get attendance map for a specific subject, term, and date (key = student_id).
     */
    public static function getBySubjectAndDate(int $subjectId, int $termId, string $date): array
    {
        $stmt = self::db()->prepare("
            SELECT ar.*, st.user_id, sd.first_name, sd.last_name, sd.student_number
            FROM attendance_records ar
            JOIN students st ON ar.student_id = st.id
            JOIN users u ON st.user_id = u.id
            LEFT JOIN student_details sd ON sd.user_id = u.id
            WHERE ar.subject_id = :subject_id
              AND ar.academic_term_id = :term_id
              AND ar.attendance_date = :date
        ");
        $stmt->execute([
            'subject_id' => $subjectId,
            'term_id' => $termId,
            'date' => $date,
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['student_id']] = $row;
        }
        return $map;
    }

    /**
     * Get historical attendance logs for a subject.
     */
    public static function getSubjectAttendanceLogs(int $subjectId, int $termId, int $limit = 15): array
    {
        $stmt = self::db()->prepare("
            SELECT attendance_date,
                   COUNT(*) as total_students,
                   SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_count,
                   SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
                   SUM(CASE WHEN status = 'Excused' THEN 1 ELSE 0 END) as excused_count,
                   SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late_count
            FROM attendance_records
            WHERE subject_id = :subject_id
              AND academic_term_id = :term_id
            GROUP BY attendance_date
            ORDER BY attendance_date DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':subject_id', $subjectId, PDO::PARAM_INT);
        $stmt->bindValue(':term_id', $termId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get summary of absences and attendance record for a student in a subject.
     */
    public static function getStudentAbsenceSummary(int $studentId, int $subjectId, int $termId): array
    {
        $stmt = self::db()->prepare("
            SELECT attendance_date, status, remarks
            FROM attendance_records
            WHERE student_id = :student_id
              AND subject_id = :subject_id
              AND academic_term_id = :term_id
            ORDER BY attendance_date ASC
        ");
        $stmt->execute([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'term_id' => $termId,
        ]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $present = 0;
        $absent = 0;
        $excused = 0;
        $late = 0;
        $history = [];

        foreach ($records as $r) {
            $st = $r['status'];
            if ($st === 'Present') $present++;
            elseif ($st === 'Absent') {
                $absent++;
                $history[] = ['date' => $r['attendance_date'], 'status' => 'Absent'];
            } elseif ($st === 'Excused') {
                $excused++;
                $history[] = ['date' => $r['attendance_date'], 'status' => 'Excused'];
            } elseif ($st === 'Late') {
                $late++;
            }
        }

        $totalSessions = count($records);
        $totalAbsenceThreshold = $absent + $excused;
        $pct = $totalSessions > 0 ? round((($present + ($late * 0.5)) / $totalSessions) * 100, 1) : 100.0;

        return [
            'present_count' => $present,
            'absent_count' => $absent,
            'excused_count' => $excused,
            'late_count' => $late,
            'total_sessions' => $totalSessions,
            'attendance_percentage' => $pct,
            'absence_history' => $history,
            'total_absences' => $totalAbsenceThreshold,
            'absences' => $totalAbsenceThreshold,
            'has_warning' => $totalAbsenceThreshold >= 5, // 5+ absence threshold
            'warning_flag' => $totalAbsenceThreshold >= 5,
        ];
    }

    /**
     * Get student overall attendance stats across all subjects for a term.
     */
    public static function getStudentOverallSummary(int $studentId, int $termId): array
    {
        $stmt = self::db()->prepare("
            SELECT status, COUNT(*) as cnt
            FROM attendance_records
            WHERE student_id = :student_id
              AND academic_term_id = :term_id
            GROUP BY status
        ");
        $stmt->execute([
            'student_id' => $studentId,
            'term_id' => $termId,
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $present = 0;
        $absent = 0;
        $excused = 0;
        $late = 0;

        foreach ($rows as $row) {
            $st = $row['status'];
            $c = (int) $row['cnt'];
            if ($st === 'Present') $present = $c;
            elseif ($st === 'Absent') $absent = $c;
            elseif ($st === 'Excused') $excused = $c;
            elseif ($st === 'Late') $late = $c;
        }

        $total = $present + $absent + $excused + $late;
        $pct = $total > 0 ? round((($present + ($late * 0.5)) / $total) * 100, 1) : 100.0;

        return [
            'present_count' => $present,
            'absent_count' => $absent,
            'excused_count' => $excused,
            'late_count' => $late,
            'total_records' => $total,
            'total_absences' => ($absent + $excused),
            'absences' => ($absent + $excused),
            'attendance_percentage' => $pct,
            'has_warning' => ($absent + $excused) >= 5,
            'warning_flag' => ($absent + $excused) >= 5,
        ];
    }
}
