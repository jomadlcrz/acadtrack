<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\AcademicTerm;
use App\Models\Enrollment;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Controllers\Faculty\StudentController;

class FacultyRosterImportTest extends TestCase
{
    private static int $termId;
    private static int $facultyUserId;
    private static int $assignedSubjectId;
    private static int $rosterSubjectId;

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
        self::$termId = (int) ($term['id'] ?? 1);

        $faculty = User::where('role', 'faculty')->first();
        self::$facultyUserId = (int) ($faculty['id'] ?? 0);

        $assigned = Faculty::getAssignedSubjects(self::$facultyUserId, self::$termId);
        self::$assignedSubjectId = (int) ($assigned[0]['id'] ?? 0);

        // A subject the faculty member is NOT assigned to, for the authorization check.
        $assignedIds = array_map(static fn(array $r): int => (int) $r['id'], $assigned);
        $unassigned = null;

        foreach (Subject::all() as $subject) {
            if (!in_array((int) $subject['id'], $assignedIds, true)) {
                $unassigned = $subject;
                break;
            }
        }

        self::$rosterSubjectId = (int) ($unassigned['id'] ?? 0);
    }

    private function facultySession(): Session
    {
        $session = new Session();
        $session->set('user', [
            'id' => self::$facultyUserId,
            'role' => 'Faculty',
        ]);
        return $session;
    }

    /** @return array{0: array<string,mixed>, 1: int} decoded JSON body and HTTP status */
    private function postRoster(array $payload): array
    {
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $_SERVER['HTTP_ACCEPT'] = 'application/json';

        $request = new Request();
        $request->setRawBody((string) json_encode($payload));

        $response = new Response();
        $session = $this->facultySession();

        ob_start();
        (new StudentController())->importRoster($request, $response, $session);
        ob_end_clean();

        $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        unset($_SERVER['HTTP_ACCEPT']);

        return [json_decode((string) $response->getBody(), true) ?: [], $response->getStatusCode()];
    }

    /** Students with an unambiguous id, email and student number. */
    private static function sampleStudents(int $limit = 2): array
    {
        $rows = Student::query()
            ->select('students.id as student_id', 'users.email', 'student_details.student_number')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->leftJoin('student_details', 'student_details.user_id', '=', 'users.id')
            ->limit($limit)
            ->get()
            ->map(static fn($s) => $s->toArray())
            ->all();

        return array_values(array_filter($rows, static fn(array $r): bool => !empty($r['email'])));
    }

    public function testImportEnrollsStudentsMatchedByIdentifier(): void
    {
        $students = self::sampleStudents(2);

        $this->assertNotEmpty($students, 'Seeded students are required for this test.');

        $payload = [];
        foreach ($students as $student) {
            Enrollment::where('student_id', $student['student_id'])
                ->where('subject_id', self::$assignedSubjectId)
                ->where('academic_term_id', self::$termId)
                ->delete();

            $payload[] = [
                'student_number' => (string) ($student['student_number'] ?? ''),
                'email' => (string) $student['email'],
                'last_name' => '',
                'first_name' => '',
            ];
        }

        [$body, $status] = $this->postRoster([
            'subject_id' => self::$assignedSubjectId,
            'academic_term_id' => self::$termId,
            'semester' => '1',
            'students' => $payload,
        ]);

        $this->assertSame(200, $status);
        $this->assertTrue($body['success'], 'Import should report success.');
        $this->assertSame(count($payload), $body['enrolled'], 'Every matched student should be enrolled.');

        foreach ($students as $student) {
            $this->assertTrue(
                Enrollment::isEnrolled((int) $student['student_id'], self::$assignedSubjectId, self::$termId),
                'Student should now be on the roster.'
            );
        }
    }

    public function testImportIsIdempotentForStudentsAlreadyOnRoster(): void
    {
        $student = self::sampleStudents(1)[0];

        Enrollment::where('student_id', $student['student_id'])
            ->where('subject_id', self::$assignedSubjectId)
            ->where('academic_term_id', self::$termId)
            ->delete();

        $this->postRoster([
            'subject_id' => self::$assignedSubjectId,
            'academic_term_id' => self::$termId,
            'students' => [['email' => (string) $student['email']]],
        ]);

        [$body] = $this->postRoster([
            'subject_id' => self::$assignedSubjectId,
            'academic_term_id' => self::$termId,
            'students' => [['email' => (string) $student['email']]],
        ]);

        $this->assertSame(0, $body['enrolled']);
        $this->assertSame(1, $body['already_enrolled']);
    }

    public function testImportRejectsSubjectFacultyIsNotAssignedTo(): void
    {
        $this->assertNotSame(0, self::$rosterSubjectId, 'A subject outside the faculty assignment is required.');

        [$body, $status] = $this->postRoster([
            'subject_id' => self::$rosterSubjectId,
            'academic_term_id' => self::$termId,
            'students' => [['email' => 'someone@gwc.edu.ph']],
        ]);

        $this->assertSame(422, $status);
        $this->assertFalse($body['success']);
        $this->assertStringContainsString('not assigned', $body['message']);
    }

    public function testImportReportsUnmatchedRowsAsErrors(): void
    {
        [$body] = $this->postRoster([
            'subject_id' => self::$assignedSubjectId,
            'academic_term_id' => self::$termId,
            'students' => [
                ['student_number' => '9999-9999'],
                ['last_name' => 'NoContact', 'first_name' => 'Row'],
            ],
        ]);

        $this->assertSame(0, $body['enrolled']);
        $this->assertSame(2, $body['failed']);
        $this->assertStringContainsString('No registered student matches', $body['errors'][0]['message']);
        $this->assertStringContainsString('Provide a student ID', $body['errors'][1]['message']);
    }

    public function testRosterTemplateReturnsCsvWithRequiredHeaders(): void
    {
        ob_start();
        (new StudentController())->rosterTemplate(new Request(), new Response());
        $csv = ob_get_clean();

        $this->assertStringContainsString('Student Number', $csv);
        $this->assertStringContainsString('Email', $csv);
        $this->assertStringContainsString('Last Name', $csv);
        $this->assertStringContainsString('First Name', $csv);
    }

    public function testRosterPageExposesImportEntryPoint(): void
    {
        $controller = new StudentController();
        $response = new Response();

        ob_start();
        $controller->index(new Request(), $response, $this->facultySession());
        ob_end_clean();

        $body = (string) $response->getBody();

        $this->assertStringContainsString('rosterImportModal', $body);
        $this->assertStringContainsString('Import roster', $body);
        $this->assertStringContainsString('/faculty/students/import-roster', $body);
        $this->assertStringContainsString('/faculty/students/roster-template', $body);
    }
}
