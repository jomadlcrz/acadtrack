<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\User;
use App\Controllers\Admin\DepartmentController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class DepartmentWorkflowTest extends TestCase
{
    private static array $cleanupUserIds = [];
    private static array $cleanupDeptIds = [];

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
        Department::whereIn('dept_abbrev', ['CITE'])->update(['status' => 'active']);
    }

    public static function tearDownAfterClass(): void
    {
        if (!empty(self::$cleanupUserIds)) {
            Faculty::whereIn('user_id', self::$cleanupUserIds)->delete();
            User::whereIn('id', self::$cleanupUserIds)->delete();
        }

        if (!empty(self::$cleanupDeptIds)) {
            Department::whereIn('id', self::$cleanupDeptIds)->delete();
        }
    }

    public function testBaselineDepartmentsExist(): void
    {
        $cite = Department::where('name', 'College of Information Technology Education')->first();
        $this->assertNotNull($cite);
        $this->assertSame('College of Information Technology Education', $cite->name);

        $activeDepts = Department::getActive();
        $this->assertGreaterThanOrEqual(1, count($activeDepts));
    }

    public function testFacultyBelongsToDepartmentRelationship(): void
    {
        $dept = Department::findByCode('CITE');
        $this->assertNotNull($dept);

        $unique = time() . '_' . rand(1000, 9999);
        $user = User::create([
            'first_name' => 'Prof',
            'last_name' => "Instructor_{$unique}",
            'email' => "prof_{$unique}@gwc.edu",
            'password' => password_hash('secret123', PASSWORD_BCRYPT),
            'role' => 'Faculty',
            'status' => 'active',
            'force_password_change' => 0,
        ]);
        self::$cleanupUserIds[] = $user->id;

        $faculty = Faculty::create([
            'user_id' => $user->id,
            'department_id' => $dept->id,
        ]);

        // Test Faculty -> Department
        $loadedFaculty = Faculty::with('department')->find($faculty->id);
        $this->assertNotNull($loadedFaculty);
        $this->assertNotNull($loadedFaculty->department);
        $this->assertSame($dept->code, $loadedFaculty->department->code);

        // Test Department -> Faculty
        $loadedDept = Department::with('faculty')->find($dept->id);
        $this->assertTrue($loadedDept->faculty->contains('id', $faculty->id));
    }

    public function testUserGetFacultyEagerLoadsDepartment(): void
    {
        $facultyList = User::getFaculty();
        $this->assertNotEmpty($facultyList);

        foreach ($facultyList as $facUser) {
            $this->assertSame('Faculty', $facUser['role']);
            if (isset($facUser['faculty']['department'])) {
                $this->assertIsArray($facUser['faculty']['department']);
                $this->assertArrayHasKey('name', $facUser['faculty']['department']);
            }
        }
    }

    public function testDepartmentCreationValidationAndDeletion(): void
    {
        $code = 'TEST' . rand(100, 999);
        $dept = Department::create([
            'code' => $code,
            'name' => 'Temporary Test Department',
            'description' => 'Test department for automated suite',
            'status' => 'active',
        ]);
        self::$cleanupDeptIds[] = $dept->id;

        $this->assertNotNull($dept->id);
        $this->assertSame($code, $dept->code);

        // Verify duplicate code throws QueryException
        $this->expectException(\Illuminate\Database\QueryException::class);
        Department::create([
            'code' => $code,
            'name' => 'Another department with duplicate code',
            'status' => 'active',
        ]);
    }

    public function testDepartmentViewRendering(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user'] = ['role' => 'Admin', 'first_name' => 'Admin', 'last_name' => 'User'];

        $departments = Department::withCount('faculty')
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();

        $html = (new \App\Core\View())->render('admin.departments.index', [
            'departments' => $departments,
        ]);

        $this->assertNotEmpty($html);
        $this->assertStringContainsString('Departments', $html);
        $this->assertStringContainsString('College of Information Technology Education', $html);
        $this->assertStringContainsString('Department abbrev', $html);
        $this->assertStringContainsString('e.g., CITE', $html);
        $this->assertStringContainsString('/admin/departments/' . ($departments[0]['id'] ?? 1), $html);
        $this->assertStringContainsString('data-href=', $html);
    }

    public function testDepartmentShowViewRendering(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user'] = ['role' => 'Admin', 'first_name' => 'Admin', 'last_name' => 'User'];

        $dept = Department::with(['programs', 'faculty.user.facultyDetail'])
            ->withCount('faculty')
            ->first();
        $this->assertNotNull($dept);

        $html = (new \App\Core\View())->render('admin.departments.show', [
            'department' => $dept->toArray(),
        ]);

        $this->assertNotEmpty($html);
        $this->assertStringContainsString($dept->name, $html);
        $this->assertStringContainsString($dept->code, $html);
        $this->assertStringContainsString('Academic Programs', $html);
        $this->assertStringContainsString('Assigned Faculty', $html);
        $this->assertStringContainsString('Back to departments', $html);
    }

    public function testDepartmentArchiveConstraints(): void
    {
        $uniqueCode = 'DARC' . rand(100, 999);
        $dept = Department::create([
            'code' => $uniqueCode,
            'name' => 'Department Archive Constraint Test ' . $uniqueCode,
            'description' => 'Test archive constraints',
            'status' => 'active',
        ]);
        self::$cleanupDeptIds[] = $dept->id;

        $controller = new DepartmentController();
        $request = new Request();
        $response = new Response();
        $session = new Session();

        // 1. With an active program, archiving department must be blocked
        $program = Program::create([
            'program_name' => 'Dept Constraint Program ' . $uniqueCode,
            'program_abbrev' => 'DCP' . rand(100, 999),
            'department_id' => $dept->id,
            'program_type' => "Bachelor's Degree",
            'program_length' => '4 Years',
            'status' => 'active',
        ]);

        $controller->archive($request, $response, $session, (string) $dept->id);
        $dept->refresh();
        $this->assertSame('active', $dept->status);
        $this->assertStringContainsString('It still has 1 active program(s)', (string) $session->getFlash('error'));

        // Remove the program
        $program->delete();

        // 2. With an assigned faculty member, archiving department must be blocked
        $user = User::create([
            'first_name' => 'DeptFac',
            'last_name' => 'Constraint_' . $uniqueCode,
            'email' => "dept_fac_{$uniqueCode}@gwc.edu",
            'password' => password_hash('secret123', PASSWORD_BCRYPT),
            'role' => 'Faculty',
            'status' => 'active',
        ]);
        self::$cleanupUserIds[] = $user->id;

        $faculty = Faculty::create([
            'user_id' => $user->id,
            'department_id' => $dept->id,
        ]);

        $controller->archive($request, $response, $session, (string) $dept->id);
        $dept->refresh();
        $this->assertSame('active', $dept->status);
        $this->assertStringContainsString('It still has 1 assigned faculty member(s)', (string) $session->getFlash('error'));

        // Remove faculty
        $faculty->delete();

        // 3. With no dependents, archiving must succeed
        $controller->archive($request, $response, $session, (string) $dept->id);
        $dept->refresh();
        $this->assertSame('inactive', $dept->status);
        $this->assertStringContainsString('archived successfully', (string) $session->getFlash('success'));

        // 4. Restore must succeed
        $controller->restore($request, $response, $session, (string) $dept->id);
        $dept->refresh();
        $this->assertSame('active', $dept->status);
        $this->assertStringContainsString('restored to active status', (string) $session->getFlash('success'));
    }
}
