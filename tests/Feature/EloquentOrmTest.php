<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\Faculty;
use App\Models\Subject;
use App\Models\AcademicTerm;
use App\Models\Department;
use App\Models\GradingPeriod;
use App\Models\GradingSheet;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Capsule\Manager as Capsule;

class EloquentOrmTest extends TestCase
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

    public function testEloquentCapsuleIsBooted(): void
    {
        $capsule = \App\Core\Database::getCapsule();
        $this->assertInstanceOf(Capsule::class, $capsule);
        $this->assertNotNull($capsule->getConnection());
    }

    public function testEloquentModelQueriesAndCollections(): void
    {
        $departments = Department::where('status', 'active')->get();
        $this->assertInstanceOf(Collection::class, $departments);
        $this->assertGreaterThanOrEqual(1, $departments->count());

        // Test Collection methods (pluck, first)
        $codes = $departments->pluck('dept_abbrev')->all();
        $this->assertIsArray($codes);
        $this->assertNotEmpty($codes);

        // Test Model instance property and array access
        $firstDept = $departments->first();
        $this->assertInstanceOf(Department::class, $firstDept);
        $this->assertSame($firstDept->dept_abbrev, $firstDept['dept_abbrev']);
    }

    public function testEloquentRelationships(): void
    {
        // 1. User -> Student (hasOne) & Student -> User (belongsTo)
        $studentUser = User::where('role', 'Student')->first();
        if ($studentUser) {
            $student = $studentUser->student;
            if ($student) {
                $this->assertInstanceOf(Student::class, $student);
                $this->assertSame((int) $studentUser->id, (int) $student->user_id);
                $this->assertSame((int) $studentUser->id, (int) $student->user->id);
            }
        }

        // 2. User -> Faculty (hasOne) & Faculty -> User (belongsTo)
        $facultyUser = User::where('role', 'Faculty')->first();
        if ($facultyUser) {
            $faculty = $facultyUser->faculty;
            if ($faculty) {
                $this->assertInstanceOf(Faculty::class, $faculty);
                $this->assertSame((int) $facultyUser->id, (int) $faculty->user_id);
                $this->assertSame((int) $facultyUser->id, (int) $faculty->user->id);
            }
        }

        // 3. AcademicTerm -> GradingPeriods (hasMany)
        $term = AcademicTerm::where('semester', '1')->first();
        $this->assertNotNull($term);
        $periods = $term->gradingPeriods;
        $this->assertInstanceOf(Collection::class, $periods);
        $this->assertCount(4, $periods);
    }

    public function testEloquentEagerLoading(): void
    {
        $termWithRelations = AcademicTerm::with(['gradingPeriods', 'subjects'])->where('semester', '1')->first();
        $this->assertNotNull($termWithRelations);
        $this->assertTrue($termWithRelations->relationLoaded('gradingPeriods'));
        $this->assertTrue($termWithRelations->relationLoaded('subjects'));
        $this->assertCount(4, $termWithRelations->gradingPeriods);
    }

    public function testEloquentCrudLifecycle(): void
    {
        // Create
        $user = User::create([
            'student_number' => 'TEST-ELOQUENT-' . time(),
            'first_name' => 'Eloquent',
            'last_name' => 'Tester',
            'email' => 'eloquent.tester.' . time() . '@gwc.edu',
            'password' => password_hash('secret123', PASSWORD_BCRYPT),
            'role' => 'Student',
            'status' => 'active',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertGreaterThan(0, $user->id);

        // Update
        $user->update(['first_name' => 'EloquentUpdated']);
        $refreshed = User::find($user->id);
        $this->assertSame('EloquentUpdated', $refreshed->first_name);

        // Delete
        $userId = $user->id;
        $user->delete();
        $this->assertNull(User::find($userId));
    }
}
