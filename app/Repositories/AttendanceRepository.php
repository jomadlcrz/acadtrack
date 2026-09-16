<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class AttendanceRepository
{
    /**
     * Bulk save or update attendance for a subject roster on a given date.
     *
     * @param int $termId
     * @param int $subjectId
     * @param int $facultyId
     * @param string $date (YYYY-MM-DD)
     * @param array $entries [ ['student_id' => 1, 'status' => 'Present', 'remarks' => ''], ... ]
     * @return int Number of affected/saved rows
     */
    public function bulkSave(int $termId, int $subjectId, int $facultyId, string $date, array $entries): int
    {
        if (empty($entries)) {
            return 0;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO attendance_records (
                academic_term_id, subject_id, student_id, faculty_id,
                attendance_date, status, remarks, created_at, updated_at
            ) VALUES (
                :academic_term_id, :subject_id, :student_id, :faculty_id,
                :attendance_date, :status, :remarks, NOW(), NOW()
            )
            ON DUPLICATE KEY UPDATE
                status = VALUES(status),
                remarks = VALUES(remarks),
                faculty_id = VALUES(faculty_id),
                updated_at = NOW()
        ");

        $saved = 0;
        foreach ($entries as $entry) {
            $studentId = (int) ($entry['student_id'] ?? 0);
            if ($studentId <= 0) continue;

            $status = in_array($entry['status'] ?? '', ['Present', 'Absent', 'Excused', 'Late'], true)
                ? $entry['status']
                : 'Present';

            $remarks = !empty($entry['remarks']) ? trim((string) $entry['remarks']) : null;

            $stmt->execute([
                'academic_term_id' => $termId,
                'subject_id' => $subjectId,
                'student_id' => $studentId,
                'faculty_id' => $facultyId,
                'attendance_date' => $date,
                'status' => $status,
                'remarks' => $remarks,
            ]);
            $saved++;
        }

        return $saved;
    }
}
