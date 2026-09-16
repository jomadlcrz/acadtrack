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
}
