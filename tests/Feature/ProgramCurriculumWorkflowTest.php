<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Department;
use App\Models\AcademicTerm;
use App\Controllers\Admin\ProgramCurriculumController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class ProgramCurriculumWorkflowTest extends TestCase
{
    private static array $cleanupSubjectIds = [];
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
            Subject::whereIn('id', self::$cleanupSubjectIds)->delete();
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
}
