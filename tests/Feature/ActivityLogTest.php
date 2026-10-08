<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\StudentRegistrationService;
use Illuminate\Database\Capsule\Manager as DB;
use PHPUnit\Framework\TestCase;

class ActivityLogTest extends TestCase
{
    private const ACTOR_EMAIL = 'activity-log-test@gwc.edu';

    private static array $userIds = [];
    private mixed $originalSession = null;

    public static function setUpBeforeClass(): void
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();
        new \App\Core\Database(env('DB_HOST'), env('DB_DATABASE'), env('DB_USERNAME'), (string) env('DB_PASSWORD', ''));
    }

    public static function tearDownAfterClass(): void
    {
        DB::table('audit_logs')->where('actor_email', self::ACTOR_EMAIL)->delete();
        $studentIds = Student::whereIn('user_id', self::$userIds)->pluck('id')->all();
        if ($studentIds) {
            DB::table('student_term_registrations')->whereIn('student_id', $studentIds)->delete();
            Student::whereIn('id', $studentIds)->delete();
        }
        User::whereIn('id', self::$userIds)->delete();
    }

    protected function setUp(): void
    {
        $this->originalSession = $_SESSION ?? null;
        $_SESSION['user'] = [
            'id' => 1, 'first_name' => 'Audit', 'last_name' => 'Tester', 'email' => self::ACTOR_EMAIL, 'role' => 'Admin',
        ];
    }

    protected function tearDown(): void
    {
        $_SESSION = $this->originalSession ?? [];
    }

    private function entries(string $action): array
    {
        return DB::table('audit_logs')->where('actor_email', self::ACTOR_EMAIL)->where('action', $action)->get()->all();
    }

    public function testRecordsWhoDidWhatToWhichRecord(): void
    {
        ActivityLogService::record([
            'category' => ActivityLogService::CATEGORY_ACCOUNTS,
            'action' => 'Unit Test Action',
            'target_type' => 'account',
            'target_id' => 42,
            'target_label' => 'Some Person',
            'summary' => 'Did a thing to Some Person.',
        ]);

        $rows = $this->entries('Unit Test Action');
        $this->assertCount(1, $rows);
        $this->assertSame('Audit Tester', $rows[0]->actor_name);
        $this->assertSame('Admin', $rows[0]->actor_role);
        $this->assertSame('accounts', $rows[0]->category);
        $this->assertSame('42', $rows[0]->target_id);
    }

    public function testNothingIsRecordedWhenNobodyIsSignedIn(): void
    {
        $_SESSION = [];
        $before = DB::table('audit_logs')->count();

        ActivityLogService::record(['category' => 'accounts', 'action' => 'Anonymous', 'summary' => 'Should not be stored.']);

        $this->assertSame($before, DB::table('audit_logs')->count());
    }

    public function testOverlongTextIsClippedInsteadOfFailing(): void
    {
        ActivityLogService::record([
            'category' => ActivityLogService::CATEGORY_GRADES,
            'action' => 'Clip Test',
            'summary' => str_repeat('x', 900),
        ]);

        $rows = $this->entries('Clip Test');
        $this->assertCount(1, $rows);
        $this->assertSame(500, mb_strlen($rows[0]->summary));
    }

    public function testPaginateFiltersByCategorySearchAndDate(): void
    {
        ActivityLogService::record(['category' => ActivityLogService::CATEGORY_TERMS, 'action' => 'Filter Probe', 'target_label' => 'ZZ-PROBE-9', 'summary' => 'Probe entry for filtering.']);

        $byCategory = ActivityLogService::paginate(['category' => 'terms', 'search' => 'ZZ-PROBE-9']);
        $this->assertSame(1, $byCategory['total']);

        $wrongCategory = ActivityLogService::paginate(['category' => 'grades', 'search' => 'ZZ-PROBE-9']);
        $this->assertSame(0, $wrongCategory['total']);

        $entryDate = substr((string) ($byCategory['data'][0]['created_at'] ?? date('Y-m-d')), 0, 10);
        $this->assertSame(1, ActivityLogService::paginate(['search' => 'ZZ-PROBE-9', 'start_date' => $entryDate, 'end_date' => $entryDate])['total']);
        $this->assertSame(0, ActivityLogService::paginate(['search' => 'ZZ-PROBE-9', 'start_date' => '2000-01-01', 'end_date' => '2000-01-02'])['total']);
    }

    public function testTargetUrlOnlyForRecordsThatHaveAPage(): void
    {
        $this->assertSame('/admin/users/7/edit', ActivityLogService::targetUrl('account', '7'));
        $this->assertSame('/dean/grade-review/3', ActivityLogService::targetUrl('grading_sheet', '3'));
        $this->assertNull(ActivityLogService::targetUrl('term', '3'));
        $this->assertNull(ActivityLogService::targetUrl('account', ''));
    }

    public function testStudentRegistrationIsLogged(): void
    {
        $n = uniqid();
        $user = User::create([
            'first_name' => 'Log', 'last_name' => 'Student' . $n, 'email' => "log_student_{$n}@gwc.edu",
            'password' => password_hash('x', PASSWORD_BCRYPT), 'role' => 'Student', 'status' => 'active',
        ]);
        self::$userIds[] = (int) $user->id;
        $student = Student::create(['user_id' => (int) $user->id, 'set_id' => null, 'year_level' => 1, 'status' => 'Irregular']);

        $term = \App\Models\AcademicTerm::getActive();
        (new StudentRegistrationService())->register((int) $student->id, (int) $term['id'], 'Irregular', 1);

        $rows = $this->entries('Student Registered');
        $this->assertNotEmpty($rows);
        $this->assertStringContainsString("Log Student{$n}", end($rows)->summary);
        $this->assertStringContainsString('Irregular', end($rows)->summary);
    }
}
