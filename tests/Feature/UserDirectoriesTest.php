<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Controllers\Admin\FacultyController;
use App\Controllers\Admin\AdministratorController;
use App\Controllers\Admin\StaffController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\UserRepository;

class UserDirectoriesTest extends TestCase
{
    private Session $session;

    protected function setUp(): void
    {
        $this->session = new Session();
        $_SESSION['user'] = ['id' => 1, 'email' => 'admin@gwc.edu', 'role' => 'Admin'];
        $_SESSION['role'] = 'Admin';
    }

    public function testPaginateFacultyReturnsOnlyFacultyAndDean(): void
    {
        $repo = new UserRepository();
        $result = $repo->paginateFaculty(1, 50);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        foreach ($result['data'] as $row) {
            $this->assertContains($row['role'], ['Faculty', 'Dean']);
            $this->assertNotSame('Student', $row['role']);
            $this->assertNotSame('Admin', $row['role']);
        }
    }

    public function testPaginateAdministratorsReturnsOnlyAdmin(): void
    {
        $repo = new UserRepository();
        $result = $repo->paginateAdministrators(1, 50);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertNotEmpty($result['data']);
        foreach ($result['data'] as $row) {
            $this->assertSame('Admin', $row['role']);
        }
    }

    public function testFacultyControllerRendersFacultyDirectory(): void
    {
        $controller = new FacultyController();
        $request = new Request();
        $response = new Response();

        ob_start();
        $controller->index($request, $response, $this->session);
        $output = ob_get_clean();

        $body = $response->getBody() ?: $output;
        $this->assertStringContainsString('Faculty', $body);
        $this->assertStringContainsString('/admin/faculty', $body);
    }

    public function testAdministratorControllerRendersAdministratorsDirectory(): void
    {
        $controller = new AdministratorController();
        $request = new Request();
        $response = new Response();

        ob_start();
        $controller->index($request, $response, $this->session);
        $output = ob_get_clean();

        $body = $response->getBody() ?: $output;
        $this->assertStringContainsString('Administrators', $body);
        $this->assertStringContainsString('/admin/administrators', $body);
    }

    public function testPaginateStudentsReturnsStudentAccounts(): void
    {
        $repo = new UserRepository();
        $result = $repo->paginateStudents();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertNotEmpty($result['data']);
        foreach ($result['data'] as $row) {
            $this->assertSame('Student', $row['role']);
            $this->assertArrayHasKey('first_name', $row);
            $this->assertArrayHasKey('last_name', $row);
            $this->assertArrayHasKey('year_level', $row);
            $this->assertArrayHasKey('student_status', $row);
        }
    }

    public function testStudentControllerRendersStudentsDirectory(): void
    {
        $controller = new \App\Controllers\Admin\StudentController();
        $request = new Request();
        $response = new Response();

        ob_start();
        $controller->index($request, $response, $this->session);
        $output = ob_get_clean();

        $body = $response->getBody() ?: $output;
        $this->assertStringContainsString('Students', $body);
        $this->assertStringContainsString('/admin/students', $body);
        $this->assertStringContainsString('Add student', $body);
        $this->assertStringNotContainsString('import-template', $body);
        $this->assertStringNotContainsString('importExcelModal', $body);
        $this->assertStringContainsString('All year levels', $body);
        $this->assertStringContainsString('All standings', $body);
    }

    public function testUserCreateStudentShowsDownloadAndImport(): void
    {
        $controller = new \App\Controllers\Admin\UserController();
        $_GET['role'] = 'Student';
        $request = new Request();
        $response = new Response();

        ob_start();
        $controller->create($request, $response, $this->session);
        $output = ob_get_clean();

        $body = $response->getBody() ?: $output;
        $this->assertStringContainsString('import-template', $body);
        $this->assertStringContainsString('importExcelModal', $body);
        $this->assertStringContainsString('/admin/students', $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('personnelRoles')]
    public function testUserCreatePersonnelShowsDownloadAndImport(string $role): void
    {
        $controller = new \App\Controllers\Admin\UserController();
        $_GET['role'] = $role;
        $request = new Request();
        $response = new Response();

        ob_start();
        $controller->create($request, $response, $this->session);
        $output = ob_get_clean();

        $body = $response->getBody() ?: $output;
        $this->assertStringContainsString('import-template?role=' . urlencode($role), $body);
        $this->assertStringContainsString('personnelImportModal', $body);
        $this->assertStringContainsString('/admin/faculty', $body);

        unset($_GET['role']);
    }

    public static function personnelRoles(): array
    {
        return [['Faculty'], ['Dean']];
    }

    /** Administrators cannot be edited after creation, so they stay manual-only. */
    public function testUserCreateAdminDoesNotShowDownloadAndImport(): void
    {
        $controller = new \App\Controllers\Admin\UserController();
        $_GET['role'] = 'Admin';
        $request = new Request();
        $response = new Response();

        ob_start();
        $controller->create($request, $response, $this->session);
        $output = ob_get_clean();

        $body = $response->getBody() ?: $output;
        $this->assertStringNotContainsString('import-template', $body);
        $this->assertStringNotContainsString('personnelImportModal', $body);
        $this->assertStringNotContainsString('importExcelModal', $body);
        $this->assertStringContainsString('/admin/administrators', $body);

        unset($_GET['role']);
    }
}
