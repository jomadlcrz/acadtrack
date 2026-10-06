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
use App\Models\GradingSetting;
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
        $validPeriodIds = array_map(fn($p) => (int) $p['id'], $periods);
        if ($periodId === 0 || !in_array($periodId, $validPeriodIds, true)) {
            $periodId = (int) ($periods[0]['id'] ?? 0);
        }

        $students = $this->studentRepository->getBySubject($subjectId, $termId);

        // Server-side paging + search: the roster is loaded per page instead of
        // rendering every enrolled student in one tall scrollable table.
        $page = max(1, (int) $request->get('page', 1));
        $search = trim((string) $request->get('search', ''));
        $perPage = 15;
        $paginated = $this->studentRepository->paginateBySubject($subjectId, $termId, null, $page, $perPage, $search);
        if ($page > $paginated['lastPage'] && $paginated['lastPage'] >= 1) {
            $paginated = $this->studentRepository->paginateBySubject($subjectId, $termId, null, $paginated['lastPage'], $perPage, $search);
        }
        $students = $paginated['data'];

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

        $currentSubject = null;
        foreach ($assignedSubjects as $subj) {
            if ((int) $subj['id'] === $subjectId) {
                $currentSubject = $subj;
                break;
            }
        }

        $gradingSetting = GradingSetting::getForSubject($subjectId, $termId, (int) $user['id']);

        $html = (new View())->render('faculty.grading.index', [
            'students' => $students,
            'pagination' => $paginated,
            'currentSearch' => $search,
            'grades' => $gradeMap,
            'subjectId' => $subjectId,
            'currentSubject' => $currentSubject,
            'gradingSetting' => $gradingSetting,
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
        $pageQuery = '&page=' . max(1, (int) $request->post('page', 1));
        $searchRaw = trim((string) $request->post('search', ''));
        $searchQuery = $searchRaw !== '' ? '&search=' . rawurlencode($searchRaw) : '';

        if (!$validator->validate($data)) {
            $session->flash('error', $validator->firstError());
            redirect("/faculty/grading?subject_id={$subjectId}&period_id={$gradingPeriodId}{$semQuery}{$pageQuery}{$searchQuery}");
            return;
        }

        try {
            $this->gradingService->saveGrades(
                (int) $user['id'],
                $subjectId,
                $gradingPeriodId,
                $termId,
                $data['grades'] ?? []
            );
            $session->flash('success', 'Grades saved successfully.');
        } catch (\DomainException $e) {
            $session->flash('error', $e->getMessage());
        }

        redirect("/faculty/grading?subject_id={$subjectId}&period_id={$gradingPeriodId}{$semQuery}{$pageQuery}{$searchQuery}");
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

    public function saveSettings(Request $request, Response $response, Session $session): void
    {
        $subjectId = (int) $request->post('subject_id');
        $periodId = (int) $request->post('period_id');
        $termId = (int) $request->post('academic_term_id', 0);
        $semester = (string) $request->post('semester', '');
        $semQuery = $semester !== '' ? "&semester={$semester}" : '';
        $pageQuery = '&page=' . max(1, (int) $request->post('page', 1));
        $searchRaw = trim((string) $request->post('search', ''));
        $searchQuery = $searchRaw !== '' ? '&search=' . rawurlencode($searchRaw) : '';

        if ($termId === 0) {
            $academicTerm = AcademicTerm::getActive();
            $termId = (int) ($academicTerm['id'] ?? 1);
        }

        $user = $session->get('user');
        $facultyId = (int) ($user['id'] ?? 0);

        $gradingMethod = (string) $request->post('grading_method', 'zero_based');
        if (!in_array($gradingMethod, ['zero_based', 'fifty_based'], true)) {
            $gradingMethod = 'zero_based';
        }

        $prelimWeight = (float) $request->post('prelim_weight', 20.00);
        $midtermWeight = (float) $request->post('midterm_weight', 20.00);
        $semiFinalWeight = (float) $request->post('semi_final_weight', 20.00);
        $finalWeight = (float) $request->post('final_weight', 40.00);

        // Period weights must sum to 100%
        $totalWeight = $prelimWeight + $midtermWeight + $semiFinalWeight + $finalWeight;
        if (abs($totalWeight - 100.0) > 0.01) {
            $session->flash('error', 'Period weights must sum to exactly 100%. Current total: ' . number_format($totalWeight, 2) . '%');
            redirect("/faculty/grading?subject_id={$subjectId}&period_id={$periodId}{$semQuery}{$pageQuery}{$searchQuery}");
            return;
        }

        try {
            $setting = GradingSetting::where('subject_id', $subjectId)
                ->where('academic_term_id', $termId)
                ->first();

            if (!$setting) {
                $setting = new GradingSetting();
                $setting->subject_id = $subjectId;
                $setting->academic_term_id = $termId;
            }

            $setting->faculty_id = $facultyId;
            $setting->grading_method = $gradingMethod;
            $setting->prelim_weight = $prelimWeight;
            $setting->midterm_weight = $midtermWeight;
            $setting->semi_final_weight = $semiFinalWeight;
            $setting->final_weight = $finalWeight;
            $setting->save();

            $session->flash('success', 'Grading settings and period weights applied to subject successfully.');
        } catch (\Throwable $e) {
            $session->flash('error', 'Failed to save subject grading settings: ' . $e->getMessage());
        }

        redirect("/faculty/grading?subject_id={$subjectId}&period_id={$periodId}{$semQuery}{$pageQuery}{$searchQuery}");
    }
}
