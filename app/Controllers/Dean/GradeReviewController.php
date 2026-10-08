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
        $selectedSem = (string) $request->get('semester', '');
        if ($selectedSem === '1' || $selectedSem === '2') {
            $academicTerm = AcademicTerm::getBySemester($selectedSem);
        } else {
            $academicTerm = AcademicTerm::getActive();
        }

        $termId = (int) ($academicTerm['id'] ?? 1);
        $statusFilter = (string) $request->get('status', 'all');

        $statuses = match ($statusFilter) {
            'pending' => ['SUBMITTED', 'UNDER_REVIEW'],
            'approved' => ['APPROVED'],
            'finalized' => ['FINALIZED'],
            'returned' => ['RETURNED'],
            default => [],
        };

        // Period filter: automatically resolve and sync the current grading period
        \App\Models\GradingPeriod::syncCurrentPeriod($termId);
        $periods = \App\Models\GradingPeriod::getByAcademicTerm($termId);
        $periodParam = (string) $request->get('period', 'all');
        $validPeriodIds = array_map(static fn (array $p): string => (string) $p['id'], $periods);
        if ($periodParam !== 'all' && !in_array($periodParam, $validPeriodIds, true)) {
            $periodParam = 'all';
        }
        $periodId = $periodParam === 'all' ? null : (int) $periodParam;

        // Summary figures follow the selected period so they match the list below
        $totalSubmissions = 0;
        $pendingCount = 0;
        $approvedCount = 0;
        $finalizedCount = 0;
        $returnedCount = 0;
        foreach (GradingSheet::getAllWithDetails($termId) as $sh) {
            if ($periodId !== null && (int) ($sh['grading_period_id'] ?? 0) !== $periodId) {
                continue;
            }
            $totalSubmissions++;
            $st = $sh['status'] ?? '';
            if (in_array($st, ['SUBMITTED', 'UNDER_REVIEW'], true)) {
                $pendingCount++;
            } elseif ($st === 'APPROVED') {
                $approvedCount++;
            } elseif ($st === 'FINALIZED') {
                $finalizedCount++;
            } elseif ($st === 'RETURNED') {
                $returnedCount++;
            }
        }

        $page = max(1, (int) $request->get('page', 1));
        $search = trim((string) $request->get('search', ''));
        $paginated = GradingSheet::paginateGroupedWithDetails($termId, $statuses, $periodId, $page, 15, $search);

        $html = (new View())->render('dean.grade-review.index', [
            'groups' => $paginated['data'],
            'pagination' => $paginated,
            'academicTerm' => $academicTerm,
            'selectedSemester' => (string) ($academicTerm['semester'] ?? '1'),
            'statusFilter' => $statusFilter,
            'currentSearch' => $search,
            'periods' => $periods,
            'periodParam' => $periodParam,
            'metrics' => [
                'total' => $totalSubmissions,
                'pending' => $pendingCount,
                'approved' => $approvedCount,
                'finalized' => $finalizedCount,
                'returned' => $returnedCount,
            ],
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

        // Opening a submitted sheet is what starts the review: it becomes UNDER_REVIEW and records who is looking.
        if ($sheet['status'] === GradingSheet::STATUS_SUBMITTED) {
            $user = $session->get('user');
            if ($this->gradingService->startReview($sheetId, (int) ($user['id'] ?? 0))) {
                $sheet = GradingSheet::findWithDetails($sheetId) ?? $sheet;
            }
        }

        $academicTerm = AcademicTerm::find((int) $sheet['academic_term_id']) ?: AcademicTerm::getActive();
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

        // The same course and instructor in the other grading periods, for quick context
        $siblings = [];
        foreach (GradingPeriod::getByAcademicTerm((int) $sheet['academic_term_id']) as $period) {
            $other = GradingSheet::findByComposite((int) $sheet['faculty_id'], (int) $sheet['subject_id'], (int) $period['id'], (int) $sheet['academic_term_id']);
            $siblings[] = [
                'period_name' => $period['name'],
                'sheet_id' => $other['id'] ?? null,
                'status' => $other['status'] ?? null,
                'is_this' => (int) $period['id'] === (int) $sheet['grading_period_id'],
            ];
        }

        $html = (new View())->render('dean.grade-review.show', [
            'sheet' => $sheet,
            'students' => $students,
            'grades' => $gradeMap,
            'setting' => $setting,
            'academicTerm' => $academicTerm,
            'summary' => $this->summarize($students, $gradeMap),
            'events' => $this->gradingService->getSheetEvents($sheetId),
            'siblings' => $siblings,
        ]);
        $response->html($html);
    }

    /**
     * Class summary the Dean reads before deciding: how many are graded, pass/fail, average, and who is near the line.
     *
     * @param array<int, array<string, mixed>> $students
     * @param array<int|string, mixed> $gradeMap student id => mark
     * @return array<string, mixed>
     */
    private function summarize(array $students, array $gradeMap): array
    {
        $marks = [];
        $noMark = 0;
        foreach ($students as $student) {
            $grade = $gradeMap[$student['id']] ?? null;
            if ($grade === null || $grade === '') {
                $noMark++;
                continue;
            }
            $marks[] = (float) $grade;
        }

        $passed = count(array_filter($marks, static fn (float $m): bool => $m >= 75.0));
        $nearLine = count(array_filter($marks, static fn (float $m): bool => $m >= 73.0 && $m < 75.0));

        return [
            'total' => count($students),
            'graded' => count($marks),
            'no_mark' => $noMark,
            'passed' => $passed,
            'failed' => count($marks) - $passed,
            'near_line' => $nearLine,
            'average' => $marks !== [] ? round(array_sum($marks) / count($marks), 2) : null,
            'highest' => $marks !== [] ? max($marks) : null,
            'lowest' => $marks !== [] ? min($marks) : null,
        ];
    }

    /** Only allow redirecting back to a grade review page of this app. */
    private function safeRedirect(Request $request, string $default): string
    {
        $target = (string) $request->post('redirect_to', '');
        if ($target !== '' && preg_match('#^/[^/\\\\]#', $target) === 1 && str_contains($target, '/dean/grade-review')) {
            return $target;
        }
        return $default;
    }

    public function updateGrade(Request $request, Response $response, Session $session, string $id): void
    {
        $sheetId = (int) $id;
        $session->flash('error', 'Direct grade adjustments are disabled. If a grade is incorrect, please return the grading sheet to the instructor with remarks for correction.');
        redirect('/dean/grade-review/' . $sheetId);
    }

    public function approve(Request $request, Response $response, Session $session): void
    {
        $gradingSheetId = (int) $request->post('grading_sheet_id');
        $user = $session->get('user');

        try {
            $this->gradingService->approveGradingSheet($gradingSheetId, (int) ($user['id'] ?? 0));
            $session->flash('success', 'Grading sheet approved. Students will see the marks once you confirm and finalize it.');
        } catch (\RuntimeException | \DomainException $e) {
            $session->flash('error', $e->getMessage());
        }

        redirect($this->safeRedirect($request, '/dean/grade-review'));
    }

    public function confirm(Request $request, Response $response, Session $session): void
    {
        $gradingSheetId = (int) $request->post('grading_sheet_id');
        $remarks = trim((string) $request->post('remarks', ''));
        $user = $session->get('user');

        try {
            $this->gradingService->confirmGradingSheet($gradingSheetId, (int) ($user['id'] ?? 0), $remarks ?: 'Formally confirmed by academic administration.');

            // Marks become visible to students at this point, so this is when they are told.
            $sheet = GradingSheet::findWithDetails($gradingSheetId);
            if ($sheet) {
                $this->notificationService->sendGradesPublished(
                    (int) $sheet['subject_id'],
                    (int) $sheet['academic_term_id'],
                    $sheet['period_name'] ?? 'Term'
                );
            }

            $session->flash('success', 'Grading sheet finalized. The marks are now official and enrolled students have been notified.');
        } catch (\RuntimeException | \DomainException $e) {
            $session->flash('error', $e->getMessage());
        }

        redirect($this->safeRedirect($request, '/dean/grade-review'));
    }

    public function returnToFaculty(Request $request, Response $response, Session $session): void
    {
        $gradingSheetId = (int) $request->post('grading_sheet_id');
        $reason = trim((string) $request->post('reason', ''));
        $user = $session->get('user');

        try {
            $this->gradingService->returnGradingSheet($gradingSheetId, (int) ($user['id'] ?? 0), $reason);

            $sheet = GradingSheet::findWithDetails($gradingSheetId);
            if ($sheet) {
                $this->notificationService->sendGradeReturned($sheet, $reason);
            }

            $session->flash('success', 'Grading sheet returned to the instructor with your reason.');
        } catch (\RuntimeException | \DomainException $e) {
            $session->flash('error', $e->getMessage());
            redirect($this->safeRedirect($request, '/dean/grade-review/' . $gradingSheetId));
            return;
        }

        redirect($this->safeRedirect($request, '/dean/grade-review'));
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

