<?php

declare(strict_types=1);

namespace App\Controllers\Student;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Student;
use App\Models\AcademicTerm;
use App\Services\GradeService;

class GradeController
{
    private GradeService $gradeService;

    public function __construct()
    {
        $this->gradeService = new GradeService();
    }

    public function index(Request $request, Response $response, Session $session): void
    {
        $user = $session->get('user');
        $student = Student::findByUserId((int) ($user['id'] ?? 0));
        
        $selectedSem = (string) $request->get('semester', '');
        if ($selectedSem === '1' || $selectedSem === '2') {
            $academicTerm = AcademicTerm::getBySemester($selectedSem);
        } else {
            $academicTerm = AcademicTerm::getActive();
        }

        $grades = $this->gradeService->getStudentGrades($student['id'] ?? 0, $academicTerm['id'] ?? 0);
        $summary = $this->gradeService->getGradeSummary($student['id'] ?? 0, $academicTerm['id'] ?? 0);

        $html = (new View())->render('student.grades.index', [
            'grades' => $grades,
            'summary' => $summary,
            'academicTerm' => $academicTerm,
            'selectedSemester' => (string) ($academicTerm['semester'] ?? '1'),
        ]);
        $response->html($html);
    }
}
