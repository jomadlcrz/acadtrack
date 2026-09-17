<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Models\Subject;
use App\Models\Set;
use App\Models\Department;
use App\Models\Program;
use App\Models\AcademicTerm;
use App\Models\User;
use App\Core\Database;
use App\Controllers\Admin\ArchiveController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class ArchiveHubWorkflowTest extends TestCase
{
    private static array $cleanupSubjectIds = [];
    private static array $cleanupSetIds = [];
    private static array $cleanupDeptIds = [];
    private static array $cleanupUserIds = [];
    private static array $cleanupTermIds = [];

    public static function setUpBeforeClass(): void
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();
        new Database(
            env('DB_HOST'),
            env('DB_DATABASE'),
            env('DB_USERNAME'),
            (string) env('DB_PASSWORD', '')
        );

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function tearDownAfterClass(): void
    {
        $pdo = Database::getConnection();

        foreach (self::$cleanupSubjectIds as $id) {
            Subject::where('id', $id)->delete();
        }
        foreach (self::$cleanupSetIds as $id) {
            Set::where('id', $id)->delete();
        }
        foreach (self::$cleanupDeptIds as $id) {
            Department::where('id', $id)->delete();
        }
        foreach (self::$cleanupUserIds as $id) {
            User::where('id', $id)->delete();
        }
        foreach (self::$cleanupTermIds as $id) {
            $pdo->prepare("DELETE FROM academic_terms WHERE id = :id")->execute(['id' => $id]);
        }
    }

    public function testArchiveHubIndexRendersSuccessfully(): void
    {
        $controller = new ArchiveController();
        $request = new Request(['tab' => 'subjects'], []);
        $response = new Response();
        $session = new Session();

        ob_start();
        $controller->index($request, $response, $session);
        ob_end_clean();

        $this->assertSame(200, $response->getStatusCode());
        $body = $response->getBody();
        $this->assertStringContainsString('Archives', $body);
        $this->assertStringContainsString('id="archiveTabs"', $body);
        $this->assertStringContainsString('tab=subjects', $body);
        $this->assertStringContainsString('tab=sets', $body);
        $this->assertStringContainsString('tab=terms', $body);
        $this->assertStringContainsString('tab=departments', $body);
        $this->assertStringContainsString('tab=users', $body);
    }

    public function testRestoreSubject(): void
    {
        $term = AcademicTerm::getActive();
        $termId = (int) ($term['id'] ?? 1);
        $program = Program::first();
        $programId = $program ? $program->id : null;

        $subject = Subject::create([
            'code' => 'ARCH_TEST_101',
            'name' => 'Archived Subject Test',
            'nature' => 'Lecture',
            'units' => 3,
            'year_level' => 1,
            'semester' => '1',
            'academic_term_id' => $termId,
            'program_id' => $programId,
            'is_archived' => 1,
            'archived_at' => date('Y-m-d H:i:s'),
        ]);
        self::$cleanupSubjectIds[] = $subject->id;

        $controller = new ArchiveController();
        $request = new Request([], []);
        $response = new Response();
        $session = new Session();

        $controller->restoreSubject($request, $response, $session, (string) $subject->id);

        $fresh = Subject::find($subject->id);
        $this->assertSame(0, (int) $fresh->is_archived);
        $this->assertNull($fresh->archived_at);
        $this->assertStringContainsString("restored to active curriculum", $session->getFlash('success') ?? '');
    }

    public function testRestoreSet(): void
    {
        $term = AcademicTerm::getActive();
        $termId = (int) ($term['id'] ?? 1);
        $program = Program::first();
        $programId = $program ? $program->id : null;

        $set = Set::create([
            'name' => 'ARCH_SEC_1',
            'year_level' => 1,
            'academic_term_id' => $termId,
            'program_id' => $programId,
            'status' => 'inactive',
        ]);
        self::$cleanupSetIds[] = $set->id;

        $controller = new ArchiveController();
        $request = new Request([], []);
        $response = new Response();
        $session = new Session();

        $controller->restoreSet($request, $response, $session, (string) $set->id);

        $fresh = Set::find($set->id);
        $this->assertSame('active', $fresh->status);
        $this->assertStringContainsString("restored to active status", $session->getFlash('success') ?? '');
    }

    public function testRestoreAcademicTerm(): void
    {
        $pdo = Database::getConnection();
        $ayStmt = $pdo->query("SELECT id FROM academic_years LIMIT 1");
        $ayId = (int) $ayStmt->fetchColumn();

        $stmt = $pdo->prepare("
            INSERT INTO academic_terms (academic_year_id, school_year, semester, is_active, is_archived, archived_at, created_at, updated_at)
            VALUES (:ay_id, '2028-2029', '1', 0, 1, NOW(), NOW(), NOW())
        ");
        $stmt->execute(['ay_id' => $ayId]);
        $termId = (int) $pdo->lastInsertId();
        self::$cleanupTermIds[] = $termId;

        $controller = new ArchiveController();
        $request = new Request([], []);
        $response = new Response();
        $session = new Session();

        $controller->restoreAcademicTerm($request, $response, $session, (string) $termId);

        $checkStmt = $pdo->prepare("SELECT is_archived, archived_at FROM academic_terms WHERE id = :id");
        $checkStmt->execute(['id' => $termId]);
        $term = $checkStmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertSame(0, (int) $term['is_archived']);
        $this->assertNull($term['archived_at']);
        $this->assertStringContainsString("Academic term restored", $session->getFlash('success') ?? '');
    }

    public function testRestoreDepartment(): void
    {
        $dept = Department::create([
            'code' => 'ARCH_DEPT',
            'name' => 'Archived Department Test',
            'status' => 'inactive',
        ]);
        self::$cleanupDeptIds[] = $dept->id;

        $controller = new ArchiveController();
        $request = new Request([], []);
        $response = new Response();
        $session = new Session();

        $controller->restoreDepartment($request, $response, $session, (string) $dept->id);

        $fresh = Department::find($dept->id);
        $this->assertSame('active', $fresh->status);
        $this->assertStringContainsString("restored to active status", $session->getFlash('success') ?? '');
    }

    public function testRestoreUser(): void
    {
        $user = User::create([
            'email' => 'arch_test_' . time() . '@example.com',
            'password' => password_hash('Secret123!', PASSWORD_BCRYPT),
            'status' => 'inactive',
            'deactivated_at' => date('Y-m-d H:i:s'),
        ]);
        self::$cleanupUserIds[] = $user->id;

        $controller = new ArchiveController();
        $request = new Request([], []);
        $response = new Response();
        $session = new Session();

        $controller->restoreUser($request, $response, $session, (string) $user->id);

        $fresh = User::find($user->id);
        $this->assertSame('active', $fresh->status);
        $this->assertNull($fresh->deactivated_at);
        $this->assertStringContainsString("activated successfully", $session->getFlash('success') ?? '');
    }
}
