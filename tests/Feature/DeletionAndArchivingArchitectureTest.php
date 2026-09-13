<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Models\AcademicTerm;
use App\Models\Subject;
use App\Models\User;
use App\Models\Student;
use App\Models\Faculty;
use App\Models\Grade;
use App\Models\GradeHistoryLog;
use App\Models\GradingPeriod;
use App\Models\Enrollment;
use App\Repositories\SubjectRepository;

class DeletionAndArchivingArchitectureTest extends TestCase
{
    private static array $cleanupSubjectIds = [];
    private static array $cleanupUserIds = [];

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
    }

    public static function tearDownAfterClass(): void
    {
        foreach (self::$cleanupSubjectIds as $sid) {
            GradeHistoryLog::whereIn('grade_id', function ($query) use ($sid) {
                $query->select('id')->from('grades')->where('subject_id', $sid);
            })->delete();
            Grade::where('subject_id', $sid)->delete();
            Enrollment::where('subject_id', $sid)->delete();
            Subject::where('id', $sid)->delete();
        }

        foreach (self::$cleanupUserIds as $uid) {
            $student = Student::where('user_id', $uid)->first();
            if ($student) {
                GradeHistoryLog::where('student_id', $student->id)->delete();
                Grade::where('student_id', $student->id)->delete();
                Enrollment::where('student_id', $student->id)->delete();
                $student->delete();
            }
            Faculty::where('user_id', $uid)->delete();
            User::where('id', $uid)->delete();
        }
    }

    public function testSubjectArchivingAndRestoring(): void
    {
        $term = AcademicTerm::getActive();
        $termId = (int) ($term['id'] ?? 1);

        $subject = Subject::create([
            'code' => 'ARCH101',
            'name' => 'Archival Test Course',
            'nature' => 'Lecture',
            'year_level' => 1,
            'semester' => '1',
            'academic_term_id' => $termId,
        ]);
        self::$cleanupSubjectIds[] = (int) $subject->id;

        $this->assertEquals(0, $subject->is_archived);
        $this->assertNull($subject->archived_at);

        // Archive
        $repo = new SubjectRepository();
        $archived = $repo->archive((int) $subject->id);
        $this->assertTrue($archived);

        $refreshed = Subject::find($subject->id);
        $this->assertEquals(1, $refreshed->is_archived);
        $this->assertNotNull($refreshed->archived_at);

        // Restore
        $restored = $repo->restore((int) $subject->id);
        $this->assertTrue($restored);

        $refreshed = Subject::find($subject->id);
        $this->assertEquals(0, $refreshed->is_archived);
        $this->assertNull($refreshed->archived_at);
    }

    public function testSubjectArchivingSetsFlagsWithoutDestroyingRow(): void
    {
        $term = AcademicTerm::getActive();
        $termId = (int) ($term['id'] ?? 1);

        $subject = Subject::create([
            'code' => 'SAFEARCH99',
            'name' => 'Safe Archiving Course',
            'nature' => 'Lecture',
            'year_level' => 1,
            'semester' => '1',
            'academic_term_id' => $termId,
        ]);
        self::$cleanupSubjectIds[] = (int) $subject->id;

        $repo = new SubjectRepository();
        $this->assertFalse(method_exists($repo, 'delete'), 'Hard delete method must not exist in SubjectRepository');

        $result = $repo->archive((int) $subject->id);
        $this->assertTrue($result);

        $refreshed = Subject::find($subject->id);
        $this->assertNotNull($refreshed, 'Subject row must never be hard deleted');
        $this->assertEquals(1, $refreshed->is_archived);
        $this->assertNotNull($refreshed->archived_at);
    }

    public function testGradeAlterationTriggersAuditLogging(): void
    {
        $term = AcademicTerm::getActive();
        $termId = (int) ($term['id'] ?? 1);

        $subject = Subject::create([
            'code' => 'AUDIT301',
            'name' => 'Audit Trigger Course',
            'nature' => 'Lecture',
            'year_level' => 3,
            'semester' => '1',
            'academic_term_id' => $termId,
        ]);
        self::$cleanupSubjectIds[] = (int) $subject->id;

        $user = User::create([
            'first_name' => 'Audit',
            'last_name' => 'Pupil',
            'email' => 'audit_pupil_' . uniqid() . '@example.com',
            'password' => password_hash('Secret123!', PASSWORD_BCRYPT),
            'role' => 'Student',
            'status' => 'active',
        ]);
        self::$cleanupUserIds[] = (int) $user->id;

        $student = Student::create([
            'user_id' => $user->id,
            'year_level' => 3,
            'status' => 'Regular',
        ]);

        $period = GradingPeriod::where('academic_term_id', $termId)->first();
        $periodId = (int) ($period->id ?? 1);

        $gradeId = Grade::saveGrade(
            (int) $student->id,
            (int) $subject->id,
            $periodId,
            $termId,
            75.00
        );

        // Grade correction / update
        Grade::saveGrade(
            (int) $student->id,
            (int) $subject->id,
            $periodId,
            $termId,
            89.50
        );

        $logs = GradeHistoryLog::getByGrade($gradeId);
        $this->assertNotEmpty($logs);
        $latestLog = $logs[0];

        $this->assertEquals((int) $gradeId, (int) $latestLog['grade_id']);
        $this->assertEquals((int) $student->id, (int) $latestLog['student_id']);
        $this->assertEquals('75.00', number_format((float) $latestLog['old_score'], 2));
        $this->assertEquals('89.50', number_format((float) $latestLog['new_score'], 2));
        $this->assertEquals('UPDATE', $latestLog['action_performed']);
    }

    public function testMySqlForeignKeyRestrictPreventsDirectSubjectDeletion(): void
    {
        $term = AcademicTerm::getActive();
        $termId = (int) ($term['id'] ?? 1);

        $subject = Subject::create([
            'code' => 'FKRESTRICT1',
            'name' => 'Foreign Key Restrict Course',
            'nature' => 'Lecture',
            'year_level' => 1,
            'semester' => '1',
            'academic_term_id' => $termId,
        ]);
        self::$cleanupSubjectIds[] = (int) $subject->id;

        $user = User::create([
            'first_name' => 'Alice',
            'last_name' => 'Restricted',
            'email' => 'alice_restrict_' . uniqid() . '@example.com',
            'password' => password_hash('Secret123!', PASSWORD_BCRYPT),
            'role' => 'Student',
            'status' => 'active',
        ]);
        self::$cleanupUserIds[] = (int) $user->id;

        $student = Student::create([
            'user_id' => $user->id,
            'year_level' => 1,
            'status' => 'Regular',
        ]);

        $period = GradingPeriod::where('academic_term_id', $termId)->first();
        $periodId = (int) ($period->id ?? 1);

        Grade::saveGrade(
            (int) $student->id,
            (int) $subject->id,
            $periodId,
            $termId,
            85.00
        );

        // Attempting direct raw SQL delete on subject must fail due to MySQL ON DELETE RESTRICT foreign key
        $pdo = \App\Core\Database::getConnection();
        $this->expectException(\PDOException::class);
        $pdo->exec("DELETE FROM subjects WHERE id = " . (int) $subject->id);
    }
}
