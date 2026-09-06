<?php

declare(strict_types=1);

namespace App\Controllers\Dean;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\AcademicTerm;
use App\Models\GradingPeriod;
use App\Models\GradingSetting;
use App\Models\GradingSheet;
use App\Repositories\GradeRepository;
use App\Repositories\GradingSheetRepository;
use App\Repositories\StudentRepository;
use App\Services\GradingService;
use App\Services\NotificationService;

class GradeReviewController
{
    private GradingSheetRepository $gradingSheetRepository;
    private GradingService $gradingService;
    private StudentRepository $studentRepository;
    private GradeRepository $gradeRepository;
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->gradingSheetRepository = new GradingSheetRepository();
        $this->gradingService = new GradingService();
        $this->studentRepository = new StudentRepository();
        $this->gradeRepository = new GradeRepository();
        $this->notificationService = new NotificationService();
    }

    public function index(Request $request, Response $response, Session $session): void
    {
        $academicTerm = AcademicTerm::getActive();
        $statusFilter = $request->get('status', 'all');

        $statusParam = match ($statusFilter) {
            'pending' => 'SUBMITTED',
            'approved' => 'APPROVED',
            'finalized' => 'FINALIZED',
            'returned' => 'RETURNED',
            default => null,
        };

        $gradingSheets = GradingSheet::getAllWithDetails((int) ($academicTerm['id'] ?? 0), $statusParam);

        $html = (new View())->render('dean.grade-review.index', [
            'gradingSheets' => $gradingSheets,
            'academicTerm' => $academicTerm,
            'statusFilter' => $statusFilter,
        ]);
        $response->html($html);
    }

    public function show(Request $request, Response $response, Session $session, string $id): void
    {
        $sheetId = (int) $id;
        $sheet = GradingSheet::findWithDetails($sheetId);

        if (!$sheet) {
            $session->flash('error', 'Grading sheet not found.');
            redirect('/dean/grade-review');
            return;
        }

        $academicTerm = AcademicTerm::getActive();
        $students = $this->studentRepository->getBySubject((int) $sheet['subject_id'], (int) $sheet['academic_term_id']);
        $existingGrades = $this->gradeRepository->getBySubjectAndPeriod((int) $sheet['subject_id'], (int) $sheet['grading_period_id'], (int) $sheet['academic_term_id']);

        $gradeMap = [];
        foreach ($existingGrades as $g) {
            $gradeMap[$g['student_id']] = $g['grade'];
        }

        $setting = GradingSetting::query()
            ->where('subject_id', (int) $sheet['subject_id'])
            ->where('academic_term_id', (int) $sheet['academic_term_id'])
            ->first();

        $html = (new View())->render('dean.grade-review.show', [
            'sheet' => $sheet,
            'students' => $students,
            'grades' => $gradeMap,
            'setting' => $setting,
            'academicTerm' => $academicTerm,
        ]);
        $response->html($html);
    }

    public function updateGrade(Request $request, Response $response, Session $session, string $id): void
    {
        $sheetId = (int) $id;
        $sheet = GradingSheet::findWithDetails($sheetId);

        if (!$sheet) {
            $session->flash('error', 'Grading sheet not found.');
            redirect('/dean/grade-review');
            return;
        }

        if ($sheet['status'] === GradingSheet::STATUS_FINALIZED) {
            $session->flash('error', 'Cannot edit a confirmed and finalized grading sheet.');
            redirect('/dean/grade-review/' . $sheetId);
            return;
        }

        $studentGrades = $request->post('grades', []);
        if (is_array($studentGrades)) {
            foreach ($studentGrades as $studentId => $gradeValue) {
                if ($gradeValue === '' || $gradeValue === null) {
                    continue;
                }
                $this->gradeRepository->saveGrade(
                    (int) $studentId,
                    (int) $sheet['subject_id'],
                    (int) $sheet['grading_period_id'],
                    (int) $sheet['academic_term_id'],
                    (float) $gradeValue
                );
            }
        }

        $session->flash('success', 'Student grade adjustments recorded successfully.');
        redirect('/dean/grade-review/' . $sheetId);
    }

    public function approve(Request $request, Response $response, Session $session): void
    {
        $gradingSheetId = (int) $request->post('grading_sheet_id');
        $user = $session->get('user');

        try {
            $this->gradingService->approveGradingSheet($gradingSheetId, (int) ($user['id'] ?? 0));
            
            $sheet = GradingSheet::findWithDetails($gradingSheetId);
            if ($sheet) {
                $this->notificationService->sendGradesPublished(
                    (int) $sheet['subject_id'],
                    (int) $sheet['academic_term_id'],
                    $sheet['period_name'] ?? 'Term'
                );
            }

            $session->flash('success', 'Grading sheet approved successfully. Enrolled students have been notified.');
        } catch (\RuntimeException $e) {
            $session->flash('error', $e->getMessage());
        }

        $redirectUrl = $request->post('redirect_to', '/dean/grade-review');
        redirect($redirectUrl);
    }

    public function confirm(Request $request, Response $response, Session $session): void
    {
        $gradingSheetId = (int) $request->post('grading_sheet_id');
        $remarks = trim((string) $request->post('remarks', ''));
        $user = $session->get('user');

        try {
            $this->gradingService->confirmGradingSheet($gradingSheetId, (int) ($user['id'] ?? 0), $remarks ?: 'Formally confirmed by academic administration.');
            $session->flash('success', 'Grading sheet officially confirmed and finalized.');
        } catch (\RuntimeException $e) {
            $session->flash('error', $e->getMessage());
        }

        $redirectUrl = $request->post('redirect_to', '/dean/grade-review');
        redirect($redirectUrl);
    }

    public function returnToFaculty(Request $request, Response $response, Session $session): void
    {
        $gradingSheetId = (int) $request->post('grading_sheet_id');

        try {
            $this->gradingService->returnGradingSheet($gradingSheetId);
            $session->flash('success', 'Grading sheet returned to faculty for revision.');
        } catch (\RuntimeException $e) {
            $session->flash('error', $e->getMessage());
        }

        redirect('/dean/grade-review');
    }

    public function printSheet(Request $request, Response $response, Session $session, string $id): void
    {
        $sheetId = (int) $id;
        $sheet = GradingSheet::findWithDetails($sheetId);

        if (!$sheet) {
            $session->flash('error', 'Grading sheet not found.');
            redirect('/dean/grade-review');
            return;
        }

        $academicTermId = (int) $sheet['academic_term_id'];
        $subjectId = (int) $sheet['subject_id'];

        $periods = GradingPeriod::where('academic_term_id', $academicTermId)->get();
        if (empty($periods)) {
            $periods = GradingPeriod::getActive();
        }

        $students = $this->studentRepository->getBySubject($subjectId, $academicTermId);

        // Fetch all grades for this subject and term
        $allGrades = GradeRepository::class;
        $pdo = \App\Core\Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT student_id, grading_period_id, grade 
            FROM grades 
            WHERE subject_id = :subject_id AND academic_term_id = :academic_term_id
        ");
        $stmt->execute(['subject_id' => $subjectId, 'academic_term_id' => $academicTermId]);
        $gradeRows = $stmt->fetchAll();

        $gradeMatrix = [];
        foreach ($gradeRows as $row) {
            $gradeMatrix[$row['student_id']][$row['grading_period_id']] = (float) $row['grade'];
        }

        $setting = GradingSetting::query()
            ->where('subject_id', $subjectId)
            ->where('academic_term_id', $academicTermId)
            ->first();

        $weights = [
            'prelim' => (float) ($setting['prelim_weight'] ?? 20.0),
            'midterm' => (float) ($setting['midterm_weight'] ?? 20.0),
            'semi_final' => (float) ($setting['semi_final_weight'] ?? 20.0),
            'final' => (float) ($setting['final_weight'] ?? 40.0),
        ];

        $html = (new View())->render('dean.grade-review.print', [
            'sheet' => $sheet,
            'periods' => $periods,
            'students' => $students,
            'gradeMatrix' => $gradeMatrix,
            'setting' => $setting,
            'weights' => $weights,
        ]);
        $response->html($html);
    }
}

