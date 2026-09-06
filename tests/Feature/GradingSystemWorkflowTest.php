<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Models\AcademicTerm;
use App\Models\GradingPeriod;
use App\Models\GradingSetting;
use App\Models\GradingSheet;
use App\Models\Notification;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\GradingService;
use App\Services\NotificationService;

class GradingSystemWorkflowTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->safeLoad();
        new \App\Core\Database(
            $_ENV['DB_HOST'] ?? '127.0.0.1',
            $_ENV['DB_DATABASE'] ?? 'grading_system',
            $_ENV['DB_USERNAME'] ?? 'root',
            $_ENV['DB_PASSWORD'] ?? ''
        );
    }

    public function testSemesterSeparationAndGradingPeriods(): void
    {
        $term1 = AcademicTerm::query()->where('semester', '1')->first();
        $this->assertNotNull($term1, '1st Semester must exist');

        $term2 = AcademicTerm::query()->where('semester', '2')->first();
        $this->assertNotNull($term2, '2nd Semester must exist');

        $periodsTerm1 = GradingPeriod::where('academic_term_id', $term1['id'])->get();
        $this->assertCount(4, $periodsTerm1, '1st Semester must have 4 grading periods (Prelim, Midterm, Semi-Final, Final)');

        $periodsTerm2 = GradingPeriod::where('academic_term_id', $term2['id'])->get();
        $this->assertCount(4, $periodsTerm2, '2nd Semester must have 4 grading periods');
    }

    public function testSubjectNatureAndGradingSettings(): void
    {
        $subject = Subject::first();
        $this->assertNotNull($subject);

        // Subject nature should be one of Lecture, Laboratory, Combined
        $this->assertContains($subject['nature'], ['Lecture', 'Laboratory', 'Combined']);

        // Test configuring grading settings
        $term = AcademicTerm::getActive();
        $setting = GradingSetting::create([
            'academic_term_id' => $term['id'],
            'subject_id' => $subject['id'],
            'grading_method' => 'fifty_based',
            'prelim_weight' => 20.00,
            'midterm_weight' => 20.00,
            'semi_final_weight' => 20.00,
            'final_weight' => 40.00,
        ]);

        $this->assertNotNull($setting);
        $settingId = (int) $setting->id;
        $this->assertGreaterThan(0, $settingId);
        $found = GradingSetting::find($settingId);
        $this->assertSame('fifty_based', $found['grading_method']);
        $this->assertEquals(20.00, (float) $found['prelim_weight']);
        $this->assertEquals(40.00, (float) $found['final_weight']);

        // Clean up test setting
        GradingSetting::query()->where('id', $settingId)->delete();
    }

    public function testStudentStatusAndRoster(): void
    {
        // Query existing students
        $students = Student::all();
        $this->assertNotEmpty($students, 'There must be at least one student record');
        foreach ($students as $student) {
            $this->assertContains($student['status'], ['Regular', 'Irregular']);
            $this->assertGreaterThanOrEqual(1, (int) $student['year_level']);
            $this->assertLessThanOrEqual(4, (int) $student['year_level']);
        }
    }

    public function testNotificationDispatchSystem(): void
    {
        $admin = User::query()->where('role', 'Admin')->first();
        $this->assertNotNull($admin);

        $notifService = new NotificationService();
        $notifService->sendStudentCredentials($admin, 'test_temp_pass123');

        $latestNotif = Notification::query()
            ->where('user_id', $admin['id'])
            ->where('type', 'credentials')
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($latestNotif);
        $this->assertSame('sent', $latestNotif['status']);
        $this->assertStringContainsString('Your GWC Acadtrack Account Credentials', $latestNotif['title']);
    }

    public function testDeanAndAdminReviewWorkflow(): void
    {
        $term = AcademicTerm::getActive();
        $subject = Subject::first();
        $faculty = User::query()->where('role', 'Faculty')->first();
        $admin = User::query()->where('role', 'Admin')->first();
        $period = GradingPeriod::where('academic_term_id', $term['id'])->first();

        $this->assertNotNull($subject);
        $this->assertNotNull($period);

        $facultyId = $faculty ? (int) $faculty['id'] : (int) $admin['id'];
        $adminId = (int) $admin['id'];

        // Clean up any existing sheet for this composite key
        GradingSheet::where('faculty_id', $facultyId)
            ->where('subject_id', (int) $subject['id'])
            ->where('grading_period_id', (int) $period['id'])
            ->where('academic_term_id', (int) $term['id'])
            ->delete();

        // 1. Create a draft grading sheet
        $createdSheet = GradingSheet::create([
            'faculty_id' => $facultyId,
            'subject_id' => (int) $subject['id'],
            'grading_period_id' => (int) $period['id'],
            'academic_term_id' => (int) $term['id'],
            'status' => GradingSheet::STATUS_DRAFT,
        ]);

        $this->assertNotNull($createdSheet);
        $sheetId = (int) $createdSheet->id;
        $this->assertGreaterThan(0, $sheetId);

        $gradingService = new GradingService();

        // 2. Submit sheet
        $gradingService->submitGradingSheet($sheetId);
        $sheet = GradingSheet::find($sheetId);
        $this->assertSame(GradingSheet::STATUS_SUBMITTED, $sheet['status']);

        // 3. Approve sheet
        $gradingService->approveGradingSheet($sheetId, $adminId);
        $sheet = GradingSheet::find($sheetId);
        $this->assertSame(GradingSheet::STATUS_APPROVED, $sheet['status']);
        $this->assertEquals($adminId, (int) $sheet['approved_by']);
        $this->assertNotNull($sheet['approved_at']);

        // 4. Confirm & Finalize sheet with remarks
        $gradingService->confirmGradingSheet($sheetId, $adminId, 'Finalized and confirmed by Dean.');
        $sheet = GradingSheet::find($sheetId);
        $this->assertSame(GradingSheet::STATUS_FINALIZED, $sheet['status']);
        $this->assertSame('Finalized and confirmed by Dean.', $sheet['remarks']);
        $this->assertNotNull($sheet['confirmed_at']);

        // Clean up test sheet
        GradingSheet::query()->where('id', $sheetId)->delete();
    }
}
