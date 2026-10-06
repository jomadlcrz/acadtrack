<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AcademicTerm;
use App\Models\Student;
use App\Models\StudentDetail;
use App\Models\StudentTermRegistration;
use App\Models\User;
use App\Controllers\Admin\UserController;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Bulk admin student import. Regression guard for the bulk-import redesign: the
 * endpoint must return JSON (never die with SMTP-fatal HTML), must not attempt
 * per-row credential emails, and must surface the generated temporary passwords
 * so the admin can distribute them. Wraps each run in a rolled-back transaction
 * so the development database is left untouched.
 */
class StudentBulkImportTest extends TestCase
{
    private static string $emailPrefix = 'import.test.student';
    private static ?int $termId = null;

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

        $term = AcademicTerm::getActive();
        self::$termId = $term ? (int) $term['id'] : null;
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
    private function postImport(array $students): array
    {
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $_SERVER['HTTP_ACCEPT'] = 'application/json';

        $request = new Request();
        $request->setRawBody((string) json_encode(['_token' => 'test-token', 'students' => $students]));

        $response = new Response();

        ob_start();
        (new UserController())->import($request, $response, $this->adminSession());
        ob_end_clean();

        $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        unset($_SERVER['HTTP_ACCEPT']);

        return [json_decode((string) $response->getBody(), true) ?: [], $response->getStatusCode()];
    }

    private function studentRow(string $number, string $first, string $last, string $email): array
    {
        return [
            'student_number' => $number,
            'first_name' => $first,
            'last_name' => $last,
            'email' => $email,
            'student_status' => 'Regular',
            'year_level' => 1,
            'section_start' => 'BSIT-1A',
            'section' => 'BSIT-1A',
        ];
    }

    public function testImportReturnsSuccessfulJsonWithCredentialsAndCreatesAccounts(): void
    {
        $this->assertNotNull(self::$termId, 'An active term is required for this test.');

        Capsule::connection()->beginTransaction();

        $juan = self::email('bulk.juan');
        $maria = self::email('bulk.maria');

        try {
            [$body, $status] = $this->postImport([
                $this->studentRow('2026-7001', 'Bulk', 'Juan', $juan),
                $this->studentRow('2026-7002', 'Bulk', 'Maria', $maria),
            ]);

            $this->assertSame(200, $status, 'Bulk import must answer JSON, never fatal-error HTML.');

            $json = (string) json_encode($body);
            $this->assertStringNotContainsString('<', $json, 'Response must be JSON, not a PHP error/HTML page.');
            $this->assertTrue($body['success']);
            $this->assertSame(2, $body['created']);
            $this->assertSame(0, $body['failed']);

            $this->assertArrayHasKey('credentials', $body, 'Temporary passwords must be returned so the admin can distribute them.');
            $this->assertArrayHasKey($juan, $body['credentials']);
            $this->assertArrayHasKey($maria, $body['credentials']);
            $this->assertSame(10, strlen((string) $body['credentials'][$juan]), 'Temporary passwords are 10 characters.');

            foreach ([$juan, $maria] as $email) {
                $user = User::where('email', $email)->first();
                $this->assertNotNull($user, 'The student account should exist.');
                $this->assertSame('Student', $user->role);
                $this->assertSame(1, (int) $user->force_password_change);

                $detail = StudentDetail::where('user_id', (int) $user->id)->first();
                $this->assertNotNull($detail);

                $student = Student::where('user_id', (int) $user->id)->first();
                $this->assertNotNull($student, 'A students row should exist for the term registration.');

                $reg = StudentTermRegistration::where('student_id', (int) $student->id)
                    ->where('academic_term_id', self::$termId)
                    ->first();
                $this->assertNotNull($reg, 'Student should be registered for the active term.');
            }

            $this->assertNull(User::where('email', $juan)->first()->notifications()->where('type', 'credentials')->first());
        } finally {
            Capsule::connection()->rollBack();
        }
    }

    public function testDuplicateEmailsAreReportedAndNotCreated(): void
    {
        Capsule::connection()->beginTransaction();

        $dup = self::email('bulk.duplicate');

        try {
            $this->postImport([$this->studentRow('2026-7101', 'Bulk', 'Dup', $dup)]);

            [$body] = $this->postImport([
                $this->studentRow('2026-7101', 'Bulk', 'Dup', $dup),
                $this->studentRow('2026-7102', 'Bulk', 'New', self::email('bulk.new')),
            ]);

            $this->assertSame(1, $body['created']);
            $this->assertSame(1, $body['failed']);
            $this->assertStringContainsString('already registered', $body['errors'][0]['message']);

            $this->assertSame(1, User::where('email', $dup)->count(), 'The duplicate row must not create a second account.');
        } finally {
            Capsule::connection()->rollBack();
        }
    }
}