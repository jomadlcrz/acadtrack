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
use App\Models\GradingSheet;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Capsule\Manager as Capsule;

class EloquentOrmTest extends TestCase
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

    public function testEloquentCapsuleIsBooted(): void
    {
        $capsule = \App\Core\Database::getCapsule();
        $this->assertInstanceOf(Capsule::class, $capsule);
        $this->assertNotNull($capsule->getConnection());
    }

    public function testEloquentModelQueriesAndCollections(): void
    {
        $subjects = Subject::where('academic_term_id', 1)->get();
        $this->assertInstanceOf(Collection::class, $subjects);
        $this->assertGreaterThanOrEqual(1, $subjects->count());

        // Test Collection methods (pluck, first)
        $codes = $subjects->pluck('code')->all();
        $this->assertIsArray($codes);
        $this->assertContains('IT101', $codes);

        // Test Model instance property and array access
        $firstSubject = $subjects->first();
        $this->assertInstanceOf(Subject::class, $firstSubject);
        $this->assertSame($firstSubject->code, $firstSubject['code']);
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
