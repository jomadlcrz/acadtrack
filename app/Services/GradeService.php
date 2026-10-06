<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\GradeRepository;

class GradeService
{
    private GradeRepository $gradeRepository;

    public function __construct()
    {
        $this->gradeRepository = new GradeRepository();
    }

    public function getStudentGrades(int $studentId, int $academicTermId): array
    {
        return $this->gradeRepository->getByStudent($studentId, $academicTermId);
    }

    public function getGradeSummary(int $studentId, int $academicTermId): array
    {
        $grades = $this->getStudentGrades($studentId, $academicTermId);
        $summary = [];

        // 1. Resolve student set assignment if student exists
        $studentSetId = null;
        if ($studentId > 0) {
            $student = \App\Models\Student::find($studentId);
            if ($student && !empty($student->set_id)) {
                $studentSetId = (int) $student->set_id;
            }
        }

        // 2. Query official course enrollments for this student & term
        if ($studentId > 0) {
            $pdo = \App\Core\Database::getConnection();
            $termSql = $academicTermId > 0 ? "AND e.academic_term_id = :term_id" : "";
            $stmt = $pdo->prepare("
                SELECT DISTINCT s.id as subject_id, s.subject_code, s.descriptive_title, s.units, s.nature, s.semester
                FROM enrollments e
                JOIN subjects s ON e.subject_id = s.id
                WHERE e.student_id = :student_id
                {$termSql}
                ORDER BY s.subject_code ASC
            ");
            $params = ['student_id' => $studentId];
            if ($academicTermId > 0) {
                $params['term_id'] = $academicTermId;
            }
            $stmt->execute($params);
            $enrolled = $stmt->fetchAll() ?: [];

            foreach ($enrolled as $sub) {
                $code = (string) $sub['subject_code'];
                $subId = (int) $sub['subject_id'];
                $summary[$code] = [
                    'subject_id' => $subId,
                    'subject_code' => $code,
                    'subject_name' => (string) ($sub['descriptive_title'] ?? $code),
                    'units' => (int) ($sub['units'] ?? 3),
                    'nature' => (string) ($sub['nature'] ?? 'Lecture'),
                    'instructor' => $this->lookupSubjectInstructor($subId, $academicTermId, $studentSetId),
                    'periods' => [],
                    'sheet_status' => null,
                ];
            }
        }

        // 3. Merge published grades
        foreach ($grades as $grade) {
            $subjectCode = (string) $grade['subject_code'];
            if (!isset($summary[$subjectCode])) {
                $subjectId = (int) ($grade['subject_id'] ?? 0);
                $summary[$subjectCode] = [
                    'subject_id' => $subjectId,
                    'subject_code' => $subjectCode,
                    'subject_name' => (string) ($grade['subject_name'] ?? $grade['descriptive_title'] ?? $subjectCode),
                    'units' => 3,
                    'nature' => 'Lecture',
                    'instructor' => $this->lookupSubjectInstructor($subjectId, $academicTermId, $studentSetId),
                    'periods' => [],
                    'sheet_status' => $grade['sheet_status'] ?? null,
                ];
            }
            $summary[$subjectCode]['periods'][$grade['period_name']] = $grade['grade'];
            if (!empty($grade['sheet_status'])) {
                $summary[$subjectCode]['sheet_status'] = $grade['sheet_status'];
            }
        }

        // 4. Compute status and average per class
        foreach ($summary as $code => &$item) {
            $scores = array_filter($item['periods'], fn($v) => is_numeric($v));
            if (!empty($scores)) {
                $avg = array_sum($scores) / count($scores);
                $item['computed_average'] = round($avg, 2);
                $item['status'] = $avg >= 75.0 ? 'Passed' : 'Failed';
                $item['has_published_grades'] = true;
            } else {
                $item['computed_average'] = null;
                $item['status'] = 'In progress';
                $item['has_published_grades'] = false;
            }
        }
        unset($item);

        return $summary;
    }

    public function lookupSubjectInstructor(int $subjectId, int $academicTermId = 0, ?int $setId = null): string
    {
        if ($subjectId <= 0) {
            return 'TBA';
        }

        $pdo = \App\Core\Database::getConnection();
        $query = "
            SELECT fd.first_name, fd.last_name, u.email
            FROM faculty_subjects fs
            JOIN users u ON fs.faculty_id = u.id
            LEFT JOIN faculty_details fd ON u.id = fd.user_id
            WHERE fs.subject_id = :subject_id
        ";
        $params = ['subject_id' => $subjectId];

        if ($academicTermId > 0) {
            $query .= " AND fs.academic_term_id = :term_id";
            $params['term_id'] = $academicTermId;
        }

        if ($setId !== null && $setId > 0) {
            $query .= " AND (fs.set_id = :set_id OR fs.set_id IS NULL) ORDER BY (fs.set_id IS NOT NULL) DESC, fs.id DESC";
            $params['set_id'] = $setId;
        } else {
            $query .= " ORDER BY fs.id DESC";
        }
        $query .= " LIMIT 1";

        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $row = $stmt->fetch();
            if ($row) {
                $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                return !empty($name) ? $name : (string) ($row['email'] ?? 'Assigned Instructor');
            }
        } catch (\Throwable $e) {
            // Graceful fallback
        }

        return 'TBA';
    }
}
