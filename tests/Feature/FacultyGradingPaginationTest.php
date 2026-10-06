<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AcademicTerm;
use App\Models\Faculty;
use App\Models\GradingPeriod;
use App\Models\Student;
use App\Models\User;
use App\Repositories\StudentRepository;
use App\Controllers\Faculty\GradingController;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Covers the server-side pagination + search wiring on /faculty/grading.
 * Uses its own temporary students (set_id NULL) so the real roster is untouched.
 */
class FacultyGradingPaginationTest extends TestCase
{
    private static int $facultyUserId;
    private static int $subjectId;
    private static int $termId;
    private static int $periodId;
    private static string $searchEmail = '';
    /** @var int[] */
    private static array $tempStudentIds = [];
    /** @var int[] */
    private static array $tempUserIds = [];

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

        $term = AcademicTerm::getActive();
        self::$termId = (int) ($term['id'] ?? 1);

        $faculty = User::where('role', 'faculty')->first();
        self::$facultyUserId = (int) ($faculty['id'] ?? 0);

        $assigned = Faculty::getAssignedSubjects(self::$facultyUserId, self::$termId);
        self::$subjectId = (int) ($assigned[0]['id'] ?? 0);

        $period = GradingPeriod::where('academic_term_id', self::$termId)
            ->orderBy('order_num')
            ->first();
        if (!$period) {
            $period = GradingPeriod::first();
        }
        self::$periodId = (int) ($period['id'] ?? 0);

        // Enough temporary students to guarantee more than one page at 15/page.
        self::$searchEmail = 'grading_pager_' . uniqid() . '@gwc.edu';
        for ($i = 0; $i < 20; $i++) {
            $user = User::create([
                'first_name' => 'Pager',
                'last_name' => 'Test' . $i,
                'email' => $i === 0 ? self::$searchEmail : "grading_pager_{$i}_" . uniqid() . '@gwc.edu',
                'password' => password_hash('x', PASSWORD_BCRYPT),
                'role' => 'Student',
                'status' => 'active',
            ]);
            self::$tempUserIds[] = (int) $user->id;

            $student = Student::create([
                'user_id' => (int) $user->id,
                'set_id' => null,
                'year_level' => 1,
                'status' => 'Regular',
            ]);
            $studentId = (int) $student->id;
            self::$tempStudentIds[] = $studentId;

            DB::table('enrollments')->insert([
                'student_id' => $studentId,
                'subject_id' => self::$subjectId,
                'academic_term_id' => self::$termId,
                'enrolled_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$tempStudentIds) {
            DB::table('enrollments')->whereIn('student_id', self::$tempStudentIds)->delete();
            Student::whereIn('id', self::$tempStudentIds)->delete();
        }
        if (self::$tempUserIds) {
            DB::table('student_details')->whereIn('user_id', self::$tempUserIds)->delete();
            User::whereIn('id', self::$tempUserIds)->delete();
        }
    }

    private function renderIndex(array $query): string
    {
        $original = $_GET;
        $_GET = $query;

        try {
            $response = new Response();
            $session = new Session();
            $session->set('user', ['id' => self::$facultyUserId, 'role' => 'Faculty']);

            (new GradingController())->index(new Request(), $response, $session);

            return (string) $response->getBody();
        } finally {
            $_GET = $original;
        }
    }

    private function defaultQuery(): array
    {
        return [
            'subject_id' => self::$subjectId,
            'period_id' => self::$periodId,
            'semester' => '1',
        ];
    }

    public function testRosterIndexRendersServerSideSearchForm(): void
    {
        $html = $this->renderIndex($this->defaultQuery());

        $this->assertStringContainsString('id="rosterSearchForm"', $html);
        $this->assertStringContainsString('name="search"', $html);
        $this->assertStringContainsString('name="page"', $html);
        $this->assertStringContainsString('Student Grade Roster', $html);
        // Pagination partial is included with the roster.
        $this->assertStringContainsString('enrolled students', $html);
    }

    public function testRosterIsLimitedToFifteenRowsPerPage(): void
    {
        $expected = (new StudentRepository())
            ->paginateBySubject(self::$subjectId, self::$termId, null, 1, 15, '');

        $this->assertGreaterThan(15, $expected['total'], 'The temporary roster must span more than one page.');
        $this->assertGreaterThan(1, $expected['lastPage']);

        $html = $this->renderIndex($this->defaultQuery());

        $this->assertSame(15, substr_count($html, 'name="grades['));
        $this->assertStringContainsString('aria-label="Table navigation"', $html);
        $this->assertStringContainsString('page=2', $html);
    }

    public function testSecondPageRendersRemainder(): void
    {
        $expected = (new StudentRepository())
            ->paginateBySubject(self::$subjectId, self::$termId, null, 2, 15, '');

        $query = $this->defaultQuery();
        $query['page'] = '2';
        $html = $this->renderIndex($query);

        $this->assertSame(count($expected['data']), substr_count($html, 'name="grades['));
        // Hidden page input keeps the faculty member on the same page after saving.
        $this->assertStringContainsString('name="page" value="2"', $html);
    }

    public function testOutOfRangePageIsClampedWithoutError(): void
    {
        $query = $this->defaultQuery();
        $query['page'] = '9999';

        $html = $this->renderIndex($query);

        $this->assertStringContainsString('Student Grade Roster', $html);
    }

    public function testSearchIsAppliedServerSide(): void
    {
        $query = $this->defaultQuery();
        $query['search'] = self::$searchEmail;
        $html = $this->renderIndex($query);

        $this->assertStringNotContainsString('empty-state-title', $html);
        $this->assertSame(1, substr_count($html, 'name="grades['));
        $this->assertStringContainsString(self::$searchEmail, $html);
    }

    public function testSearchWithNoMatchesShowsDedicatedEmptyState(): void
    {
        $query = $this->defaultQuery();
        $query['search'] = 'zzz-no-such-student-zzz';
        $html = $this->renderIndex($query);

        $this->assertStringContainsString('No enrolled students match your search query.', $html);
        $this->assertStringContainsString('Clear search', $html);
        $this->assertStringNotContainsString('id="rosterTable"', $html);
    }
}