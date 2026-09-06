<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\Faculty;
use App\Models\Subject;
use App\Models\AcademicTerm;
use App\Models\GradingPeriod;
use App\Models\GradingSetting;
use App\Models\GradingSheet;
use App\Models\Grade;
use App\Models\Notification;
use App\Services\AuthService;
use App\Services\GradingService;
use App\Services\GradeService;
use App\Services\EvaluationService;
use App\Services\NotificationService;
use App\Repositories\FacultyRepository;
use App\Repositories\StudentRepository;
use App\Repositories\GradeRepository;

class CompleteWorkflowEndToEndTest extends TestCase
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

    public function testCompleteTwelveStepOperationalWorkflow(): void
    {
        // -------------------------------------------------------------
        // STEP 1: LOGIN & ROLE-BASED DISPATCH
        // -------------------------------------------------------------
        $authService = new AuthService();
        $this->assertSame('/admin/dashboard', $authService->getDashboardRoute('Admin'));
        $this->assertSame('/dean/dashboard', $authService->getDashboardRoute('Dean'));
        $this->assertSame('/faculty/dashboard', $authService->getDashboardRoute('Faculty'));
        $this->assertSame('/student/dashboard', $authService->getDashboardRoute('Student'));

        $admin = User::where('role', 'Admin')->first();
        $dean = User::where('role', 'Dean')->first();
        $facultyUser = User::where('role', 'Faculty')->first();
        $this->assertNotNull($admin, 'Admin account must exist');
        $this->assertNotNull($dean, 'Dean account must exist');
        $this->assertNotNull($facultyUser, 'Faculty account must exist');

        // -------------------------------------------------------------
        // STEP 2: DEAN ASSIGNS SUBJECTS
        // -------------------------------------------------------------
        $term1 = AcademicTerm::where('semester', '1')->first();
        $this->assertNotNull($term1);
        $termId = (int) $term1->id;

        $subject = Subject::where('academic_term_id', $termId)->first();
        $this->assertNotNull($subject);
        $subjectId = (int) $subject->id;

        $facultyRepo = new FacultyRepository();
        $facultyRepo->assignSubject((int) $facultyUser->id, $subjectId, $termId);

        $assignedSubjects = Faculty::getAssignedSubjects((int) $facultyUser->id, $termId);
        $assignedIds = array_column($assignedSubjects, 'id');
        $this->assertContains($subjectId, $assignedIds, 'Assigned subject must appear in faculty instructional load');

        // -------------------------------------------------------------
        // STEP 3: FACULTY SETS UP SUBJECT NATURE & GRADING SETTINGS
        // -------------------------------------------------------------
        Subject::where('id', $subjectId)->update(['nature' => 'Combined']);
        $reloadedSubj = Subject::find($subjectId);
        $this->assertSame('Combined', $reloadedSubj->nature);

        // Configure grading setting: 50-Based, weights totaling 100%
        GradingSetting::where('subject_id', $subjectId)->where('academic_term_id', $termId)->delete();
        $setting = GradingSetting::create([
            'academic_term_id' => $termId,
            'faculty_id' => (int) $facultyUser->id,
            'subject_id' => $subjectId,
            'grading_method' => 'fifty_based',
            'prelim_weight' => 20.00,
            'midterm_weight' => 20.00,
            'semi_final_weight' => 20.00,
            'final_weight' => 40.00,
        ]);
        $this->assertNotNull($setting);
        $totalWeight = $setting->prelim_weight + $setting->midterm_weight + $setting->semi_final_weight + $setting->final_weight;
        $this->assertEquals(100.00, (float) $totalWeight, 'Grading period weights must total exactly 100%');

        // -------------------------------------------------------------
        // STEP 4: FACULTY ADDS / SELECTS STUDENTS
        // -------------------------------------------------------------
        $testStudentNum = 'TEST-' . time();
        $testEmail = 'student_' . time() . '@gwc.edu';

        $studentUser = User::create([
            'first_name' => 'Automated',
            'last_name' => 'Tester',
            'student_number' => $testStudentNum,
            'email' => $testEmail,
            'password' => password_hash('secret123', PASSWORD_BCRYPT),
            'role' => 'Student',
            'status' => 'active',
        ]);

        $student = Student::create([
            'user_id' => (int) $studentUser->id,
            'year_level' => 3, // 3rd Year
            'status' => 'Irregular', // Irregular
        ]);

        $this->assertSame('Irregular', $student->status);
        $this->assertEquals(3, (int) $student->year_level);

        // Enroll student into course section
        Student::enroll((int) $student->id, $subjectId, $termId);

        $studentRepo = new StudentRepository();
        $roster = $studentRepo->getBySubject($subjectId, $termId);
        $rosterIds = array_column($roster, 'id');
        $this->assertContains((int) $student->id, $rosterIds, 'Enrolled student must appear on class roster');

        // -------------------------------------------------------------
        // STEP 5: SELECT SEMESTER (1st / 2nd SEMESTER SEPARATION)
        // -------------------------------------------------------------
        $term2 = AcademicTerm::where('semester', '2')->first();
        $this->assertNotNull($term2);
        $this->assertNotEquals($term1->id, $term2->id);

        $periods1 = GradingPeriod::where('academic_term_id', $term1->id)->get();
        $periods2 = GradingPeriod::where('academic_term_id', $term2->id)->get();
        $this->assertCount(4, $periods1);
        $this->assertCount(4, $periods2);

        // -------------------------------------------------------------
        // STEP 6: FACULTY ENTERS GRADES
        // -------------------------------------------------------------
        $prelimPeriod = $periods1->firstWhere('order_num', 1);
        $this->assertNotNull($prelimPeriod);
        $periodId = (int) $prelimPeriod->id;

        $gradingService = new GradingService();
        $studentGrades = [
            (int) $student->id => 88.50,
        ];

        // -------------------------------------------------------------
        // STEP 7: SAVE AND REVIEW (DRAFT STATE)
        // -------------------------------------------------------------
        GradingSheet::where('faculty_id', (int) $facultyUser->id)
            ->where('subject_id', $subjectId)
            ->where('grading_period_id', $periodId)
            ->where('academic_term_id', $termId)
            ->delete();

        $gradingService->saveGrades(
            (int) $facultyUser->id,
            $subjectId,
            $periodId,
            $termId,
            $studentGrades
        );

        $sheet = GradingSheet::where('faculty_id', (int) $facultyUser->id)
            ->where('subject_id', $subjectId)
            ->where('grading_period_id', $periodId)
            ->where('academic_term_id', $termId)
            ->first();

        $this->assertNotNull($sheet);
        $this->assertSame(GradingSheet::STATUS_DRAFT, $sheet->status, 'Saved grades must start in DRAFT status');

        // -------------------------------------------------------------
        // STEP 8: SUBMIT GRADING SHEET
        // -------------------------------------------------------------
        $gradingService->submitGradingSheet((int) $sheet->id);
        $sheet->refresh();
        $this->assertSame(GradingSheet::STATUS_SUBMITTED, $sheet->status, 'Submitted sheet must have SUBMITTED status');

        // -------------------------------------------------------------
        // STEP 9: DEAN / ADMIN REVIEW (VIEW, EDIT, APPROVE, CONFIRM)
        // -------------------------------------------------------------
        // Dean edits student grade if needed
        $gradeRepo = new GradeRepository();
        $gradeRepo->saveGrade((int) $student->id, $subjectId, $periodId, $termId, 92.00);

        $recordedGrade = Grade::where('student_id', (int) $student->id)
            ->where('subject_id', $subjectId)
            ->where('grading_period_id', $periodId)
            ->first();
        $this->assertEquals(92.00, (float) $recordedGrade->grade);

        // Dean approves
        $gradingService->approveGradingSheet((int) $sheet->id, (int) $dean->id);
        $sheet->refresh();
        $this->assertSame(GradingSheet::STATUS_APPROVED, $sheet->status);

        // Dean confirms & finalizes
        $gradingService->confirmGradingSheet((int) $sheet->id, (int) $dean->id, 'Officially verified and approved by College Dean.');
        $sheet->refresh();
        $this->assertSame(GradingSheet::STATUS_FINALIZED, $sheet->status);
        $this->assertSame('Officially verified and approved by College Dean.', $sheet->remarks);

        // -------------------------------------------------------------
        // STEP 10: GRADE FINALIZATION
        // -------------------------------------------------------------
        $this->assertSame(GradingSheet::STATUS_FINALIZED, $sheet->status);

        // -------------------------------------------------------------
        // STEP 11: EMAIL NOTIFICATION
        // -------------------------------------------------------------
        $notifService = new NotificationService();
        $notifService->sendStudentCredentials($studentUser->toArray(), 'secret123');
        $notifService->sendGradesPublished($subjectId, $termId, $prelimPeriod->name);

        $credentialsNotif = Notification::where('user_id', (int) $studentUser->id)
            ->where('type', 'credentials')
            ->first();
        $this->assertNotNull($credentialsNotif, 'Credentials notification must be created');

        $gradesNotif = Notification::where('type', 'grades_published')->orderBy('id', 'DESC')->first();
        $this->assertNotNull($gradesNotif, 'Grade publication notification must be dispatched');

        // -------------------------------------------------------------
        // STEP 12: STUDENT VIEWS EVALUATION (SEMESTER & WHOLE EVALUATION)
        // -------------------------------------------------------------
        $gradeService = new GradeService();
        $visibleGrades = $gradeService->getStudentGrades((int) $student->id, $termId);
        $this->assertNotEmpty($visibleGrades, 'Finalized/Approved grades must be visible to student');

        $evalService = new EvaluationService();
        $evaluation = $evalService->calculateEvaluation(['Prelim' => 92.00]);
        $this->assertEquals(92.00, (float) $evaluation['average']);
        $this->assertSame('Excellent', $evaluation['status']);

        // Clean up test data
        Notification::where('user_id', (int) $studentUser->id)->delete();
        Grade::where('student_id', (int) $student->id)->delete();
        \App\Models\Enrollment::where('student_id', (int) $student->id)->delete();
        $student->delete();
        $studentUser->delete();
        $sheet->delete();
        $setting->delete();
    }
}
