<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Models\AcademicTerm;
use App\Models\Department;
use App\Models\Program;
use App\Models\Curriculum;
use App\Models\CurriculumSubject;
use App\Models\Set;
use App\Core\View;

class AcademicModulesWorkflowTest extends TestCase
{
    private static array $cleanupProgramIds = [];
    private static array $cleanupCurriculumIds = [];
    private static array $cleanupSetIds = [];

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

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (!empty(self::$cleanupSetIds)) {
            Set::whereIn('id', self::$cleanupSetIds)->delete();
        }
        if (!empty(self::$cleanupCurriculumIds)) {
            CurriculumSubject::whereIn('curriculum_id', self::$cleanupCurriculumIds)->delete();
            Curriculum::whereIn('id', self::$cleanupCurriculumIds)->delete();
        }
        if (!empty(self::$cleanupProgramIds)) {
            Program::whereIn('id', self::$cleanupProgramIds)->delete();
        }
    }

    public function testAcademicTermsRendering(): void
    {
        $_SESSION['user'] = ['role' => 'Admin', 'first_name' => 'Admin', 'last_name' => 'User'];

        $terms = AcademicTerm::getAll();
        $this->assertIsArray($terms);

        $html = (new View())->render('admin.academic_terms.index', [
            'terms' => $terms,
        ]);

        $this->assertNotEmpty($html);
        $this->assertStringContainsString('Academic Terms', $html);
        $this->assertStringContainsString('Create Academic Term', $html);
        $this->assertStringContainsString('School Year', $html);
        $this->assertStringContainsString('Semester', $html);
    }

    public function testProgramCurriculaRenderingAndRelations(): void
    {
        $_SESSION['user'] = ['role' => 'Admin', 'first_name' => 'Admin', 'last_name' => 'User'];

        $programs = Program::with(['department', 'activeCurriculum'])->orderBy('program_abbrev', 'asc')->get();
        $this->assertTrue($programs->isNotEmpty(), 'Programs catalog should not be empty');

        $bsit = $programs->firstWhere('program_abbrev', 'BSIT');
        $this->assertNotNull($bsit, 'BSIT program must exist');
        $this->assertEquals('Bachelor of Science in Information Technology', $bsit->program_name);

        $curriculum = Curriculum::where('program_id', $bsit->id)->first();
        $this->assertNotNull($curriculum, 'BSIT curriculum should exist');

        $subjects = CurriculumSubject::where('curriculum_id', $curriculum->id)->get();
        $this->assertTrue($subjects->isNotEmpty(), 'BSIT curriculum should have subjects');

        $html = (new View())->render('admin.program_curricula.index', [
            'programs' => $programs,
            'selectedProgram' => $bsit,
            'selectedAbbrev' => 'BSIT',
            'curriculum' => $curriculum,
            'groupedSubjects' => [
                [
                    'year_level' => 'First Year',
                    'semester' => 1,
                    'title' => 'First Year · 1st Semester',
                    'subjects' => $subjects,
                    'total_units' => 18.0,
                ]
            ],
            'totalSubjects' => $subjects->count(),
            'totalUnits' => 18.0,
        ]);

        $this->assertNotEmpty($html);
        $this->assertStringContainsString('Program Curricula', $html);
        $this->assertStringContainsString('Bachelor of Science in Information Technology', $html);
        $this->assertStringContainsString('New Curriculum', $html);
        $this->assertStringContainsString('Print', $html);
        $this->assertStringContainsString('CSV / Excel', $html);
    }

    public function testSetsViewAndBatchDerivation(): void
    {
        $_SESSION['user'] = ['role' => 'Admin', 'first_name' => 'Admin', 'last_name' => 'User'];

        // Test section naming derivation
        $name1A = Set::deriveSetName('BSIT', 1, 'A');
        $name2B = Set::deriveSetName('BSCS', 2, 'B');
        $this->assertEquals('BSIT-1A', $name1A);
        $this->assertEquals('BSCS-2B', $name2B);

        $term = AcademicTerm::getActive();
        $termId = (int) ($term['id'] ?? 1);

        $sets = Set::where('academic_term_id', $termId)
            ->with(['program', 'department'])
            ->withCount('students')
            ->get();

        $programs = Program::where('status', 'active')->get();
        $departments = Department::where('status', 'active')->get();

        $html = (new View())->render('admin.sets.index', [
            'sets' => $sets,
            'programs' => $programs,
            'departments' => $departments,
            'activeTerm' => $term,
        ]);

        $this->assertNotEmpty($html);
        $this->assertStringContainsString('Sets (Sections)', $html);
        $this->assertStringContainsString('Create Sections', $html);
        $this->assertStringContainsString('All Programs', $html);
        $this->assertStringContainsString('All Years', $html);
    }

    public function testProgramCurriculaCreateWizardView(): void
    {
        $_SESSION['user'] = ['role' => 'Admin', 'first_name' => 'Admin', 'last_name' => 'User'];

        $departments = Department::where('status', 'active')->get();
        $existingPrograms = Program::orderBy('program_abbrev', 'asc')->get();

        $html = (new View())->render('admin.program_curricula.create', [
            'departments' => $departments,
            'existingPrograms' => $existingPrograms,
        ]);

        $this->assertNotEmpty($html);
        $this->assertStringContainsString('Create Program Curriculum', $html);
        $this->assertStringContainsString('Step 1: Program Information', $html);
        $this->assertStringContainsString('Step 2: Curriculum Subjects', $html);
        $this->assertStringContainsString('Step 3: Review &amp; Finalize', $html);
    }
}
