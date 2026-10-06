<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\User;
use App\Controllers\Admin\UserController;

/**
 * Bulk personnel import. The controller writes real rows into the development
 * database, so every account this test creates is removed again afterwards.
 */
class PersonnelImportTest extends TestCase
{
    private static string $emailPrefix = 'import.test.personnel';
    /** @var array<int,string> */
    private static array $createdEmails = [];
    private static array $department = [];

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

        $departments = Department::getActive();
        self::$department = $departments[0] ?? [];
    }

    public static function tearDownAfterClass(): void
    {
        foreach (self::$createdEmails as $email) {
            $user = User::where('email', $email)->first();

            if ($user) {
                Faculty::where('user_id', (int) $user->id)->delete();
                $user->delete();
            }
        }
    }

    private static function remember(string $email): string
    {
        self::$createdEmails[] = $email;
        return $email;
    }

    private static function email(string $suffix): string
    {
        return self::$emailPrefix . '.' . $suffix . '@gwc.edu.ph';
    }

    private function adminSession(): Session
    {
        $session = new Session();
        $session->set('user', ['id' => 1, 'role' => 'Admin']);
        return $session;
    }

    /** @return array{0: array<string,mixed>, 1: int} decoded JSON body and HTTP status */
    private function postImport(string $role, array $rows): array
    {
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $_SERVER['HTTP_ACCEPT'] = 'application/json';

        $request = new Request();
        $request->setRawBody((string) json_encode(['role' => $role, 'personnel' => $rows]));

        $response = new Response();

        ob_start();
        (new UserController())->importPersonnel($request, $response, $this->adminSession());
        ob_end_clean();

        $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        unset($_SERVER['HTTP_ACCEPT']);

        return [json_decode((string) $response->getBody(), true) ?: [], $response->getStatusCode()];
    }

    private function departmentLabel(): string
    {
        $this->assertNotEmpty(self::$department, 'An active department is required for this test.');
        return (string) self::$department['dept_name'];
    }

    public function testImportCreatesFacultyAccountsLinkedToDepartment(): void
    {
        $email = self::remember(self::email('faculty.ok'));

        [$body, $status] = $this->postImport('Faculty', [[
            'first_name' => 'Import',
            'last_name' => 'Faculty',
            'email' => $email,
            'department' => $this->departmentLabel(),
        ]]);

        $this->assertSame(200, $status);
        $this->assertTrue($body['success']);
        $this->assertSame(1, $body['created']);
        $this->assertSame(0, $body['failed']);

        $user = User::where('email', $email)->first();

        $this->assertNotNull($user, 'The faculty account should exist.');
        $this->assertSame('Faculty', $user->role);
        $this->assertNull($user->student_number, 'Personnel accounts must not get a student number.');
        $this->assertSame(1, (int) $user->force_password_change, 'Imported accounts start on a forced password change.');

        $faculty = Faculty::where('user_id', (int) $user->id)->first();
        $this->assertNotNull($faculty, 'A faculty record should be created for the account.');
        $this->assertSame((int) self::$department['id'], (int) $faculty->department_id);
    }

    public function testImportMatchesDepartmentByAbbreviationAndName(): void
    {
        $byAbbrev = self::remember(self::email('dean.abbrev'));
        $byName = self::remember(self::email('dean.name'));

        [$body] = $this->postImport('Dean', [
            [
                'first_name' => 'Import',
                'last_name' => 'Abbrev',
                'email' => $byAbbrev,
                'department' => (string) self::$department['dept_abbrev'],
            ],
            [
                'first_name' => 'Import',
                'last_name' => 'Name',
                'email' => $byName,
                'department' => $this->departmentLabel(),
            ],
        ]);

        $this->assertSame(2, $body['created'], 'Both abbreviation and full name should resolve.');

        foreach ([$byAbbrev, $byName] as $email) {
            $user = User::where('email', $email)->first();
            $this->assertNotNull($user);
            $this->assertSame('Dean', $user->role);
        }
    }

    public function testImportRejectsUnknownDepartmentInsteadOfSilentlySkippingIt(): void
    {
        $email = self::remember(self::email('baddept'));

        [$body, $status] = $this->postImport('Faculty', [[
            'first_name' => 'Import',
            'last_name' => 'BadDept',
            'email' => $email,
            'department' => 'No Such Department 999',
        ]]);

        $this->assertSame(200, $status);
        $this->assertFalse($body['success']);
        $this->assertSame(0, $body['created']);
        $this->assertSame(1, $body['failed']);
        $this->assertStringContainsString('Unknown department', $body['errors'][0]['message']);
        $this->assertNull(User::where('email', $email)->first(), 'No account should be created without a department.');
    }

    public function testImportRejectsDuplicateAndIncompleteRows(): void
    {
        $existing = self::remember(self::email('duplicate'));
        $incomplete = self::remember(self::email('incomplete'));

        $this->postImport('Faculty', [[
            'first_name' => 'Import',
            'last_name' => 'Duplicate',
            'email' => $existing,
            'department' => $this->departmentLabel(),
        ]]);

        [$body] = $this->postImport('Faculty', [
            [
                'first_name' => 'Import',
                'last_name' => 'Duplicate',
                'email' => $existing,
                'department' => $this->departmentLabel(),
            ],
            ['first_name' => '', 'last_name' => '', 'email' => 'not-an-email', 'department' => ''],
            [
                'first_name' => 'Import',
                'last_name' => 'NoDept',
                'email' => $incomplete,
                'department' => '',
            ],
        ]);

        $this->assertSame(0, $body['created']);
        $this->assertSame(3, $body['failed']);
        $this->assertStringContainsString('already registered', $body['errors'][0]['message']);
        $this->assertStringContainsString('First name and last name are required', $body['errors'][1]['message']);
        $this->assertStringContainsString('Department is required', $body['errors'][2]['message']);
    }

    public function testImportRefusesAdminRole(): void
    {
        $email = self::remember(self::email('admin'));

        [$body, $status] = $this->postImport('Admin', [[
            'first_name' => 'Import',
            'last_name' => 'Admin',
            'email' => $email,
            'department' => $this->departmentLabel(),
        ]]);

        $this->assertSame(422, $status);
        $this->assertFalse($body['success']);
        $this->assertStringContainsString('Faculty and Dean', $body['message']);
        $this->assertNull(User::where('email', $email)->first(), 'Administrator accounts must not be bulk-created.');
    }

    public function testImportWithoutRowsIsRejected(): void
    {
        [$body, $status] = $this->postImport('Faculty', []);

        $this->assertSame(422, $status);
        $this->assertFalse($body['success']);
    }

    public function testFacultyAndDeanCreatePagesExposeImportUi(): void
    {
        foreach (['Faculty', 'Dean'] as $role) {
            $_GET['role'] = $role;

            $response = new Response();
            $request = new Request();

            ob_start();
            (new UserController())->create($request, $response, $this->adminSession());
            ob_end_clean();

            $html = (string) $response->getBody();

            $this->assertStringContainsString('personnelImportModal', $html, "{$role} page should expose the import modal.");
            $this->assertStringContainsString('Import spreadsheet', $html, "{$role} page should offer the import action.");
            $this->assertStringContainsString('/admin/users/import-template?role=' . urlencode($role), $html);
            $this->assertStringContainsString('/admin/users/import-personnel', $html);
        }

        unset($_GET['role']);
    }

    public function testAdminCreatePageDoesNotOfferSpreadsheetImport(): void
    {
        $_GET['role'] = 'Admin';

        $response = new Response();

        ob_start();
        (new UserController())->create(new Request(), $response, $this->adminSession());
        ob_end_clean();

        $html = (string) $response->getBody();

        $this->assertStringNotContainsString('personnelImportModal', $html);
        $this->assertStringNotContainsString('Import spreadsheet', $html);

        unset($_GET['role']);
    }
}