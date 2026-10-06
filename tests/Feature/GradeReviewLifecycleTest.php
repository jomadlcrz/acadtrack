<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\GradeHistoryLog;
use App\Models\GradingPeriod;
use App\Models\GradingSheet;
use App\Models\GradingSheetEvent;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\GradingService;
use PHPUnit\Framework\TestCase;

/**
 * The grade review rules: every step is guarded and atomic, returns need a reason, adjustments are
 * attributed, the whole history is kept, and students only see marks once the sheet is finalized.
 */
class GradeReviewLifecycleTest extends TestCase
{
    private GradingService $service;
    private int $termId;
    private int $subjectId;
    private int $periodId;
    private int $deanId;
    private User $faculty;
    private User $studentUser;
    private Student $student;
    private int $sheetId;
    private ?Subject $testSubject = null;

    public static function setUpBeforeClass(): void
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();
        new \App\Core\Database(
            env('DB_HOST'),
            env('DB_DATABASE'),
            env('DB_USERNAME'),
            (string) env('DB_PASSWORD', '')
        );
    }

    protected function setUp(): void
    {
        $this->service = new GradingService();

        $term = AcademicTerm::where('semester', '1')->first();
        $this->termId = (int) $term->id;
        $this->periodId = (int) GradingPeriod::where('academic_term_id', $this->termId)->where('order_num', 1)->first()->id;
        $this->deanId = (int) User::where('role', 'Dean')->first()->id;

        $uniq = time() . '_' . rand(1000, 9999);
        $this->testSubject = Subject::create([
            'academic_term_id' => $this->termId,
            'subject_code' => "TST-{$uniq}",
            'descriptive_title' => "Review Test Subject {$uniq}",
            'units' => 3.0,
            'subject_type' => 'GenEd Core',
            'nature' => 'Lecture',
            'year_level' => 1,
            'semester' => 1,
        ]);
        $this->subjectId = (int) $this->testSubject->id;

        $this->faculty = User::create([
            'first_name' => 'Review',
            'last_name' => "Instructor_{$uniq}",
            'email' => "review_instructor_{$uniq}@gwc.edu",
            'password' => password_hash('secret123', PASSWORD_BCRYPT),
            'role' => 'Faculty',
            'status' => 'active',
        ]);

        $this->studentUser = User::create([
            'first_name' => 'Review',
            'last_name' => 'Student',
            'student_number' => "RV-{$uniq}",
            'email' => "review_student_{$uniq}@gwc.edu",
            'password' => password_hash('secret123', PASSWORD_BCRYPT),
            'role' => 'Student',
            'status' => 'active',
        ]);
        $this->student = Student::create(['user_id' => (int) $this->studentUser->id, 'year_level' => 1, 'status' => 'Regular']);
        Student::enroll((int) $this->student->id, $this->subjectId, $this->termId);

        // Instructor encodes a mark -> a DRAFT sheet exists
        $this->service->saveGrades((int) $this->faculty->id, $this->subjectId, $this->periodId, $this->termId, [(int) $this->student->id => 80.0]);
        $this->sheetId = (int) GradingSheet::findByComposite((int) $this->faculty->id, $this->subjectId, $this->periodId, $this->termId)['id'];
    }

    protected function tearDown(): void
    {
        GradeHistoryLog::where('student_id', (int) $this->student->id)->delete();
        Grade::where('student_id', (int) $this->student->id)->delete();
        Enrollment::where('student_id', (int) $this->student->id)->delete();
        GradingSheet::where('faculty_id', (int) $this->faculty->id)->delete(); // events cascade
        if ($this->testSubject) {
            $this->testSubject->delete();
        }
        $this->student->delete();
        $this->studentUser->delete();
        $this->faculty->delete();
    }

    private function sheetStatus(): string
    {
        return (string) GradingSheet::where('id', $this->sheetId)->value('status');
    }

    private function actions(): array
    {
        return array_column(GradingSheetEvent::forSheet($this->sheetId), 'action');
    }

    public function testOpeningASubmittedSheetStartsReviewOnce(): void
    {
        $this->service->submitGradingSheet($this->sheetId);
        $this->assertSame('SUBMITTED', $this->sheetStatus());

        $this->assertTrue($this->service->startReview($this->sheetId, $this->deanId));
        $this->assertSame('UNDER_REVIEW', $this->sheetStatus());
        $this->assertSame($this->deanId, (int) GradingSheet::where('id', $this->sheetId)->value('reviewed_by'));

        // A second open (or a second reviewer) changes nothing and adds no second event
        $this->assertFalse($this->service->startReview($this->sheetId, $this->deanId));
        $this->assertSame(['SUBMITTED', 'REVIEW_STARTED'], $this->actions());
    }

    public function testEachStepCanOnlyBeAppliedOnce(): void
    {
        $this->service->submitGradingSheet($this->sheetId);
        $this->service->approveGradingSheet($this->sheetId, $this->deanId);

        // Double click / second reviewer: the sheet is no longer awaiting review
        $this->expectException(\RuntimeException::class);
        try {
            $this->service->approveGradingSheet($this->sheetId, $this->deanId);
        } finally {
            $this->assertSame(1, count(array_keys($this->actions(), 'APPROVED', true)), 'Approval must be recorded exactly once');
        }
    }

    public function testOnlyAnApprovedSheetCanBeFinalized(): void
    {
        $this->service->submitGradingSheet($this->sheetId);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Only an APPROVED sheet');
        try {
            $this->service->confirmGradingSheet($this->sheetId, $this->deanId, 'skip approval');
        } finally {
            $this->assertSame('SUBMITTED', $this->sheetStatus());
        }
    }

    public function testDraftSheetCannotBeFinalized(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->service->confirmGradingSheet($this->sheetId, $this->deanId, 'nope');
    }

    public function testReturnNeedsAReasonAndReachesTheInstructor(): void
    {
        $this->service->submitGradingSheet($this->sheetId);

        try {
            $this->service->returnGradingSheet($this->sheetId, $this->deanId, '   ');
            $this->fail('A blank reason must be rejected');
        } catch (\DomainException $e) {
            $this->assertSame('SUBMITTED', $this->sheetStatus());
        }

        $this->service->returnGradingSheet($this->sheetId, $this->deanId, 'Midterm marks for two students look swapped.');
        $this->assertSame('RETURNED', $this->sheetStatus());
        $this->assertSame('Midterm marks for two students look swapped.', GradingSheet::where('id', $this->sheetId)->value('remarks'));

        // The instructor can fix and resubmit; the history keeps both rounds
        $this->service->submitGradingSheet($this->sheetId);
        $this->assertSame(['SUBMITTED', 'RETURNED', 'SUBMITTED'], $this->actions());
    }

    public function testFinalizedSheetCannotBeReturned(): void
    {
        $this->service->submitGradingSheet($this->sheetId);
        $this->service->approveGradingSheet($this->sheetId, $this->deanId);
        $this->service->confirmGradingSheet($this->sheetId, $this->deanId, 'Official.');

        $this->expectException(\RuntimeException::class);
        try {
            $this->service->returnGradingSheet($this->sheetId, $this->deanId, 'Changed my mind');
        } finally {
            $this->assertSame('FINALIZED', $this->sheetStatus(), 'A finalized sheet must stay locked');
        }
    }

    public function testAdjustingMarksNeedsAReasonAndIsAttributed(): void
    {
        $this->service->submitGradingSheet($this->sheetId);
        $studentId = (int) $this->student->id;

        try {
            $this->service->adjustGrades($this->sheetId, [$studentId => 90], '', $this->deanId);
            $this->fail('An adjustment without a reason must be rejected');
        } catch (\DomainException $e) {
            $this->assertEquals(80.0, (float) Grade::where('student_id', $studentId)->value('grade'), 'Nothing may change without a reason');
        }

        $changes = $this->service->adjustGrades($this->sheetId, [$studentId => 90], 'Recomputed from the class record.', $this->deanId);
        $this->assertCount(1, $changes);
        $this->assertEquals(90.0, (float) Grade::where('student_id', $studentId)->value('grade'));

        $log = GradeHistoryLog::where('student_id', $studentId)->orderBy('log_id', 'desc')->first();
        $this->assertSame($this->deanId, (int) $log->changed_by);
        $this->assertSame('Recomputed from the class record.', $log->reason);
        $this->assertContains('GRADES_ADJUSTED', $this->actions());

        // Same mark again is not a change
        $this->assertSame([], $this->service->adjustGrades($this->sheetId, [$studentId => 90], 'No-op check.', $this->deanId));
    }

    public function testMarksCannotBeAdjustedOnADraftOrFinalizedSheet(): void
    {
        $studentId = (int) $this->student->id;

        try {
            $this->service->adjustGrades($this->sheetId, [$studentId => 70], 'Dean should not touch drafts.', $this->deanId);
            $this->fail('Draft sheets belong to the instructor');
        } catch (\DomainException $e) {
            $this->assertEquals(80.0, (float) Grade::where('student_id', $studentId)->value('grade'));
        }

        $this->service->submitGradingSheet($this->sheetId);
        $this->service->approveGradingSheet($this->sheetId, $this->deanId);
        $this->service->confirmGradingSheet($this->sheetId, $this->deanId, 'Official.');

        $this->expectException(\DomainException::class);
        $this->service->adjustGrades($this->sheetId, [$studentId => 70], 'Too late.', $this->deanId);
    }

    public function testMarksMustBeBetweenZeroAndOneHundred(): void
    {
        $this->service->submitGradingSheet($this->sheetId);

        $this->expectException(\DomainException::class);
        $this->service->adjustGrades($this->sheetId, [(int) $this->student->id => 101], 'Typo test.', $this->deanId);
    }

    public function testStudentsOnlySeeMarksOnceTheSheetIsFinalized(): void
    {
        $studentId = (int) $this->student->id;
        $visible = fn (): int => count(array_filter(
            Grade::getByStudent($studentId, $this->termId),
            fn (array $row): bool => (int) $row['subject_id'] === $this->subjectId
        ));

        $this->assertSame(0, $visible(), 'DRAFT marks are hidden');

        $this->service->submitGradingSheet($this->sheetId);
        $this->assertSame(0, $visible(), 'SUBMITTED marks are hidden');

        $this->service->approveGradingSheet($this->sheetId, $this->deanId);
        $this->assertSame(0, $visible(), 'APPROVED marks are still hidden: the Dean can still fix them');

        $this->service->confirmGradingSheet($this->sheetId, $this->deanId, 'Official.');
        $this->assertSame(1, $visible(), 'FINALIZED marks are published, exactly once');
    }

    public function testHistoryKeepsEveryStepWithWhoDidIt(): void
    {
        $this->service->submitGradingSheet($this->sheetId);
        $this->service->startReview($this->sheetId, $this->deanId);
        $this->service->approveGradingSheet($this->sheetId, $this->deanId);
        $this->service->confirmGradingSheet($this->sheetId, $this->deanId, 'Official.');

        $events = GradingSheetEvent::forSheet($this->sheetId);
        $this->assertSame(['SUBMITTED', 'REVIEW_STARTED', 'APPROVED', 'FINALIZED'], array_column($events, 'action'));
        $this->assertSame(['SUBMITTED', 'UNDER_REVIEW', 'APPROVED', 'FINALIZED'], array_column($events, 'to_status'));
        $this->assertSame((int) $this->faculty->id, (int) $events[0]['actor_id'], 'The instructor submitted it');
        $this->assertSame($this->deanId, (int) $events[3]['actor_id']);
        $this->assertNotSame('', $events[3]['actor_name']);
    }

    public function testAutomaticGradingPeriodResolutionAndSync(): void
    {
        $current = GradingPeriod::syncCurrentPeriod($this->termId);
        $this->assertNotNull($current);
        $this->assertSame(1, (int) $current['is_current']);

        // Verify in DB that only this period has is_current = 1 for this term
        $allTermPeriods = GradingPeriod::getByAcademicTerm($this->termId);
        $currentCount = 0;
        foreach ($allTermPeriods as $p) {
            if ((int) $p['is_current'] === 1) {
                $currentCount++;
                $this->assertSame((int) $current['id'], (int) $p['id']);
            }
        }
        $this->assertSame(1, $currentCount);

        // Verify determineCurrent behaves predictably if periods are closed
        $closureService = new \App\Services\TermClosureService();
        $closureService->togglePeriodState((int) $current['id'], 'closed', 'Test closure');

        $nextPeriod = GradingPeriod::determineCurrent($this->termId);
        $this->assertNotNull($nextPeriod);
        $this->assertNotSame((int) $current['id'], (int) $nextPeriod['id']);
        $this->assertGreaterThan((int) $current['order_num'], (int) $nextPeriod['order_num']);

        // Reopen and verify it returns
        $closureService->togglePeriodState((int) $current['id'], 'open');
        $reopened = GradingPeriod::determineCurrent($this->termId);
        $this->assertSame((int) $current['id'], (int) $reopened['id']);
    }
}
