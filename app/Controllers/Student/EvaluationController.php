<?php

declare(strict_types=1);

namespace App\Controllers\Student;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Student;
use App\Models\AcademicTerm;
use App\Services\EvaluationService;
use App\Services\GradeService;

class EvaluationController
{
    private EvaluationService $evaluationService;
    private GradeService $gradeService;

    public function __construct()
    {
        $this->evaluationService = new EvaluationService();
        $this->gradeService = new GradeService();
    }

    private function prepareEvaluationData(Request $request, Session $session): array
    {
        $user = $session->get('user');
        $student = Student::findWithDetailsByUserId((int) ($user['id'] ?? 0))
            ?: Student::findByUserId((int) ($user['id'] ?? 0));

        $selectedSem = (string) $request->get('semester', '');
        $termId = 0;
        $academicTerm = null;
        if ($selectedSem === '1' || $selectedSem === '2') {
            $academicTerm = AcademicTerm::getBySemester($selectedSem);
            $termId = (int) ($academicTerm['id'] ?? 0);
        } else {
            $academicTerm = AcademicTerm::getActive();
        }

        $summary = $this->gradeService->getGradeSummary((int) ($student['id'] ?? 0), $termId);

        $evaluations = [];
        foreach ($summary as $subjectCode => $subjectGrades) {
            $evaluations[$subjectCode] = $this->evaluationService->calculateEvaluation($subjectGrades['periods']);
            $evaluations[$subjectCode]['subject_name'] = $subjectGrades['subject_name'];
            $evaluations[$subjectCode]['subject_id'] = $subjectGrades['subject_id'] ?? 0;
        }

        $studentId = (int) ($student['id'] ?? 0);
        $attendanceSummary = $studentId > 0 && $termId > 0 
            ? \App\Models\AttendanceRecord::getStudentOverallSummary($studentId, $termId)
            : null;

        $programName = null;
        if ($studentId > 0) {
            $pdo = \App\Core\Database::getConnection();
            $stmtProg = $pdo->prepare("
                SELECT p.program_name, p.program_abbrev
                FROM student_term_registrations str
                JOIN programs p ON p.id = str.program_id
                WHERE str.student_id = :sid
                ORDER BY str.id DESC LIMIT 1
            ");
            $stmtProg->execute(['sid' => $studentId]);
            $prog = $stmtProg->fetch(\PDO::FETCH_ASSOC);
            if (!$prog && !empty($student['set_id'])) {
                $stmtProgSet = $pdo->prepare("
                    SELECT p.program_name, p.program_abbrev
                    FROM sets sec
                    JOIN programs p ON p.id = sec.program_id
                    WHERE sec.id = :set_id LIMIT 1
                ");
                $stmtProgSet->execute(['set_id' => (int) $student['set_id']]);
                $prog = $stmtProgSet->fetch(\PDO::FETCH_ASSOC);
            }
            if ($prog) {
                $programName = $prog['program_name'] . (!empty($prog['program_abbrev']) ? ' (' . $prog['program_abbrev'] . ')' : '');
            }
        }

        return [
            'student' => $student,
            'programName' => $programName,
            'evaluations' => $evaluations,
            'academicTerm' => $academicTerm,
            'selectedSemester' => $selectedSem,
            'attendanceSummary' => $attendanceSummary,
        ];
    }

    public function show(Request $request, Response $response, Session $session): void
    {
        $data = $this->prepareEvaluationData($request, $session);
        $html = (new View())->render('student.evaluation.show', $data);
        $response->html($html);
    }

    public function printEvaluation(Request $request, Response $response, Session $session): void
    {
        $data = $this->prepareEvaluationData($request, $session);

        if (empty($data['evaluations'])) {
            $session->flash('error', 'No evaluation records available to print.');
            redirect('/student/evaluation');
            return;
        }

        $totalSum = 0;
        $count = 0;
        $passedCount = 0;
        foreach ($data['evaluations'] as $e) {
            $avg = (float) ($e['average'] ?? 0);
            if ($avg > 0) {
                $totalSum += $avg;
                $count++;
                if ($avg >= 75.0 && !in_array(strtoupper($e['status'] ?? ''), ['FAILING', 'NO GRADES', 'NEEDS IMPROVEMENT'], true)) {
                    $passedCount++;
                }
            }
        }
        $overallGwa = $count > 0 ? round($totalSum / $count, 2) : 0.0;

        $data['count'] = $count;
        $data['passedCount'] = $passedCount;
        $data['overallGwa'] = $overallGwa;

        $html = (new View())->render('student.evaluation.print', $data);
        $response->html($html);
    }
}
