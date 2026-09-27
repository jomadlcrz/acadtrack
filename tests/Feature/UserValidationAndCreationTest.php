<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Models\User;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\Department;
use App\Models\Set;
use App\Validators\UserValidator;
use App\Controllers\Admin\UserController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class UserValidationAndCreationTest extends TestCase
{
    private static array $createdUserIds = [];
    private static array $cleanupSetIds = [];
    private static ?int $deptId = null;
    private static ?int $setId = null;

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

        $dept = Department::first();
        self::$deptId = $dept ? (int) $dept->id : null;

        $set = Set::first();
        if (!$set) {
            $term = \App\Models\AcademicTerm::getActive();
            $set = Set::create([
                'name' => 'TEST-VALID-SET',
                'academic_term_id' => (int) ($term['id'] ?? 1),
                'year_level' => 1,
                'status' => 'active',
            ]);
            self::$cleanupSetIds[] = (int) $set->id;
        }
        self::$setId = $set ? (int) $set->id : null;
    }

    public static function tearDownAfterClass(): void
    {
        foreach (self::$createdUserIds as $uid) {
            Student::where('user_id', $uid)->delete();
            Faculty::where('user_id', $uid)->delete();
            User::where('id', $uid)->delete();
        }

        if (!empty(self::$cleanupSetIds)) {
            Set::whereIn('id', self::$cleanupSetIds)->delete();
        }
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
    }

    public function testValidatorRejectsCompletelyEmptyData(): void
    {
        $validator = new UserValidator();
        $valid = $validator->validate([]);

        $this->assertFalse($valid);
        $errors = $validator->errors();
        $this->assertArrayHasKey('first_name', $errors);
        $this->assertArrayHasKey('last_name', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('role', $errors);
    }

    public function testValidatorRejectsInvalidEmail(): void
    {
        $validator = new UserValidator();
        $valid = $validator->validate([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'invalid-email',
            'role' => 'Admin',
        ]);

        $this->assertFalse($valid);
        $this->assertArrayHasKey('email', $validator->errors());
    }

    public function testValidatorRejectsStudentWithoutSet(): void
    {
        $validator = new UserValidator();
        $valid = $validator->validate([
            'first_name' => 'Student',
            'last_name' => 'Test',
            'email' => 'student_test_' . time() . '@gwc.edu',
            'role' => 'Student',
            'set_id' => '',
        ]);

        $this->assertFalse($valid);
        $this->assertArrayHasKey('set_id', $validator->errors());
    }

    public function testValidatorRejectsFacultyWithoutDepartment(): void
    {
        $validator = new UserValidator();
        $valid = $validator->validate([
            'first_name' => 'Prof',
            'last_name' => 'Test',
            'email' => 'prof_test_' . time() . '@gwc.edu',
            'role' => 'Faculty',
            'department_id' => '',
        ]);

        $this->assertFalse($valid);
        $this->assertArrayHasKey('department_id', $validator->errors());
    }

    public function testValidatorRejectsDeanWithoutDepartment(): void
    {
        $validator = new UserValidator();
        $valid = $validator->validate([
            'first_name' => 'Dean',
            'last_name' => 'Test',
            'email' => 'dean_test_' . time() . '@gwc.edu',
            'role' => 'Dean',
            'department_id' => '',
        ]);

        $this->assertFalse($valid);
        $this->assertArrayHasKey('department_id', $validator->errors());
    }

    public function testValidatorRejectsDuplicateEmail(): void
    {
        $existing = User::first();
        $this->assertNotNull($existing);

        $validator = new UserValidator();
        $valid = $validator->validate([
            'first_name' => 'Clone',
            'last_name' => 'User',
            'email' => $existing->email,
            'role' => 'Admin',
        ]);

        $this->assertFalse($valid);
        $this->assertArrayHasKey('email', $validator->errors());
    }

    public function testValidatorAllowsUpdatingOwnEmail(): void
    {
        $existing = User::first();
        $this->assertNotNull($existing);

        $validator = new UserValidator();
        $valid = $validator->validate([
            'first_name' => $existing->first_name,
            'last_name' => $existing->last_name,
            'email' => $existing->email,
            'role' => $existing->role,
            'department_id' => self::$deptId,
            'set_id' => self::$setId,
        ], (int) $existing->id);

        $this->assertTrue($valid);
    }

    public function testValidatorAcceptsValidStudent(): void
    {
        $this->assertNotNull(self::$setId);
        $validator = new UserValidator();
        $valid = $validator->validate([
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'email' => 'maria_test_' . uniqid() . '@gwc.edu',
            'role' => 'Student',
            'set_id' => self::$setId,
        ]);

        $this->assertTrue($valid, json_encode($validator->errors()));
    }

    public function testValidatorAcceptsValidFaculty(): void
    {
        $this->assertNotNull(self::$deptId);
        $validator = new UserValidator();
        $valid = $validator->validate([
            'first_name' => 'Alan',
            'last_name' => 'Turing',
            'email' => 'alan_test_' . uniqid() . '@gwc.edu',
            'role' => 'Faculty',
            'department_id' => self::$deptId,
        ]);

        $this->assertTrue($valid, json_encode($validator->errors()));
    }

    public function testDeactivateAndActivateUser(): void
    {
        $uniqueId = time() . '_' . rand(1000, 9999);
        $user = User::create([
            'first_name' => 'Deact',
            'last_name' => 'User',
            'email' => "deact_{$uniqueId}@gwc.edu",
            'password' => password_hash('Secret123!', PASSWORD_BCRYPT),
            'role' => 'Faculty',
            'status' => 'active',
        ]);
        self::$createdUserIds[] = (int) $user->id;

        $controller = new UserController();
        $request = new Request();
        $response = new Response();
        $session = new Session();

        // Simulate admin session (different user ID)
        $session->set('user', ['id' => 999999, 'role' => 'Admin']);

        // Deactivate
        $controller->deactivate($request, $response, $session, (string) $user->id);
        $user->refresh();
        $this->assertSame('inactive', $user->status);
        $this->assertStringContainsString('deactivated', $session->getFlash('success'));

        // Activate
        $controller->activate($request, $response, $session, (string) $user->id);
        $user->refresh();
        $this->assertSame('active', $user->status);
        $this->assertStringContainsString('activated', $session->getFlash('success'));

        // Toggle to inactive
        $controller->toggleStatus($request, $response, $session, (string) $user->id);
        $user->refresh();
        $this->assertSame('inactive', $user->status);

        // Toggle to active
        $controller->toggleStatus($request, $response, $session, (string) $user->id);
        $user->refresh();
        $this->assertSame('active', $user->status);
    }

    public function testCannotDeactivateOwnAccount(): void
    {
        $uniqueId = time() . '_' . rand(1000, 9999);
        $admin = User::create([
            'first_name' => 'Self',
            'last_name' => 'Admin',
            'email' => "self_admin_{$uniqueId}@gwc.edu",
            'password' => password_hash('Secret123!', PASSWORD_BCRYPT),
            'role' => 'Admin',
            'status' => 'active',
        ]);
        self::$createdUserIds[] = (int) $admin->id;

        $controller = new UserController();
        $request = new Request();
        $response = new Response();
        $session = new Session();

        // Simulate logged in as this admin
        $session->set('user', ['id' => (int) $admin->id, 'role' => 'Admin']);

        $controller->deactivate($request, $response, $session, (string) $admin->id);
        $admin->refresh();
        $this->assertSame('active', $admin->status);
        $this->assertSame('You cannot deactivate your own active account.', $session->getFlash('error'));

        $controller->toggleStatus($request, $response, $session, (string) $admin->id);
        $admin->refresh();
        $this->assertSame('active', $admin->status);
        $this->assertSame('You cannot deactivate your own active account.', $session->getFlash('error'));
    }

    public function testAdminAccountsCannotBeDeactivatedByOtherAdmins(): void
    {
        $uniqueId = time() . '_' . rand(1000, 9999);
        $targetAdmin = User::create([
            'first_name' => 'Target',
            'last_name' => 'Admin',
            'email' => "target_admin_{$uniqueId}@gwc.edu",
            'password' => password_hash('Secret123!', PASSWORD_BCRYPT),
            'role' => 'Admin',
            'status' => 'active',
        ]);
        self::$createdUserIds[] = (int) $targetAdmin->id;

        $controller = new UserController();
        $request = new Request();
        $response = new Response();
        $session = new Session();

        // Simulate a DIFFERENT admin logged in
        $session->set('user', ['id' => 999999, 'role' => 'Admin']);

        $controller->deactivate($request, $response, $session, (string) $targetAdmin->id);
        $targetAdmin->refresh();
        $this->assertSame('active', $targetAdmin->status);
        $this->assertSame('Administrator accounts cannot be deactivated to prevent system lockout.', $session->getFlash('error'));

        $controller->toggleStatus($request, $response, $session, (string) $targetAdmin->id);
        $targetAdmin->refresh();
        $this->assertSame('active', $targetAdmin->status);
        $this->assertSame('Administrator accounts cannot be deactivated to prevent system lockout.', $session->getFlash('error'));
    }

    public function testValidatorRejectsSetMismatchedToYearLevel(): void
    {
        $term = \App\Models\AcademicTerm::getActive();
        $year1Set = Set::create([
            'name' => 'TEST-SYNC-1A',
            'academic_term_id' => (int) ($term['id'] ?? 1),
            'year_level' => 1,
            'status' => 'active',
        ]);
        self::$cleanupSetIds[] = (int) $year1Set->id;

        $validator = new UserValidator();
        // Trying to assign Year 1 set to a Year 2 student
        $valid = $validator->validate([
            'first_name' => 'Mismatch',
            'last_name' => 'Student',
            'email' => 'mismatch_' . time() . '@gwc.edu',
            'role' => 'Student',
            'year_level' => 2,
            'set_id' => $year1Set->id,
            'student_status' => 'Regular',
        ]);

        $this->assertFalse($valid);
        $this->assertArrayHasKey('year_level', $validator->errors());
        $this->assertStringContainsString('belongs to Year 1, which does not match Year 2', $validator->errors()['year_level']);
    }

    public function testValidatorAllowsSetMatchingYearLevel(): void
    {
        $term = \App\Models\AcademicTerm::getActive();
        $year2Set = Set::create([
            'name' => 'TEST-SYNC-2A',
            'academic_term_id' => (int) ($term['id'] ?? 1),
            'year_level' => 2,
            'status' => 'active',
        ]);
        self::$cleanupSetIds[] = (int) $year2Set->id;

        $validator = new UserValidator();
        $valid = $validator->validate([
            'first_name' => 'Match',
            'last_name' => 'Student',
            'email' => 'match_' . time() . '@gwc.edu',
            'role' => 'Student',
            'year_level' => 2,
            'set_id' => $year2Set->id,
            'student_status' => 'Regular',
        ]);

        $this->assertTrue($valid);
        $this->assertEmpty($validator->errors());
    }

    public function testValidatorAllowsIrregularStudentWithoutClassSet(): void
    {
        $validator = new UserValidator();
        $valid = $validator->validate([
            'first_name' => 'Irreg',
            'last_name' => 'Student',
            'email' => 'irreg_' . time() . '@gwc.edu',
            'role' => 'Student',
            'year_level' => 3,
            'set_id' => '',
            'student_status' => 'Irregular',
        ]);

        $this->assertTrue($valid);
        $this->assertEmpty($validator->errors());
    }

    public function testControllerStoreCreatesSynchronizedStudentAndStudentDetail(): void
    {
        $term = \App\Models\AcademicTerm::getActive();
        $testSet = Set::create([
            'name' => 'TEST-STORE-SET',
            'academic_term_id' => (int) ($term['id'] ?? 1),
            'year_level' => 1,
            'status' => 'active',
        ]);
        self::$cleanupSetIds[] = (int) $testSet->id;

        $uniqueEmail = 'store_sync_' . time() . '@gwc.edu';
        $_POST = [
            'first_name' => 'SyncStore',
            'last_name' => 'User',
            'email' => $uniqueEmail,
            'role' => 'Student',
            'year_level' => 1,
            'set_id' => (string) $testSet->id,
            'student_status' => 'Regular',
            'student_number' => '2026-TEST-' . rand(1000, 9999),
        ];

        $controller = new UserController();
        $request = new Request();
        $response = new Response();
        $session = new Session();

        $controller->store($request, $response, $session);
        $this->assertSame('User created successfully.', $session->getFlash('success'));

        $user = User::where('email', $uniqueEmail)->first();
        $this->assertNotNull($user);
        self::$createdUserIds[] = (int) $user->id;

        $student = Student::where('user_id', $user->id)->first();
        $this->assertNotNull($student);
        $this->assertSame((int) $testSet->id, (int) $student->set_id);
        $this->assertSame(1, (int) $student->year_level);
        $this->assertSame('Regular', $student->status);

        $studentDetail = \App\Models\StudentDetail::where('user_id', $user->id)->first();
        $this->assertNotNull($studentDetail);
        $this->assertSame((int) $testSet->id, (int) $studentDetail->set_id);
        $this->assertSame(1, (int) $studentDetail->year_level);
        $this->assertSame('Regular', $studentDetail->status);
    }

    public function testUserRepositoryFiltersByEnrollmentStatus(): void
    {
        $repo = new \App\Repositories\UserRepository();
        $regularResult = $repo->paginate(1, 10, 'Student', '', '', 'Regular');
        $this->assertIsArray($regularResult['data']);
        foreach ($regularResult['data'] as $row) {
            $this->assertSame('Regular', $row['student_status']);
        }

        $irregularResult = $repo->paginate(1, 10, 'Student', '', '', 'Irregular');
        $this->assertIsArray($irregularResult['data']);
        foreach ($irregularResult['data'] as $row) {
            $this->assertSame('Irregular', $row['student_status']);
        }
    }

    public function testControllerStoreForIrregularStudentForcesNullSet(): void
    {
        $uniqueEmail = 'store_irreg_' . time() . '@gwc.edu';
        $_POST = [
            'first_name' => 'IrregStore',
            'last_name' => 'User',
            'email' => $uniqueEmail,
            'role' => 'Student',
            'year_level' => 2,
            'set_id' => '',
            'student_status' => 'Irregular',
            'student_number' => '2026-IRREG-' . rand(1000, 9999),
        ];

        $controller = new UserController();
        $request = new Request();
        $response = new Response();
        $session = new Session();

        $controller->store($request, $response, $session);
        $this->assertSame('User created successfully.', $session->getFlash('success'));

        $user = User::where('email', $uniqueEmail)->first();
        $this->assertNotNull($user);
        self::$createdUserIds[] = (int) $user->id;

        $student = Student::where('user_id', $user->id)->first();
        $this->assertNotNull($student);
        $this->assertNull($student->set_id);
        $this->assertSame('Irregular', $student->status);

        $studentDetail = \App\Models\StudentDetail::where('user_id', $user->id)->first();
        $this->assertNotNull($studentDetail);
        $this->assertNull($studentDetail->set_id);
        $this->assertSame('Irregular', $studentDetail->status);
    }

    public function testBulkImportStudentsFromSpreadsheetJson(): void
    {
        $uniqueEmail1 = 'import_reg_' . time() . '_' . rand(100, 999) . '@gwc.edu';
        $uniqueEmail2 = 'import_irreg_' . time() . '_' . rand(100, 999) . '@gwc.edu';

        $studentRows = [
            [
                'student_number' => '2026-IMP-' . rand(1000, 9999),
                'first_name' => 'ImportedRegular',
                'last_name' => 'StudentOne',
                'email' => $uniqueEmail1,
                'student_status' => 'Regular',
                'year_level' => 1,
                'section' => 'TEST-VALID-SET',
            ],
            [
                'student_number' => '2026-IMP-' . rand(1000, 9999),
                'first_name' => 'ImportedIrregular',
                'last_name' => 'StudentTwo',
                'email' => $uniqueEmail2,
                'student_status' => 'Irregular',
                'year_level' => 2,
                'section' => '',
            ]
        ];

        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $tempInput = json_encode(['students' => $studentRows]);
        // Mock php://input by temporarily calling import with custom Request or simulated environment
        // Since UserController::import uses file_get_contents('php://input'), we can also test multipart or verify logic directly:
        // In PHP, file_get_contents('php://input') is empty in CLI when not piped, so let's test via direct rows or multipart file:
        $tmpCsv = tempnam(sys_get_temp_dir(), 'csv_import_');
        $fp = fopen($tmpCsv, 'w');
        fputcsv($fp, ['Student Number', 'First Name', 'Last Name', 'Email', 'Enrollment Status', 'Year Level', 'Class Section']);
        fputcsv($fp, [$studentRows[0]['student_number'], $studentRows[0]['first_name'], $studentRows[0]['last_name'], $studentRows[0]['email'], 'Regular', '1', 'TEST-VALID-SET']);
        fputcsv($fp, [$studentRows[1]['student_number'], $studentRows[1]['first_name'], $studentRows[1]['last_name'], $studentRows[1]['email'], 'Irregular', '2', '']);
        fclose($fp);

        $_SERVER['CONTENT_TYPE'] = 'multipart/form-data';
        $_FILES = [
            'excel_file' => [
                'name' => 'students.csv',
                'type' => 'text/csv',
                'tmp_name' => $tmpCsv,
                'error' => UPLOAD_ERR_OK,
                'size' => filesize($tmpCsv),
            ]
        ];

        $controller = new UserController();
        $request = new Request();
        $response = new Response();
        $session = new Session();

        $controller->import($request, $response, $session);
        unlink($tmpCsv);

        $flashError = $session->getFlash('error');
        $flashSuccess = $session->getFlash('success');
        $this->assertNull($flashError, 'Flash error occurred: ' . ($flashError ?? ''));
        $this->assertNotEmpty($flashSuccess);
        $this->assertStringContainsString('student accounts registered successfully', $flashSuccess);

        $user1 = User::where('email', $uniqueEmail1)->first();
        $this->assertNotNull($user1);
        self::$createdUserIds[] = (int) $user1->id;
        $student1 = Student::where('user_id', $user1->id)->first();
        $this->assertNotNull($student1);
        $this->assertSame('Regular', $student1->status);

        $user2 = User::where('email', $uniqueEmail2)->first();
        $this->assertNotNull($user2);
        self::$createdUserIds[] = (int) $user2->id;
        $student2 = Student::where('user_id', $user2->id)->first();
        $this->assertNotNull($student2);
        $this->assertSame('Irregular', $student2->status);
        $this->assertNull($student2->set_id);
    }
}

