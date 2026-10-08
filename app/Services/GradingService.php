<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\GradeRepository;
use App\Repositories\GradingSheetRepository;
use App\Models\GradingSheet;
use App\Models\GradingSheetEvent;

class GradingService
{
    private GradeRepository $gradeRepository;
    private GradingSheetRepository $gradingSheetRepository;

    public function __construct()
    {
        $this->gradeRepository = new GradeRepository();
        $this->gradingSheetRepository = new GradingSheetRepository();
    }

    public function saveGrades(int $facultyId, int $subjectId, int $gradingPeriodId, int $academicTermId, array $studentGrades): void
    {
        $term = \App\Models\AcademicTerm::find($academicTermId);
        if ($term && (bool) ($term->is_closed ?? false)) {
            throw new \DomainException('Cannot modify grades: Academic term has been officially closed and locked.');
        }

        $period = \App\Models\GradingPeriod::find($gradingPeriodId);
        if ($period && (bool) ($period->is_closed ?? false)) {
            throw new \DomainException('Cannot modify grades: This grading period has been officially closed and locked.');
        }

        $sheet = $this->gradingSheetRepository->findByComposite($facultyId, $subjectId, $gradingPeriodId, $academicTermId);
        if ($sheet && !in_array($sheet['status'], [GradingSheet::STATUS_DRAFT, GradingSheet::STATUS_RETURNED], true)) {
            throw new \DomainException('Cannot modify grades: this grading sheet has already been submitted for review. Ask the Dean to return it first.');
        }

        foreach ($studentGrades as $studentId => $grade) {
            if ($grade === '' || $grade === null) {
                continue;
            }
            if (!is_numeric($grade) || (float) $grade < 0 || (float) $grade > 100) {
                throw new \DomainException('Period score must be a number between 0.00 and 100.00.');
            }
            $gradeValue = round((float) $grade, 2);
            $this->gradeRepository->saveGrade(
                (int) $studentId,
                $subjectId,
                $gradingPeriodId,
                $academicTermId,
                $gradeValue
            );
        }

        $this->ensureGradingSheet($facultyId, $subjectId, $gradingPeriodId, $academicTermId);
    }

    private function ensureGradingSheet(int $facultyId, int $subjectId, int $gradingPeriodId, int $academicTermId): void
    {
        $existing = $this->gradingSheetRepository->findByComposite(
            $facultyId,
            $subjectId,
            $gradingPeriodId,
            $academicTermId
        );

        if (!$existing) {
            $this->gradingSheetRepository->create([
                'faculty_id' => $facultyId,
                'subject_id' => $subjectId,
                'grading_period_id' => $gradingPeriodId,
                'academic_term_id' => $academicTermId,
                'status' => GradingSheet::STATUS_DRAFT,
            ]);
        }
    }

    /**
     * Grading sheet lifecycle
     *
     *   DRAFT -> SUBMITTED -> UNDER_REVIEW -> APPROVED -> FINALIZED (students see marks only now)
     *                 \__________\____________\__> RETURNED (reason required) -> back to the instructor
     *
     * Every transition is a single conditional UPDATE (`WHERE status IN (...)`), so a double click or two
     * reviewers acting at once can never apply the same step twice, and each step is written to
     * `grading_sheet_events` so the full history survives return/resubmit cycles.
     */

    public function submitGradingSheet(int $gradingSheetId): void
    {
        $sheet = $this->gradingSheetRepository->findById($gradingSheetId);
        if (!$sheet) {
            throw new \RuntimeException("Grading sheet not found.");
        }

        $term = \App\Models\AcademicTerm::find((int) ($sheet['academic_term_id'] ?? 0));
        if ($term && (bool) ($term->is_closed ?? false)) {
            throw new \DomainException('Cannot submit grading sheet: Academic term has been officially closed and locked.');
        }

        $period = \App\Models\GradingPeriod::find((int) ($sheet['grading_period_id'] ?? 0));
        if ($period && (bool) ($period->is_closed ?? false)) {
            throw new \DomainException('Cannot submit grading sheet: This grading period has been officially closed and locked.');
        }

        if ($sheet['status'] !== GradingSheet::STATUS_DRAFT && $sheet['status'] !== GradingSheet::STATUS_RETURNED) {
            throw new \RuntimeException("Only DRAFT or RETURNED sheets can be submitted.");
        }

        $this->transition(
            $gradingSheetId,
            [GradingSheet::STATUS_DRAFT, GradingSheet::STATUS_RETURNED],
            GradingSheet::STATUS_SUBMITTED,
            ['submitted_at' => date('Y-m-d H:i:s')],
            GradingSheetEvent::SUBMITTED,
            (int) ($sheet['faculty_id'] ?? 0),
            'Only DRAFT or RETURNED sheets can be submitted.'
        );
        $this->logSheet($gradingSheetId, 'Sheet Submitted', 'Submitted');
    }

    /**
     * A reviewer opens a SUBMITTED sheet: it becomes UNDER_REVIEW and records who is looking at it.
     * Returns false (and changes nothing) if the sheet is not waiting for review.
     */
    public function startReview(int $gradingSheetId, int $reviewerId): bool
    {
        $changed = GradingSheet::where('id', $gradingSheetId)
            ->where('status', GradingSheet::STATUS_SUBMITTED)
            ->update([
                'status' => GradingSheet::STATUS_UNDER_REVIEW,
                'reviewed_by' => $reviewerId > 0 ? $reviewerId : null,
                'reviewed_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        if ($changed < 1) {
            return false;
        }

        $this->recordEvent($gradingSheetId, GradingSheetEvent::REVIEW_STARTED, GradingSheet::STATUS_SUBMITTED, GradingSheet::STATUS_UNDER_REVIEW, $reviewerId, null);
        return true;
    }

    public function approveGradingSheet(int $gradingSheetId, ?int $approvedBy = null): void
    {
        $sheet = $this->gradingSheetRepository->findById($gradingSheetId);
        if (!$sheet) {
            throw new \RuntimeException("Grading sheet not found.");
        }

        $this->transition(
            $gradingSheetId,
            [GradingSheet::STATUS_SUBMITTED, GradingSheet::STATUS_UNDER_REVIEW],
            GradingSheet::STATUS_APPROVED,
            ['approved_by' => $approvedBy, 'approved_at' => date('Y-m-d H:i:s')],
            GradingSheetEvent::APPROVED,
            $approvedBy,
            "Sheet must be SUBMITTED or UNDER_REVIEW to approve."
        );
        $this->logSheet($gradingSheetId, 'Sheet Approved', 'Approved');
    }

    /** Final, irreversible step. Only an APPROVED sheet can be finalized; this is also what publishes marks to students. */
    public function confirmGradingSheet(int $gradingSheetId, int $confirmedBy, ?string $remarks = null): void
    {
        $sheet = $this->gradingSheetRepository->findById($gradingSheetId);
        if (!$sheet) {
            throw new \RuntimeException("Grading sheet not found.");
        }

        $this->transition(
            $gradingSheetId,
            [GradingSheet::STATUS_APPROVED],
            GradingSheet::STATUS_FINALIZED,
            ['approved_by' => $confirmedBy, 'remarks' => $remarks, 'confirmed_at' => date('Y-m-d H:i:s')],
            GradingSheetEvent::FINALIZED,
            $confirmedBy,
            "Only an APPROVED sheet can be confirmed and finalized.",
            $remarks
        );
        $this->logSheet($gradingSheetId, 'Sheet Finalized', 'Finalized', $remarks);
    }

    /** Send a sheet back to its instructor. The reason is required so the instructor knows what to fix. */
    public function returnGradingSheet(int $gradingSheetId, ?int $returnedBy = null, ?string $reason = null): void
    {
        $sheet = $this->gradingSheetRepository->findById($gradingSheetId);
        if (!$sheet) {
            throw new \RuntimeException("Grading sheet not found.");
        }

        $reason = trim((string) $reason);
        if ($reason === '') {
            throw new \DomainException('Please tell the instructor what needs to be corrected before returning the sheet.');
        }

        $this->transition(
            $gradingSheetId,
            [GradingSheet::STATUS_SUBMITTED, GradingSheet::STATUS_UNDER_REVIEW, GradingSheet::STATUS_APPROVED],
            GradingSheet::STATUS_RETURNED,
            ['returned_at' => date('Y-m-d H:i:s'), 'remarks' => $reason],
            GradingSheetEvent::RETURNED,
            $returnedBy,
            "Only a sheet that is awaiting review or approved can be returned.",
            $reason
        );
        $this->logSheet($gradingSheetId, 'Sheet Returned', 'Returned to the instructor', $reason);
    }

    /**
     * Dean/Admin correction of marks while a sheet is in review. Needs a reason, respects term/period locks,
     * and writes who changed what to the grade history.
     *
     * @param array<int|string, mixed> $studentGrades student id => new mark
     * @return string[] human readable list of the marks that actually changed
     */
    public function adjustGrades(int $gradingSheetId, array $studentGrades, string $reason, int $actorId): array
    {
        $sheet = $this->gradingSheetRepository->findById($gradingSheetId);
        if (!$sheet) {
            throw new \RuntimeException("Grading sheet not found.");
        }

        $reason = trim($reason);
        if (mb_strlen($reason) < 5) {
            throw new \DomainException('Please give a short reason for the grade adjustment (at least 5 characters).');
        }

        if (!in_array($sheet['status'], [GradingSheet::STATUS_SUBMITTED, GradingSheet::STATUS_UNDER_REVIEW, GradingSheet::STATUS_APPROVED], true)) {
            throw new \DomainException('Marks can only be adjusted while the sheet is awaiting review or approved. Draft and returned sheets belong to the instructor; finalized sheets are locked.');
        }

        $term = \App\Models\AcademicTerm::find((int) $sheet['academic_term_id']);
        if ($term && (bool) ($term->is_closed ?? false)) {
            throw new \DomainException('Cannot adjust grades: Academic term has been officially closed and locked.');
        }
        $period = \App\Models\GradingPeriod::find((int) $sheet['grading_period_id']);
        if ($period && (bool) ($period->is_closed ?? false)) {
            throw new \DomainException('Cannot adjust grades: This grading period has been officially closed and locked.');
        }

        $changes = [];
        foreach ($studentGrades as $studentId => $value) {
            if ($value === '' || $value === null) {
                continue;
            }
            if (!is_numeric($value) || (float) $value < 0 || (float) $value > 100) {
                throw new \DomainException('Marks must be numbers from 0 to 100.');
            }
            $new = (float) $value;

            $old = \App\Models\Grade::where('student_id', (int) $studentId)
                ->where('subject_id', (int) $sheet['subject_id'])
                ->where('grading_period_id', (int) $sheet['grading_period_id'])
                ->where('academic_term_id', (int) $sheet['academic_term_id'])
                ->value('grade');

            if ($old !== null && abs((float) $old - $new) <= 0.0001) {
                continue;
            }

            $student = \App\Models\Student::find((int) $studentId);
            $detail = $student ? \App\Models\StudentDetail::where('user_id', $student->user_id)->first() : null;
            $name = $detail ? trim($detail->first_name . ' ' . $detail->last_name) : "student #{$studentId}";
            $changes[] = $name . ': ' . ($old === null ? 'none' : rtrim(rtrim((string) $old, '0'), '.')) . ' to ' . rtrim(rtrim(number_format($new, 2, '.', ''), '0'), '.');

            \App\Models\Grade::saveGrade(
                (int) $studentId,
                (int) $sheet['subject_id'],
                (int) $sheet['grading_period_id'],
                (int) $sheet['academic_term_id'],
                $new,
                $actorId,
                $reason
            );
        }

        if ($changes !== []) {
            $this->recordEvent(
                $gradingSheetId,
                GradingSheetEvent::GRADES_ADJUSTED,
                (string) $sheet['status'],
                (string) $sheet['status'],
                $actorId,
                $reason . ' (' . count($changes) . ' mark' . (count($changes) === 1 ? '' : 's') . ' changed)'
            );

            $info = GradingSheet::findWithDetails($gradingSheetId) ?? [];
            $code = (string) ($info['subject_code'] ?? "sheet #{$gradingSheetId}");
            $periodName = (string) ($info['period_name'] ?? '');
            $shown = implode('; ', array_slice($changes, 0, 5)) . (count($changes) > 5 ? '; and ' . (count($changes) - 5) . ' more' : '');
            ActivityLogService::record([
                'category' => ActivityLogService::CATEGORY_GRADES,
                'action' => 'Grades Adjusted',
                'target_type' => 'grading_sheet',
                'target_id' => $gradingSheetId,
                'target_label' => trim($code . ' - ' . $periodName, ' -'),
                'summary' => 'Adjusted ' . count($changes) . " mark(s) in the {$periodName} sheet for {$code}. Reason: {$reason}. {$shown}.",
            ]);
        }

        return $changes;
    }

    /** @return array<int, array<string, mixed>> the sheet's history, oldest first */
    public function getSheetEvents(int $gradingSheetId): array
    {
        return GradingSheetEvent::forSheet($gradingSheetId);
    }

    /**
     * Apply one status change atomically. Throws if the sheet is no longer in one of the allowed
     * "from" statuses (someone else already moved it), so a step can never be applied twice.
     *
     * @param string[] $from
     * @param array<string, mixed> $extra extra columns to set together with the status
     */
    private function transition(int $sheetId, array $from, string $to, array $extra, string $event, ?int $actorId, string $failureMessage, ?string $eventRemarks = null): void
    {
        $current = (string) (GradingSheet::where('id', $sheetId)->value('status') ?? '');

        $changed = GradingSheet::where('id', $sheetId)
            ->whereIn('status', $from)
            ->update(array_merge($extra, ['status' => $to, 'updated_at' => date('Y-m-d H:i:s')]));

        if ($changed < 1) {
            throw new \RuntimeException($failureMessage);
        }

        $this->recordEvent($sheetId, $event, $current !== '' ? $current : null, $to, $actorId, $eventRemarks);
    }

    private function recordEvent(int $sheetId, string $action, ?string $from, ?string $to, ?int $actorId, ?string $remarks): void
    {
        try {
            GradingSheetEvent::create([
                'grading_sheet_id' => $sheetId,
                'action' => $action,
                'from_status' => $from,
                'to_status' => $to,
                'actor_id' => $actorId > 0 ? $actorId : null,
                'actor_name' => $this->actorName($actorId),
                'remarks' => $remarks !== null && $remarks !== '' ? $remarks : null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // The history must never block the action it describes
            error_log('[GradingSheetEvent] Could not record event: ' . $e->getMessage());
        }
    }

    private function actorName(?int $actorId): string
    {
        if ($actorId === null || $actorId <= 0) {
            return 'System';
        }

        $stmt = \App\Core\Database::getConnection()->prepare("
            SELECT u.email,
                   COALESCE(ad.first_name, fd.first_name, sd.first_name) AS first_name,
                   COALESCE(ad.last_name, fd.last_name, sd.last_name) AS last_name
            FROM users u
            LEFT JOIN admin_details ad ON ad.user_id = u.id
            LEFT JOIN faculty_details fd ON fd.user_id = u.id
            LEFT JOIN student_details sd ON sd.user_id = u.id
            WHERE u.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $actorId]);
        $row = $stmt->fetch();
        if (!$row) {
            return 'Staff';
        }

        return trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: (string) ($row['email'] ?? 'Staff');
    }

    public function getGradesByStudent(int $studentId, int $academicTermId): array
    {
        return $this->gradeRepository->getByStudent($studentId, $academicTermId);
    }

    public function getGradesBySubjectAndPeriod(int $subjectId, int $gradingPeriodId, int $academicTermId): array
    {
        return $this->gradeRepository->getBySubjectAndPeriod($subjectId, $gradingPeriodId, $academicTermId);
    }

    public function calculateFinalGrade(array $grades): float
    {
        if (empty($grades)) {
            return 0.0;
        }

        $weightedSum = 0;
        $totalWeight = 0;

        foreach ($grades as $grade) {
            $weight = $grade['weight'] ?? 1;
            $weightedSum += $grade['grade'] * $weight;
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : 0.0;
    }

    private function logSheet(int $gradingSheetId, string $action, string $verb, ?string $remarks = null): void
    {
        $sheet = GradingSheet::findWithDetails($gradingSheetId);
        if (!$sheet) {
            return;
        }

        $faculty = trim(($sheet['faculty_first_name'] ?? '') . ' ' . ($sheet['faculty_last_name'] ?? '')) ?: ($sheet['faculty_email'] ?? 'the instructor');
        $summary = "{$verb} the {$sheet['period_name']} grading sheet for {$sheet['subject_code']} by {$faculty}.";
        if ($remarks !== null && $remarks !== '') {
            $summary .= " Remarks: {$remarks}";
        }

        ActivityLogService::record([
            'category' => ActivityLogService::CATEGORY_GRADES,
            'action' => $action,
            'target_type' => 'grading_sheet',
            'target_id' => $gradingSheetId,
            'target_label' => $sheet['subject_code'] . ' - ' . $sheet['period_name'],
            'summary' => $summary,
        ]);
    }
}
