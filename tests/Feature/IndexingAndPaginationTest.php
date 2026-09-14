<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Core\Model;
use App\Repositories\UserRepository;
use App\Repositories\StudentRepository;
use App\Models\GradingSheet;
use App\Models\AcademicTerm;

class IndexingAndPaginationTest extends TestCase
{
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
    }

    public function test_database_performance_indexes_exist(): void
    {
        $pdo = Model::db();

        $checkIndex = function (string $table, string $indexName) use ($pdo): bool {
            $stmt = $pdo->prepare("SHOW INDEX FROM `{$table}` WHERE Key_name = :key_name");
            $stmt->execute(['key_name' => $indexName]);
            return $stmt->rowCount() > 0;
        };

        $this->assertTrue($checkIndex('academic_terms', 'idx_terms_active_sem'), "academic_terms idx_terms_active_sem missing");
        $this->assertTrue($checkIndex('academic_years', 'idx_ay_school_year'), "academic_years idx_ay_school_year missing");
        $this->assertTrue($checkIndex('users', 'idx_users_status'), "users idx_users_status missing");
        $this->assertTrue($checkIndex('student_details', 'idx_sd_name'), "student_details idx_sd_name missing");
        $this->assertTrue($checkIndex('faculty_details', 'idx_fd_name'), "faculty_details idx_fd_name missing");
        $this->assertTrue($checkIndex('subjects', 'idx_subjects_term_archived'), "subjects idx_subjects_term_archived missing");
        $this->assertTrue($checkIndex('enrollments', 'idx_enrollments_subject_term'), "enrollments idx_enrollments_subject_term missing");
        $this->assertTrue($checkIndex('grades', 'idx_grades_subject_term_period'), "grades idx_grades_subject_term_period missing");
        $this->assertTrue($checkIndex('grading_sheets', 'idx_sheets_term_status'), "grading_sheets idx_sheets_term_status missing");
        $this->assertTrue($checkIndex('sets', 'idx_sets_term_year'), "sets idx_sets_term_year missing");
        $this->assertTrue($checkIndex('notifications', 'idx_notifications_user_status_date'), "notifications idx_notifications_user_status_date missing");
    }

    public function test_user_repository_server_pagination_and_search(): void
    {
        $repo = new UserRepository();
        $result = $repo->paginate(1, 5, '', '', '');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('perPage', $result);
        $this->assertArrayHasKey('lastPage', $result);
        $this->assertLessThanOrEqual(5, count($result['data']));

        // Test search
        $searchResult = $repo->paginate(1, 10, '', '', 'admin@gwc.edu');
        $this->assertGreaterThanOrEqual(1, $searchResult['total']);
        $this->assertSame('admin@gwc.edu', $searchResult['data'][0]['email']);
    }

    public function test_grading_sheet_server_pagination(): void
    {
        $activeTerm = AcademicTerm::getActive();
        $termId = (int) ($activeTerm['id'] ?? 1);

        $paginated = GradingSheet::paginateWithDetails($termId, null, 1, 10);
        $this->assertIsArray($paginated);
        $this->assertArrayHasKey('data', $paginated);
        $this->assertArrayHasKey('total', $paginated);
        $this->assertArrayHasKey('page', $paginated);
        $this->assertArrayHasKey('lastPage', $paginated);
    }

    public function test_student_repository_server_pagination(): void
    {
        $repo = new StudentRepository();
        $activeTerm = AcademicTerm::getActive();
        $termId = (int) ($activeTerm['id'] ?? 1);

        $result = $repo->paginateBySubject(1, $termId, null, 1, 10);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('lastPage', $result);
    }

    public function test_pagination_component_renders_and_preserves_query_params(): void
    {
        $_GET = ['search' => 'Juan', 'role' => 'Student'];
        $pagination = [
            'total' => 45,
            'page' => 2,
            'perPage' => 10,
            'lastPage' => 5,
        ];

        ob_start();
        include __DIR__ . '/../../app/Views/components/pagination.php';
        $html = ob_get_clean();

        $this->assertStringContainsString('Showing', $html);
        $this->assertStringContainsString('11', $html);
        $this->assertStringContainsString('20', $html);
        $this->assertStringContainsString('45', $html);
        // Query param preservation check
        $this->assertStringContainsString('search=Juan', $html);
        $this->assertStringContainsString('role=Student', $html);
        $this->assertStringContainsString('page=3', $html);
    }
}
