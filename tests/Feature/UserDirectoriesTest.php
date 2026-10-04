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
}
