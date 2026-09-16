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
        $dotenv->load();
        new \App\Core\Database(
            env('DB_HOST'),
            env('DB_DATABASE'),
            env('DB_USERNAME'),
            (string) env('DB_PASSWORD', '')
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

}

