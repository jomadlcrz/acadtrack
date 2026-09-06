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
        $selectedSem = (string) $request->get('semester', '');
        if ($selectedSem === '1' || $selectedSem === '2') {
            $academicTerm = AcademicTerm::getBySemester($selectedSem);
        } else {
            $academicTerm = AcademicTerm::getActive();
        }

        $termId = (int) ($academicTerm['id'] ?? 1);
        $user = $session->get('user');
        $assignedSubjects = \App\Models\Faculty::getAssignedSubjects((int) $user['id'], $termId);

        $subjectId = (int) $request->get('subject_id');
        if ($subjectId === 0 && !empty($assignedSubjects)) {
            $subjectId = (int) ($assignedSubjects[0]['id'] ?? 0);
        }

        $periods = GradingPeriod::where('academic_term_id', $termId)->orderBy('order_num')->get()->toArray();
        if (empty($periods)) {
            $periods = GradingPeriod::getActive();
        }

        $periodId = (int) $request->get('period_id', 0);
        if ($periodId === 0 && !empty($periods)) {
            $periodId = (int) ($periods[0]['id'] ?? 0);
        }

        $students = $this->studentRepository->getBySubject($subjectId, $termId);
        $existingGrades = $this->gradeRepository->getBySubjectAndPeriod($subjectId, $periodId, $termId);

        $gradeMap = [];
        foreach ($existingGrades as $grade) {
            $gradeMap[$grade['student_id']] = $grade['grade'];
        }

        $gradingSheet = (new GradingSheetRepository())->findByComposite(
            (int) $user['id'],
            $subjectId,
            $periodId,
            $termId
        );

        $html = (new View())->render('faculty.grading.index', [
            'students' => $students,
            'grades' => $gradeMap,
            'subjectId' => $subjectId,
            'periodId' => $periodId,
            'periods' => $periods,
            'academicTerm' => $academicTerm,
            'selectedSemester' => (string) ($academicTerm['semester'] ?? '1'),
            'assignedSubjects' => $assignedSubjects,
            'gradingSheet' => $gradingSheet,
        ]);
        $response->html($html);
    }

    public function save(Request $request, Response $response, Session $session): void
    {
        $subjectId = (int) $request->post('subject_id');
        $gradingPeriodId = (int) $request->post('grading_period_id');
        $termId = (int) $request->post('academic_term_id', 0);
        
        if ($termId === 0) {
            $academicTerm = AcademicTerm::getActive();
            $termId = (int) ($academicTerm['id'] ?? 1);
        }

        $user = $session->get('user');

        $validator = new GradeValidator();
        $data = $request->all();

        $semester = (string) $request->post('semester', '');
        $semQuery = $semester !== '' ? "&semester={$semester}" : '';

        if (!$validator->validate($data)) {
            $session->flash('error', $validator->firstError());
            redirect("/faculty/grading?subject_id={$subjectId}&period_id={$gradingPeriodId}{$semQuery}");
            return;
        }

        $this->gradingService->saveGrades(
            (int) $user['id'],
            $subjectId,
            $gradingPeriodId,
            $termId,
            $data['grades'] ?? []
        );

        $session->flash('success', 'Grades saved successfully.');
        redirect("/faculty/grading?subject_id={$subjectId}&period_id={$gradingPeriodId}{$semQuery}");
    }

    public function submit(Request $request, Response $response, Session $session): void
    {
        $gradingSheetId = (int) $request->post('grading_sheet_id');
        $redirectUrl = '/faculty/grading';

        try {
            $sheet = \App\Models\GradingSheet::find($gradingSheetId);
            if ($sheet) {
                $term = \App\Models\AcademicTerm::find($sheet->academic_term_id);
                $sem = $term ? (string)$term->semester : '';
                $semQuery = $sem !== '' ? "&semester={$sem}" : '';
                $redirectUrl = "/faculty/grading?subject_id={$sheet->subject_id}&period_id={$sheet->grading_period_id}{$semQuery}";
            }

            $this->gradingService->submitGradingSheet($gradingSheetId);
            $session->flash('success', 'Grading sheet submitted for review.');
        } catch (\RuntimeException $e) {
            $session->flash('error', $e->getMessage());
        }

        redirect($redirectUrl);
    }
}
