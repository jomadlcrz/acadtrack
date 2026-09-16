<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Models\AcademicTerm;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Models\AttendanceRecord;
use App\Repositories\AttendanceRepository;
use App\Services\EvaluationService;

class AttendanceWorkflowTest extends TestCase
{
    private static int $termId;
    private static int $subjectId;
    private static int $studentId;
    private static int $facultyId;

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

        $student = Student::first();
        self::$studentId = (int) ($student['id'] ?? 1);

        $subject = Subject::first();
        self::$subjectId = (int) ($subject['id'] ?? 1);

        $faculty = User::where('role', 'faculty')->first();
        self::$facultyId = (int) ($faculty['id'] ?? 1);
    }

    public function testAttendanceBulkSaveAndRetrieval(): void
    {
        $repo = new AttendanceRepository();
        $testDate = '2026-09-10';

        // Clean previous test records
        AttendanceRecord::query()
            ->where('subject_id', self::$subjectId)
            ->where('attendance_date', $testDate)
            ->delete();

        $records = [
            [
                'student_id' => self::$studentId,
                'status' => 'Present',
                'remarks' => 'On time',
            ],
        ];

        $savedCount = $repo->bulkSave(
            self::$termId,
            self::$subjectId,
            self::$facultyId,
            $testDate,
            $records
        );

        $this->assertSame(1, $savedCount);

        // Verify retrieval by subject and date
        $fetched = AttendanceRecord::getBySubjectAndDate(self::$subjectId, self::$termId, $testDate);
        $this->assertArrayHasKey(self::$studentId, $fetched);
        $this->assertSame('Present', $fetched[self::$studentId]['status']);
        $this->assertSame('On time', $fetched[self::$studentId]['remarks']);
    }

    public function testAttendanceLogsHistory(): void
    {
        $logs = AttendanceRecord::getSubjectAttendanceLogs(self::$subjectId, self::$termId);
        $this->assertIsArray($logs);
        // Should contain our test date record
        $found = false;
        foreach ($logs as $log) {
            if ($log['attendance_date'] === '2026-09-10') {
                $found = true;
                $this->assertGreaterThanOrEqual(1, (int) $log['present_count']);
                break;
            }
        }
        $this->assertTrue($found, 'Attendance logs should include the logged session.');
    }

    public function testAbsenceCounterAndWarningThreshold(): void
    {
        $repo = new AttendanceRepository();

        // Add 5 distinct absent sessions for this student
        for ($i = 1; $i <= 5; $i++) {
            $d = sprintf('2026-08-%02d', $i);
            AttendanceRecord::query()
                ->where('subject_id', self::$subjectId)
                ->where('attendance_date', $d)
                ->delete();

            $repo->bulkSave(
                self::$termId,
                self::$subjectId,
                self::$facultyId,
                $d,
                [
                    [
                        'student_id' => self::$studentId,
                        'status' => 'Absent',
                        'remarks' => 'Unexcused absence #' . $i,
                    ],
                ]
            );
        }

        $absenceSummary = AttendanceRecord::getStudentAbsenceSummary(self::$studentId, self::$subjectId, self::$termId);
        $this->assertGreaterThanOrEqual(5, $absenceSummary['absences']);
        $this->assertTrue($absenceSummary['warning_flag'], '5 or more absences must trigger the warning_flag');

        // Test Overall Summary for student
        $overall = AttendanceRecord::getStudentOverallSummary(self::$studentId, self::$termId);
        $this->assertArrayHasKey('total_absences', $overall);
        $this->assertGreaterThanOrEqual(5, $overall['total_absences']);
        $this->assertTrue($overall['warning_flag']);
    }

    public function testDigitalStudentPassGeneration(): void
    {
        $evaluationService = new EvaluationService();
        $pass = $evaluationService->getDigitalPass(self::$studentId, self::$subjectId, self::$termId);

        $this->assertIsArray($pass);
        $this->assertArrayHasKey('student', $pass);
        $this->assertArrayHasKey('grades', $pass);
        $this->assertArrayHasKey('summary', $pass);
        $this->assertArrayHasKey('attendance', $pass);
        $this->assertArrayHasKey('verification', $pass);

        $this->assertNotEmpty($pass['verification']['verification_code']);
        $this->assertNotEmpty($pass['verification']['verification_url']);
        $this->assertNotEmpty($pass['verification']['qr_data']);
        $this->assertArrayHasKey('warning_flag', $pass['attendance']);
    }

    public function testAttendanceControllerIndexRendersWithoutError(): void
    {
        $_SESSION['user'] = [
            'id' => self::$facultyId,
            'role' => 'faculty',
            'username' => 'faculty_test',
        ];

        $request = new \App\Core\Request();
        $response = new class extends \App\Core\Response {
            public string $output = '';
            public function html(string $content, int $code = 200): void
            {
                $this->output = $content;
            }
        };
        $session = new \App\Core\Session();

        $controller = new \App\Controllers\Faculty\AttendanceController();
        $controller->index($request, $response, $session);

        $this->assertNotEmpty($response->output);
        $this->assertStringContainsString('Attendance', $response->output);
    }

    public static function tearDownAfterClass(): void
    {
        // Clean up test attendance records
        AttendanceRecord::query()
            ->where('subject_id', self::$subjectId)
            ->where('attendance_date', '>=', '2026-08-01')
            ->where('attendance_date', '<=', '2026-09-15')
            ->delete();
    }
}
