<?php

declare(strict_types=1);

namespace App\Controllers\Faculty;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\StudentRepository;
use App\Repositories\GradeRepository;
use App\Repositories\GradingSheetRepository;
use App\Models\AcademicTerm;
use App\Models\GradingPeriod;
use App\Services\GradingService;
use App\Validators\GradeValidator;

class GradingController
{
    private GradingService $gradingService;
    private StudentRepository $studentRepository;
    private GradeRepository $gradeRepository;

    public function __construct()
    {
        $this->gradingService = new GradingService();
        $this->studentRepository = new StudentRepository();
        $this->gradeRepository = new GradeRepository();
    }

    public function index(Request $request, Response $response, Session $session): void
    {
        $subjectId = (int) $request->get('subject_id');
        $periodId = (int) $request->get('period_id', 0);
        $academicTerm = AcademicTerm::getActive();
        $periods = GradingPeriod::getActive();

        if ($periodId === 0 && !empty($periods)) {
            $currentPeriod = GradingPeriod::getCurrent();
            $periodId = $currentPeriod['id'] ?? $periods[0]['id'];
        }

        $students = $this->studentRepository->getBySubject($subjectId, $academicTerm['id'] ?? 0);
        $existingGrades = $this->gradeRepository->getBySubjectAndPeriod($subjectId, $periodId, $academicTerm['id'] ?? 0);

        $gradeMap = [];
        foreach ($existingGrades as $grade) {
            $gradeMap[$grade['student_id']] = $grade['grade'];
        }

        $user = $session->get('user');
        $gradingSheet = (new GradingSheetRepository())->findByComposite(
            (int) $user['id'],
            $subjectId,
            $periodId,
            $academicTerm['id'] ?? 0
        );

        $html = (new View())->render('faculty.grading.index', [
            'students' => $students,
            'grades' => $gradeMap,
            'subjectId' => $subjectId,
            'periodId' => $periodId,
            'periods' => $periods,
            'academicTerm' => $academicTerm,
            'gradingSheet' => $gradingSheet,
        ]);
        $response->html($html);
    }

    public function save(Request $request, Response $response, Session $session): void
    {
        $subjectId = (int) $request->post('subject_id');
        $gradingPeriodId = (int) $request->post('grading_period_id');
        $academicTerm = AcademicTerm::getActive();
        $user = $session->get('user');

        $validator = new GradeValidator();
        $data = $request->all();

        if (!$validator->validate($data)) {
            $session->flash('error', $validator->firstError());
            redirect("/faculty/grading?subject_id={$subjectId}&period_id={$gradingPeriodId}");
            return;
        }

        $this->gradingService->saveGrades(
            (int) $user['id'],
            $subjectId,
            $gradingPeriodId,
            $academicTerm['id'],
            $data['grades']
        );

        $session->flash('success', 'Grades saved successfully.');
        redirect("/faculty/grading?subject_id={$subjectId}&period_id={$gradingPeriodId}");
    }

    public function submit(Request $request, Response $response, Session $session): void
    {
        $gradingSheetId = (int) $request->post('grading_sheet_id');

        try {
            $this->gradingService->submitGradingSheet($gradingSheetId);
            $session->flash('success', 'Grading sheet submitted for review.');
        } catch (\RuntimeException $e) {
            $session->flash('error', $e->getMessage());
        }

        redirect('/faculty/grading');
    }
}
