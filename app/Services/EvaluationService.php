<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\AcademicTerm;
use App\Models\AttendanceRecord;
use App\Models\GradingPeriod;
use App\Models\Subject;
use PDO;

class EvaluationService
{
    public function calculateEvaluation(array $grades, array $gradingPeriodWeights = []): array
    {
        if (empty($grades)) {
            return ['average' => 0, 'status' => 'No grades'];
        }

        $totalWeight = 0;
        $weightedSum = 0;

        foreach ($grades as $period => $grade) {
            $weight = $gradingPeriodWeights[$period] ?? 1;
            $weightedSum += $grade * $weight;
            $totalWeight += $weight;
        }

        $average = $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : 0;

        return [
            'average' => $average,
            'status' => $this->getStatus($average),
            'remarks' => $this->getRemarks($average),
        ];
    }

    public function getStatus(float $grade): string
    {
        return match (true) {
            $grade >= 90 => 'Excellent',
            $grade >= 80 => 'Very Good',
            $grade >= 70 => 'Good',
            $grade >= 60 => 'Satisfactory',
            $grade >= 50 => 'Needs Improvement',
            default => 'Failing',
        };
    }

    public function getRemarks(float $grade): string
    {
        return match (true) {
            $grade >= 90 => 'Outstanding performance',
            $grade >= 80 => 'Commendable performance',
            $grade >= 70 => 'Good performance',
            $grade >= 60 => 'Acceptable performance',
            $grade >= 50 => 'Below expectations',
            default => 'Unsatisfactory performance',
        };
    }

    /**
     * Build the complete Digital Student Pass data package.
     *
     * @param int $studentId
     * @param int $subjectId
     * @param int $termId
     * @return array|null
     */
    public function getDigitalPass(int $studentId, int $subjectId, int $termId): ?array
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT st.id as student_id, st.user_id, st.year_level, st.status as student_status,
                   sd.first_name, sd.last_name, sd.student_number,
                   p.program_name, p.program_abbrev,
                   COALESCE(s.set_name, 'Unassigned') as set_name
            FROM students st
            JOIN users u ON st.user_id = u.id
            LEFT JOIN student_details sd ON sd.user_id = u.id
            LEFT JOIN programs p ON sd.program_id = p.id
            LEFT JOIN sets s ON COALESCE(st.set_id, sd.set_id) = s.id
            WHERE st.id = :student_id
            LIMIT 1
        ");
        $stmt->execute(['student_id' => $studentId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            return null;
        }

        $subject = Subject::find($subjectId);
        $term = AcademicTerm::find($termId);

        // Fetch grading periods
        $periods = GradingPeriod::where('academic_term_id', $termId)->orderBy('order_num')->get()->toArray();
        if (empty($periods)) {
            $periods = GradingPeriod::getActive();
        }

        // Fetch grades
        $gradeStmt = $pdo->prepare("
            SELECT gp.id as period_id, gp.name as period_name, gp.order_num, gp.weight, g.grade
            FROM grades g
            JOIN grading_periods gp ON g.grading_period_id = gp.id
            WHERE g.student_id = :student_id 
              AND g.subject_id = :subject_id 
              AND g.academic_term_id = :term_id
        ");
        $gradeStmt->execute([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'term_id' => $termId,
        ]);
        $gradeRows = $gradeStmt->fetchAll(PDO::FETCH_ASSOC);

        $gradeMap = [];
        foreach ($gradeRows as $gr) {
            $gradeMap[(int)$gr['period_id']] = (float)$gr['grade'];
        }

        $totalWeight = 0;
        $weightedSum = 0;
        $hasAllPeriods = true;
        $periodBreakdown = [];
        $qrGrades = [];

        foreach ($periods as $p) {
            $pId = (int)$p['id'];
            $pName = $p['name'];
            $pWeight = (float)($p['weight'] ?? 25.0);
            $totalWeight += $pWeight;

            if (isset($gradeMap[$pId])) {
                $sc = $gradeMap[$pId];
                $periodBreakdown[$pName] = $sc;
                $qrGrades[strtolower(str_replace([' ', '-'], '', $pName))] = number_format($sc, 2);
                $weightedSum += $sc * ($pWeight / 100);
            } else {
                $hasAllPeriods = false;
                $periodBreakdown[$pName] = null;
                $qrGrades[strtolower(str_replace([' ', '-'], '', $pName))] = 'N/A';
            }
        }

        $finalGrade = $hasAllPeriods ? round($weightedSum, 2) : null;
        $statusRemark = $finalGrade !== null
            ? ($finalGrade >= 75.0 ? 'PASS' : 'FAIL')
            : 'INCOMPLETE';

        // Attendance summary
        $attendanceSummary = AttendanceRecord::getStudentAbsenceSummary($studentId, $subjectId, $termId);

        $studentNumber = $student['student_number'] ?: ('STU-' . str_pad((string)$studentId, 4, '0', STR_PAD_LEFT));
        $fullName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));

        $subjectCode = $subject ? $subject->subject_code : 'SUBJ';
        $subjectTitle = $subject ? $subject->descriptive_title : 'Course Subject';
        $setName = $student['set_name'] ?: 'Set A';
        $yearLevelStr = match((int)($student['year_level'] ?? 1)) {
            1 => 'First Year',
            2 => 'Second Year',
            3 => 'Third Year',
            4 => 'Fourth Year',
            default => $student['year_level'] . 'th Year',
        };

        // Verification URL
        $verifyUrl = (function_exists('url') ? url('/verify/student-pass') : '/verify/student-pass') .
                     '?id=' . urlencode($studentNumber) .
                     '&sub=' . $subjectId .
                     '&term=' . $termId;

        $qrPayload = [
            'id' => $studentNumber,
            'name' => $fullName,
            'course' => $subjectCode,
            'section' => $setName,
            'year' => $yearLevelStr,
            'status' => $student['student_status'] ?? 'Regular',
            'grades' => $qrGrades,
            'final_grade' => $finalGrade !== null ? number_format($finalGrade, 2) : 'INCOMPLETE',
            'remarks' => $statusRemark,
            'absences' => $attendanceSummary['total_absences'] ?? 0,
            'has_warning' => $attendanceSummary['has_warning'] ?? false,
            'issued' => date('Y-m-d H:i:s'),
            'verification_url' => $verifyUrl,
        ];

        $studentData = [
            'id' => $studentId,
            'student_id' => $studentId,
            'student_number' => $studentNumber,
            'name' => $fullName,
            'full_name' => $fullName,
            'status' => $student['student_status'] ?? 'Regular',
            'year_level' => $yearLevelStr,
            'program_name' => $student['program_name'] ?? 'College Program',
            'set_name' => $setName,
        ];

        $summaryData = [
            'final_grade' => $finalGrade,
            'status_remark' => $statusRemark,
            'remarks' => $statusRemark,
            'has_all_periods' => $hasAllPeriods,
        ];

        $verificationData = [
            'verification_code' => $studentNumber,
            'verification_url' => $verifyUrl,
            'qr_data' => json_encode($qrPayload),
            'payload' => $qrPayload,
        ];

        return [
            'student_id' => $studentId,
            'student_number' => $studentNumber,
            'name' => $fullName,
            'status' => $student['student_status'] ?? 'Regular',
            'year_level' => $yearLevelStr,
            'program_name' => $student['program_name'] ?? 'College Program',
            'set_name' => $setName,
            'subject_id' => $subjectId,
            'subject_code' => $subjectCode,
            'subject_title' => $subjectTitle,
            'academic_term_id' => $termId,
            'semester' => (int)($term ? $term->semester : 1),
            'periods' => $periods,
            'period_scores' => $periodBreakdown,
            'grades' => $periodBreakdown,
            'final_grade' => $finalGrade,
            'status_remark' => $statusRemark,
            'attendance' => $attendanceSummary,
            'issued_date' => date('F j, Y'),
            'qr_payload' => json_encode($qrPayload),
            'verify_url' => $verifyUrl,
            'student' => $studentData,
            'summary' => $summaryData,
            'verification' => $verificationData,
        ];
    }
}
