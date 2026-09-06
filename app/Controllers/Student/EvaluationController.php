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

    public function show(Request $request, Response $response, Session $session): void
    {
        $user = $session->get('user');
        $student = Student::findByUserId($user['id']);
        $academicTerm = AcademicTerm::getActive();

        $summary = $this->gradeService->getGradeSummary($student['id'] ?? 0, $academicTerm['id'] ?? 0);

        $evaluations = [];
        foreach ($summary as $subjectCode => $subjectGrades) {
            $evaluations[$subjectCode] = $this->evaluationService->calculateEvaluation($subjectGrades['periods']);
            $evaluations[$subjectCode]['subject_name'] = $subjectGrades['subject_name'];
        }

        $html = (new View())->render('student.evaluation.show', [
            'evaluations' => $evaluations,
            'academicTerm' => $academicTerm,
        ]);
        $response->html($html);
    }
}
