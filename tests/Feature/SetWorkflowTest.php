<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Models\Set;
use App\Models\Student;
use App\Models\User;
use App\Models\Department;
use App\Models\AcademicTerm;

class SetWorkflowTest extends TestCase
{
    private static array $cleanupUserIds = [];
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
    }

    public static function tearDownAfterClass(): void
    {
        if (!empty(self::$cleanupUserIds)) {
            Student::whereIn('user_id', self::$cleanupUserIds)->delete();
            User::whereIn('id', self::$cleanupUserIds)->delete();
        }

        if (!empty(self::$cleanupSetIds)) {
            Set::whereIn('id', self::$cleanupSetIds)->delete();
        }
    }

    public function testBaselineSetsExist(): void
    {
        $term = AcademicTerm::getActive();
        $this->assertNotNull($term, 'Active academic term should exist');

        $activeSets = Set::getActiveByTerm((int) $term['id']);
        $this->assertNotEmpty($activeSets, 'Active academic sets should be available for active term');

        $names = array_column($activeSets, 'name');
        $this->assertContains('BSIT-1A', $names);
    }

    public function testCreateUpdateAndRelateSet(): void
    {
        $term = AcademicTerm::getActive();
        $dept = Department::where('code', 'IT')->orWhere('code', 'CIT')->first();

        $uniqueName = 'TEST-SET-' . rand(100, 999);
        $set = Set::create([
            'name' => $uniqueName,
            'academic_term_id' => (int) $term['id'],
            'year_level' => 3,
            'department_id' => $dept ? $dept->id : null,
            'status' => 'active',
        ]);
        self::$cleanupSetIds[] = $set->id;

        $this->assertNotNull($set->id);
        $this->assertSame($uniqueName, $set->name);
        $this->assertSame(3, (int) $set->year_level);

        // Test Department relation
        if ($dept) {
            $loadedSet = Set::with('department')->find($set->id);
            $this->assertNotNull($loadedSet->department);
            $this->assertSame($dept->code, $loadedSet->department->code);
        }

        // Test Update
        $updatedName = $uniqueName . '-UPD';
        $set->update([
            'name' => $updatedName,
            'status' => 'inactive',
        ]);

        $reloaded = Set::find($set->id);
        $this->assertSame($updatedName, $reloaded->name);
        $this->assertSame('inactive', $reloaded->status);
    }

    public function testSetUniquenessPerAcademicTerm(): void
    {
        $term = AcademicTerm::getActive();
        $uniqueName = 'UNIQ-SET-' . rand(1000, 9999);

        $set1 = Set::create([
            'name' => $uniqueName,
            'academic_term_id' => (int) $term['id'],
            'year_level' => 1,
            'status' => 'active',
        ]);
        self::$cleanupSetIds[] = $set1->id;

        $this->expectException(\PDOException::class);
        // Attempt duplicate set name in same academic term
        Set::create([
            'name' => $uniqueName,
            'academic_term_id' => (int) $term['id'],
            'year_level' => 2,
            'status' => 'active',
        ]);
    }

    public function testStudentSetRelationshipAndRosterFiltering(): void
    {
        $term = AcademicTerm::getActive();
        $termId = (int) $term['id'];

        // 1. Create two test sets
        $setA = Set::create([
            'name' => 'SET-BATCH-A-' . rand(100, 999),
            'academic_term_id' => $termId,
            'year_level' => 2,
            'status' => 'active',
        ]);
        self::$cleanupSetIds[] = $setA->id;

        $setB = Set::create([
            'name' => 'SET-BATCH-B-' . rand(100, 999),
            'academic_term_id' => $termId,
            'year_level' => 2,
            'status' => 'active',
        ]);
        self::$cleanupSetIds[] = $setB->id;

        // 2. Create student A assigned to Set A
        $randA = rand(1000, 9999);
        $userA = User::create([
            'first_name' => 'Student',
            'last_name' => "SetA_{$randA}",
            'email' => "student_set_a_{$randA}@gwc.edu",
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'role' => 'Student',
            'status' => 'active',
        ]);
        self::$cleanupUserIds[] = $userA->id;

        $studentA = Student::create([
            'user_id' => $userA->id,
            'set_id' => $setA->id,
            'year_level' => 2,
            'status' => 'Regular',
        ]);

        // 3. Create student B assigned to Set B
        $randB = rand(1000, 9999);
        $userB = User::create([
            'first_name' => 'Student',
            'last_name' => "SetB_{$randB}",
            'email' => "student_set_b_{$randB}@gwc.edu",
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'role' => 'Student',
            'status' => 'active',
        ]);
        self::$cleanupUserIds[] = $userB->id;

        $studentB = Student::create([
            'user_id' => $userB->id,
            'set_id' => $setB->id,
            'year_level' => 2,
            'status' => 'Regular',
        ]);

        // 4. Verify Student -> Set relationship
        $loadedStudentA = Student::with('set')->find($studentA->id);
        $this->assertNotNull($loadedStudentA->set);
        $this->assertSame($setA->name, $loadedStudentA->set->name);

        // 5. Verify Set -> Students relationship
        $loadedSetA = Set::with('students')->find($setA->id);
        $this->assertTrue($loadedSetA->students->contains('id', $studentA->id));
        $this->assertFalse($loadedSetA->students->contains('id', $studentB->id));

        // 6. Test Subject Enrollment and filtering by set
        $subject = \App\Models\Subject::first();
        $this->assertNotNull($subject, 'Subject should exist for test');

        Student::enroll((int) $studentA->id, (int) $subject->id, $termId);
        Student::enroll((int) $studentB->id, (int) $subject->id, $termId);

        // Unfiltered roster contains both
        $allEnrolled = Student::getBySubject((int) $subject->id, $termId);
        $enrolledIds = array_column($allEnrolled, 'id');
        $this->assertContains((int) $studentA->id, $enrolledIds);
        $this->assertContains((int) $studentB->id, $enrolledIds);

        // Filtered by Set A contains student A and NOT student B
        $setAEnrolled = Student::getBySubject((int) $subject->id, $termId, (int) $setA->id);
        $setAIds = array_column($setAEnrolled, 'id');
        $this->assertContains((int) $studentA->id, $setAIds);
        $this->assertNotContains((int) $studentB->id, $setAIds);

        // Filtered by Set B contains student B and NOT student A
        $setBEnrolled = Student::getBySubject((int) $subject->id, $termId, (int) $setB->id);
        $setBIds = array_column($setBEnrolled, 'id');
        $this->assertContains((int) $studentB->id, $setBIds);
        $this->assertNotContains((int) $studentA->id, $setBIds);
    }

    public function testAssignedSetIsRequiredForStudent(): void
    {
        // 1. In Admin UserController::store, missing set_id fails validation
        $_POST = [
            'first_name' => 'ReqSet',
            'last_name' => 'Student',
            'email' => 'req_set_' . time() . '@gwc.edu',
            'role' => 'Student',
            'set_id' => '',
        ];
        $adminController = new \App\Controllers\Admin\UserController();
        $request = new \App\Core\Request();
        $response = new \App\Core\Response();
        $session = new \App\Core\Session();

        $adminController->store($request, $response, $session);
        $this->assertSame('Assigned set is required for student accounts.', $session->getFlash('error'));

        // 2. In Faculty StudentController::addStudent, missing set_id fails validation
        $_POST = [
            'first_name' => 'FacultyReqSet',
            'last_name' => 'Student',
            'email' => 'fac_req_set_' . time() . '@gwc.edu',
            'subject_id' => '1',
            'set_id' => '',
        ];
        $facultyController = new \App\Controllers\Faculty\StudentController();
        $facRequest = new \App\Core\Request();
        $facultyController->addStudent($facRequest, $response, $session);
        $this->assertSame('Assigned set is required when adding a student.', $session->getFlash('error'));
    }

    public function testArchiveAndRestoreSetViaController(): void
    {
        $term = AcademicTerm::getActive();
        $set = Set::create([
            'name' => 'SET-ARCHIVE-' . rand(100, 999),
            'academic_term_id' => (int) $term['id'],
            'year_level' => 1,
            'status' => 'active',
        ]);
        self::$cleanupSetIds[] = $set->id;

        $controller = new \App\Controllers\Dean\SetController();
        $request = new \App\Core\Request();
        $response = new \App\Core\Response();
        $session = new \App\Core\Session();

        // Archive
        $_POST = ['semester' => '1'];
        $controller->archive($request, $response, $session, (string) $set->id);
        $set->refresh();
        $this->assertSame('inactive', $set->status);

        // Restore
        $controller->restore($request, $response, $session, (string) $set->id);
        $set->refresh();
        $this->assertSame('active', $set->status);
    }
}
