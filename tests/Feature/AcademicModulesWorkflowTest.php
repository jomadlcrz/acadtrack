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
        $this->assertStringContainsString('Sets', $html);
        $this->assertStringContainsString('Create Sections', $html);
        $this->assertStringContainsString('All Programs', $html);
        $this->assertStringContainsString('All Years', $html);

        // Dean read-only verification
        $_SESSION['user'] = ['role' => 'Dean', 'first_name' => 'College', 'last_name' => 'Dean'];
        $deanHtml = (new View())->render('admin.sets.index', [
            'sets' => $sets,
            'programs' => $programs,
            'departments' => $departments,
            'activeTerm' => $term,
            'isReadOnly' => true,
        ]);
        $this->assertStringContainsString('Read-Only View', $deanHtml);
        $this->assertStringNotContainsString('Create Sections', $deanHtml);
        $this->assertStringNotContainsString('selectAllCheckbox', $deanHtml);
        $this->assertStringNotContainsString('Archive Selected', $deanHtml);
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

    public function testDeanFacultyAssignmentsViewRendering(): void
    {
        $_SESSION['user'] = ['role' => 'Dean', 'first_name' => 'College', 'last_name' => 'Dean'];

        $term = AcademicTerm::getActive();
        $subjectRepo = new \App\Repositories\SubjectRepository();
        $facultyRepo = new \App\Repositories\FacultyRepository();

        $subjects = $subjectRepo->getByDean((int)$term['id']);
        $faculty = \App\Models\User::getFaculty();
        $assignments = $facultyRepo->getAssignmentsForTerm((int)$term['id']);

        $assignmentsBySubject = [];
        $assignmentsByFaculty = [];
        foreach ($assignments as $a) {
            $assignmentsBySubject[$a['subject_id']][] = $a;
            $assignmentsByFaculty[$a['faculty_id']][] = $a;
        }

        foreach ($subjects as &$s) {
            $s['assigned_faculty'] = $assignmentsBySubject[$s['id']] ?? [];
            $s['assigned_faculty_count'] = count($s['assigned_faculty']);
        }
        unset($s);

        foreach ($faculty as &$f) {
            $f['assigned_count'] = count($assignmentsByFaculty[$f['id']] ?? []);
        }
        unset($f);

        $html = (new View())->render('dean.faculty-assignments.index', [
            'subjects' => $subjects,
            'faculty' => $faculty,
            'academicTerm' => $term,
            'selectedSemester' => (string)($term['semester'] ?? '1'),
            'totalAssignments' => count($assignments),
        ]);

        $this->assertNotEmpty($html);
        $this->assertStringContainsString('Faculty Subject Assignments', $html);
        $this->assertStringContainsString('Assign Instructor to Course', $html);
        $this->assertStringContainsString('1st Year Curriculum', $html);
        $this->assertStringContainsString('Faculty Teaching Workload Summary', $html);
        $this->assertStringContainsString('assignmentSearch', $html);
        $this->assertStringContainsString('yearLevelFilter', $html);
        $this->assertStringContainsString('statusFilter', $html);
    }

    public function testSettingsViewRendersAcademicTermSelectDropdown(): void
    {
        $_SESSION['user'] = ['role' => 'Admin', 'first_name' => 'Admin', 'last_name' => 'User'];

        $term = AcademicTerm::getActive();
        $termsStmt = \App\Core\Database::getConnection()->query("
            SELECT at.*, 
                   COALESCE(at.school_year, ay.school_year, '2026-2027') as school_year_display
            FROM academic_terms at
            LEFT JOIN academic_years ay ON at.academic_year_id = ay.id
            WHERE at.is_archived = 0
            ORDER BY at.semester ASC
        ");
        $allTerms = $termsStmt->fetchAll(\PDO::FETCH_ASSOC);

        $html = (new View())->render('admin.settings.index', [
            'term' => $term,
            'allTerms' => $allTerms,
            'settings' => [
                'grading_method' => 'zero_based',
                'prelim_weight' => 20.00,
                'midterm_weight' => 20.00,
                'semi_final_weight' => 20.00,
                'final_weight' => 40.00,
            ],
        ]);

        $this->assertNotEmpty($html);
        $this->assertStringContainsString('Active academic term & school year', $html);
        $this->assertStringContainsString('<select class="form-select" id="academic_term_id"', $html);
        $this->assertStringContainsString('/admin/academic-terms', $html);
    }
}
