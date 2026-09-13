<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\User;

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
        Department::whereIn('dept_abbrev', ['CIT', 'CS', 'CBA', 'CAS', 'COE'])->update(['status' => 'active']);
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
        $cit = Department::where('name', 'College of Information Technology')->first();
        $this->assertNotNull($cit);
        $this->assertSame('College of Information Technology', $cit->name);

        $cs = Department::findByCode('CS');
        $this->assertNotNull($cs);
        $this->assertSame('Department of Computer Studies', $cs->name);

        $activeDepts = Department::getActive();
        $this->assertGreaterThanOrEqual(5, count($activeDepts));
    }

    public function testFacultyBelongsToDepartmentRelationship(): void
    {
        $dept = Department::findByCode('CS');
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
        $this->assertStringContainsString('Institutional Departments', $html);
        $this->assertStringContainsString('College of Information Technology', $html);
        $this->assertStringContainsString('Department code', $html);
    }
}
