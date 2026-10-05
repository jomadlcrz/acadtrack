<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Department;
use App\Models\AcademicTerm;
use App\Models\Grade;
use App\Models\GradingSheet;
use App\Models\Enrollment;
use App\Models\GradeHistoryLog;
use App\Models\Student;
use App\Models\User;
use App\Models\GradingPeriod;
use App\Controllers\Admin\ProgramCurriculumController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class ProgramCurriculumWorkflowTest extends TestCase
{
    private static array $cleanupSubjectIds = [];
    private static array $cleanupUserIds = [];
    private static ?Program $testProgram = null;

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

        $dept = Department::first();
        self::$testProgram = Program::firstOrCreate(
            ['program_abbrev' => 'TESTCURR'],
            [
                'program_name' => 'Curriculum Test Degree',
                'department_id' => $dept ? $dept->id : null,
                'program_type' => "Bachelor's Degree",
                'program_length' => '4 Years',
                'status' => 'active',
            ]
        );
    }

    public static function tearDownAfterClass(): void
    {
        if (!empty(self::$cleanupSubjectIds)) {
            \App\Core\Database::getConnection()->exec("DELETE FROM prerequisites WHERE subject_id IN (" . implode(',', self::$cleanupSubjectIds) . ") OR prerequisite_subject_id IN (" . implode(',', self::$cleanupSubjectIds) . ")");
            GradeHistoryLog::whereIn('grade_id', function ($query) {
                $query->select('id')->from('grades')->whereIn('subject_id', self::$cleanupSubjectIds);
            })->delete();
            Grade::whereIn('subject_id', self::$cleanupSubjectIds)->delete();
            Enrollment::whereIn('subject_id', self::$cleanupSubjectIds)->delete();
            Subject::whereIn('id', self::$cleanupSubjectIds)->delete();
        }
        if (!empty(self::$cleanupUserIds)) {
            Student::whereIn('user_id', self::$cleanupUserIds)->delete();
            User::whereIn('id', self::$cleanupUserIds)->delete();
        }
        if (self::$testProgram) {
            self::$testProgram->delete();
        }
    }

    public function testStoreSubjectAndPrerequisites(): void
    {
        $controller = new ProgramCurriculumController();
        $response = new Response();
        $session = new Session();

        $code = 'TCURR' . rand(100, 999);

        // Simulate POST request
        $_POST = [
            'program_id' => self::$testProgram->id,
            'program' => self::$testProgram->program_abbrev,
            'subject_code' => $code,
            'descriptive_title' => 'Curriculum Test Course',
            'units' => 3.0,
            'year_level' => 1,
            'semester' => 1,
            'subject_type' => 'Major with Lab',
            'prerequisites' => '',
        ];

        // Replace exit/header redirects by handling through buffer/session
        try {
            $controller->storeSubject(new Request(), $response, $session);
        } catch (\Throwable $e) {
            // redirect() calls exit in normal flow
        }

        $subject = Subject::where('program_id', self::$testProgram->id)
            ->where('subject_code', $code)
            ->first();

        $this->assertNotNull($subject);
        $this->assertSame($code, $subject->subject_code);
        $this->assertSame('Curriculum Test Course', $subject->descriptive_title);
        $this->assertSame(0, (int) $subject->is_archived);

        self::$cleanupSubjectIds[] = $subject->id;

        // Test Update
        $_POST = [
            'program' => self::$testProgram->program_abbrev,
            'subject_code' => $code,
            'descriptive_title' => 'Updated Curriculum Title',
            'units' => 4.0,
            'year_level' => 2,
            'semester' => 2,
            'subject_type' => 'GenEd Elective',
            'prerequisites' => '',
        ];

        try {
            $controller->updateSubject(new Request(), $response, $session, (string) $subject->id);
        } catch (\Throwable $e) {
            // redirect() calls exit
        }

        $updated = Subject::find($subject->id);
        $this->assertSame('Updated Curriculum Title', $updated->descriptive_title);
        $this->assertEquals(4.0, (float) $updated->units);
        $this->assertEquals(2, (int) $updated->year_level);
        $this->assertEquals(2, (int) $updated->semester);

        // Test Archive
        $_POST = [
            'program' => self::$testProgram->program_abbrev,
        ];

        try {
            $controller->archiveSubject(new Request(), $response, $session, (string) $subject->id);
        } catch (\Throwable $e) {
            // redirect() calls exit
        }

        $archived = Subject::find($subject->id);
        $this->assertSame(1, (int) $archived->is_archived);
        $this->assertNotNull($archived->archived_at);
    }

    public function testAssociateDegreeEnforcesTwoYearsAndDisallowsFiveYears(): void
    {
        $controller = new ProgramCurriculumController();
        $response = new Response();
        $session = new Session();

        // 1. Associate Degree should always be 2 Years even if another length is sent
        $_POST = [
            'program_name' => 'Associate in Computer Technology',
            'program_abbrev' => 'ACT_TEST',
            'program_type' => 'Associate Degree',
            'program_length' => '4 Years',
            'subjects' => [],
        ];

        try {
            $controller->store(new Request(), $response, $session);
        } catch (\Throwable $e) {
            // redirect() calls exit
        }

        $act = Program::where('program_abbrev', 'ACT_TEST')->first();
        $this->assertNotNull($act);
        $this->assertSame('Associate Degree', $act->program_type);
        $this->assertSame('2 Years', $act->program_length);
        $act->delete();

        // 2. Bachelor's Degree with attempted 5 Years should normalize to 4 Years
        $_POST = [
            'program_name' => 'Bachelor of Technology Test',
            'program_abbrev' => 'BTECH_TEST',
            'program_type' => "Bachelor's Degree",
            'program_length' => '5 Years',
            'subjects' => [],
        ];

        try {
            $controller->store(new Request(), $response, $session);
        } catch (\Throwable $e) {
            // redirect() calls exit
        }

        $btech = Program::where('program_abbrev', 'BTECH_TEST')->first();
        $this->assertNotNull($btech);
        $this->assertSame("Bachelor's Degree", $btech->program_type);
        $this->assertSame('4 Years', $btech->program_length);
        $btech->delete();
    }

    public function testSubjectArchiveConstraintsPreventArchivingWithGradesPrerequisitesEnrollments(): void
    {
        $term = AcademicTerm::getActive();
        $termId = (int) ($term['id'] ?? 1);

        $controller = new ProgramCurriculumController();
        $request = new Request();
        $response = new Response();
        $session = new Session();

        // 1. Subject with recorded grades
        $subGrade = Subject::create([
            'program_id' => self::$testProgram->id,
            'subject_code' => 'TGRD' . rand(100, 999),
            'descriptive_title' => 'Subject With Grades',
            'units' => 3.0,
            'year_level' => 1,
            'semester' => 1,
            'academic_term_id' => $termId,
        ]);
        self::$cleanupSubjectIds[] = $subGrade->id;

        $user = User::create([
            'first_name' => 'GradeSub',
            'last_name' => 'Student',
            'email' => 'gradesub_' . time() . '_' . rand(100, 999) . '@gwc.edu',
            'password' => password_hash('secret123', PASSWORD_BCRYPT),
            'role' => 'Student',
            'status' => 'active',
        ]);
        self::$cleanupUserIds[] = $user->id;

        $student = Student::create([
            'user_id' => $user->id,
            'year_level' => 1,
            'status' => 'Regular',
        ]);

        $period = GradingPeriod::where('academic_term_id', $termId)->first();
        $periodId = (int) ($period->id ?? 1);

        Grade::saveGrade(
            (int) $student->id,
            (int) $subGrade->id,
            $periodId,
            $termId,
            88.50
        );

        $_POST = ['program' => self::$testProgram->program_abbrev];
        $controller->archiveSubject($request, $response, $session, (string) $subGrade->id);
        $subGrade->refresh();
        $this->assertSame(0, (int) $subGrade->is_archived);
        $this->assertStringContainsString('recorded student grade(s)', (string) $session->getFlash('error'));

        // 2. Subject with active enrollment
        $subEnroll = Subject::create([
            'program_id' => self::$testProgram->id,
            'subject_code' => 'TENR' . rand(100, 999),
            'descriptive_title' => 'Subject With Enrollments',
            'units' => 3.0,
            'year_level' => 1,
            'semester' => 1,
            'academic_term_id' => $termId,
        ]);
        self::$cleanupSubjectIds[] = $subEnroll->id;

        Enrollment::create([
            'student_id' => $student->id,
            'subject_id' => $subEnroll->id,
            'academic_term_id' => $termId,
        ]);

        $controller->archiveSubject($request, $response, $session, (string) $subEnroll->id);
        $subEnroll->refresh();
        $this->assertSame(0, (int) $subEnroll->is_archived);
        $this->assertStringContainsString('active student enrollment(s)', (string) $session->getFlash('error'));

        // 3. Subject as active prerequisite for another active curriculum subject
        $prereqSub = Subject::create([
            'program_id' => self::$testProgram->id,
            'subject_code' => 'TPREQ' . rand(100, 999),
            'descriptive_title' => 'Prerequisite Subject',
            'units' => 3.0,
            'year_level' => 1,
            'semester' => 1,
            'academic_term_id' => $termId,
        ]);
        self::$cleanupSubjectIds[] = $prereqSub->id;

        $dependentSub = Subject::create([
            'program_id' => self::$testProgram->id,
            'subject_code' => 'TDEP' . rand(100, 999),
            'descriptive_title' => 'Dependent Subject',
            'units' => 3.0,
            'year_level' => 2,
            'semester' => 1,
            'academic_term_id' => $termId,
        ]);
        self::$cleanupSubjectIds[] = $dependentSub->id;

        \App\Core\Database::getConnection()->exec("
            INSERT INTO prerequisites (subject_id, prerequisite_subject_id)
            VALUES ({$dependentSub->id}, {$prereqSub->id})
        ");

        $controller->archiveSubject($request, $response, $session, (string) $prereqSub->id);
        $prereqSub->refresh();
        $this->assertSame(0, (int) $prereqSub->is_archived);
        $errorMsg = (string) $session->getFlash('error');
        $this->assertStringContainsString('required prerequisite for active curriculum subject(s)', $errorMsg);
        $this->assertStringContainsString($dependentSub->subject_code, $errorMsg);
    }
}
