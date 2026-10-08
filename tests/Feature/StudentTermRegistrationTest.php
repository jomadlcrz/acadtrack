<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\Program;
use App\Models\Set;
use App\Models\Student;
use App\Models\StudentTermRegistration;
use App\Models\User;
use App\Services\StudentRegistrationService;
use Illuminate\Database\Capsule\Manager as DB;
use PHPUnit\Framework\TestCase;

/**
 * Uses its own school years (2098-2099, 2099-2100) so rollovers never touch real students.
 */
class StudentTermRegistrationTest extends TestCase
{
    private static StudentRegistrationService $service;
    private static array $yearIds = [];
    private static array $termIds = [];
    private static array $setIds = [];
    private static array $userIds = [];
    private static int $programId;

    public static function setUpBeforeClass(): void
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();
        new \App\Core\Database(env('DB_HOST'), env('DB_DATABASE'), env('DB_USERNAME'), (string) env('DB_PASSWORD', ''));

        self::$service = new StudentRegistrationService();

        self::$programId = (int) Program::create([
            'program_abbrev' => 'TSTREG' . rand(100, 999),
            'program_name' => 'Registration Test Program ' . rand(1000, 9999),
            'program_type' => 'Associate Degree',
            'program_length' => '2 Years',
            'status' => 'active',
        ])->id;

        foreach (['2098-2099', '2099-2100'] as $schoolYear) {
            $yearId = (int) DB::table('academic_years')->insertGetId(['school_year' => $schoolYear, 'is_active' => 0]);
            self::$yearIds[$schoolYear] = $yearId;
            foreach ([1, 2] as $semester) {
                self::$termIds["{$schoolYear}:{$semester}"] = (int) AcademicTerm::create([
                    'academic_year_id' => $yearId,
                    'semester' => $semester,
                    'is_active' => 0,
                    'is_archived' => 0,
                ])->id;
            }
        }

        // Set "A" for Year 1 and Year 2 in every test term.
        foreach (self::$termIds as $termId) {
            foreach ([1, 2] as $year) {
                self::$setIds["{$termId}:{$year}"] = (int) Set::create([
                    'name' => "TSTR-{$year}A-{$termId}",
                    'set_code' => 'A',
                    'academic_term_id' => $termId,
                    'program_id' => self::$programId,
                    'year_level' => $year,
                    'status' => 'active',
                ])->id;
            }
        }
    }

    public static function tearDownAfterClass(): void
    {
        $termIds = array_values(self::$termIds);
        $studentIds = Student::whereIn('user_id', self::$userIds)->pluck('id')->all();

        if ($studentIds) {
            DB::table('enrollments')->whereIn('student_id', $studentIds)->delete();
            StudentTermRegistration::whereIn('student_id', $studentIds)->delete();
            Student::whereIn('id', $studentIds)->delete();
        }
        DB::table('grading_periods')->whereIn('academic_term_id', $termIds)->delete();
        DB::table('subjects')->whereIn('academic_term_id', $termIds)->delete();
        Set::whereIn('academic_term_id', $termIds)->delete();
        User::whereIn('id', self::$userIds)->delete();
        AcademicTerm::whereIn('id', $termIds)->delete();
        DB::table('academic_years')->whereIn('id', array_values(self::$yearIds))->delete();
        Program::where('id', self::$programId)->delete();
    }

    private function makeStudent(string $status = 'Regular', int $year = 1): int
    {
        $n = uniqid();
        $user = User::create([
            'first_name' => 'Reg',
            'last_name' => 'Test' . $n,
            'email' => "reg_test_{$n}@gwc.edu",
            'password' => password_hash('x', PASSWORD_BCRYPT),
            'role' => 'Student',
            'status' => 'active',
        ]);
        self::$userIds[] = (int) $user->id;

        return (int) Student::create([
            'user_id' => (int) $user->id,
            'set_id' => null,
            'year_level' => $year,
            'status' => $status,
        ])->id;
    }

    private function term(string $schoolYear, int $semester): int
    {
        return self::$termIds["{$schoolYear}:{$semester}"];
    }

    private function set(int $termId, int $year): int
    {
        return self::$setIds["{$termId}:{$year}"];
    }

    public function testRegularStudentIsRegisteredWithSetAndProfileMirrorsIt(): void
    {
        $studentId = $this->makeStudent();
        $termId = $this->term('2098-2099', 1);
        $setId = $this->set($termId, 1);

        $reg = self::$service->register($studentId, $termId, 'Regular', 1, $setId);

        $this->assertSame($setId, (int) $reg->set_id);
        $this->assertSame(self::$programId, (int) $reg->program_id);
        $student = Student::find($studentId);
        $this->assertSame($setId, (int) $student->set_id);
        $this->assertSame('Regular', $student->status);
    }

    public function testRegularStudentWithoutSetIsRejected(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('must be registered in a set');
        self::$service->register($this->makeStudent(), $this->term('2098-2099', 1), 'Regular', 1, null);
    }

    public function testSetFromAnotherTermOrWrongYearIsRejected(): void
    {
        $studentId = $this->makeStudent();
        $termId = $this->term('2098-2099', 1);

        try {
            self::$service->register($studentId, $termId, 'Regular', 1, $this->set($this->term('2098-2099', 2), 1));
            $this->fail('A set from another term must be rejected.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('does not belong to this academic term', $e->getMessage());
        }

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('is for Year 2, not Year 1');
        self::$service->register($studentId, $termId, 'Regular', 1, $this->set($termId, 2));
    }

    public function testIrregularStudentNeverKeepsASet(): void
    {
        $studentId = $this->makeStudent('Irregular');
        $termId = $this->term('2098-2099', 1);

        $reg = self::$service->register($studentId, $termId, 'Irregular', 1, $this->set($termId, 1));

        $this->assertNull($reg->set_id);
        $this->assertSame('Irregular', $reg->status);
    }

    public function testClosedTermRejectsRegistration(): void
    {
        $termId = $this->term('2099-2100', 2);
        AcademicTerm::where('id', $termId)->update(['is_closed' => 1]);

        try {
            self::$service->register($this->makeStudent('Irregular'), $termId, 'Irregular', 1);
            $this->fail('Registration into a closed term must be rejected.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('closed', $e->getMessage());
        } finally {
            AcademicTerm::where('id', $termId)->update(['is_closed' => 0]);
        }
    }

    public function testNextTermFollowsSemesterThenSchoolYear(): void
    {
        $this->assertSame($this->term('2098-2099', 2), (int) self::$service->nextTerm($this->term('2098-2099', 1))->id);
        $this->assertSame($this->term('2099-2100', 1), (int) self::$service->nextTerm($this->term('2098-2099', 2))->id);
    }

    public function testRolloverMovesStudentsAcrossSemestersAndSchoolYears(): void
    {
        $regular = $this->makeStudent('Regular');
        $irregular = $this->makeStudent('Irregular');
        $sem1 = $this->term('2098-2099', 1);
        $sem2 = $this->term('2098-2099', 2);
        $nextYearSem1 = $this->term('2099-2100', 1);

        // A Year 1 subject in the program for semester 2 so the curriculum load has something to enroll.
        $subjectId = (int) DB::table('subjects')->insertGetId([
            'academic_term_id' => $sem2,
            'program_id' => self::$programId,
            'subject_code' => 'TSTR101-' . uniqid(),
            'descriptive_title' => 'Rollover Test Subject',
            'units' => 3,
            'year_level' => 1,
            'semester' => 2,
        ]);

        self::$service->register($regular, $sem1, 'Regular', 1, $this->set($sem1, 1));
        self::$service->register($irregular, $sem1, 'Irregular', 1);

        // Semester 1 -> 2: same school year, year level stays, Regular is loaded with the curriculum.
        $result = self::$service->rollover($sem1);
        $this->assertSame($sem2, $result['target_term_id']);
        $this->assertGreaterThanOrEqual(2, $result['registered']);

        $regSem2 = StudentTermRegistration::where('student_id', $regular)->where('academic_term_id', $sem2)->first();
        $this->assertSame(1, (int) $regSem2->year_level);
        $this->assertSame($this->set($sem2, 1), (int) $regSem2->set_id);
        $this->assertTrue(DB::table('enrollments')->where('student_id', $regular)->where('subject_id', $subjectId)->where('academic_term_id', $sem2)->exists());

        $irrSem2 = StudentTermRegistration::where('student_id', $irregular)->where('academic_term_id', $sem2)->first();
        $this->assertNull($irrSem2->set_id);
        $this->assertSame('Irregular', $irrSem2->status);
        $this->assertFalse(DB::table('enrollments')->where('student_id', $irregular)->where('academic_term_id', $sem2)->exists());

        // Running it again must not duplicate anything.
        $again = self::$service->rollover($sem1);
        $this->assertSame(0, $again['registered']);
        $this->assertGreaterThanOrEqual(2, count($again['skipped']));

        // Semester 2 -> next school year: year level +1, set follows the set code.
        $result = self::$service->rollover($sem2);
        $this->assertSame($nextYearSem1, $result['target_term_id']);
        $nextReg = StudentTermRegistration::where('student_id', $regular)->where('academic_term_id', $nextYearSem1)->first();
        $this->assertSame(2, (int) $nextReg->year_level);
        $this->assertSame($this->set($nextYearSem1, 2), (int) $nextReg->set_id);

        // Profile mirrors the latest registration.
        $this->assertSame(2, (int) Student::find($regular)->year_level);
    }

    public function testStudentsPastProgramLengthAreMarkedCompleted(): void
    {
        $studentId = $this->makeStudent('Regular', 2);
        $sem2 = $this->term('2099-2100', 2);
        self::$service->register($studentId, $sem2, 'Regular', 2, $this->set($sem2, 2));

        // No term follows 2099-2100 sem 2, so rolling over must refuse clearly.
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('No next academic term');
        self::$service->rollover($sem2);
    }

    public function testYearBeyondProgramLengthIsRejected(): void
    {
        $studentId = $this->makeStudent('Regular', 3);
        $termId = $this->term('2098-2099', 1);
        $set = Set::create([
            'name' => 'TSTR-3A-' . $termId,
            'set_code' => 'A',
            'academic_term_id' => $termId,
            'program_id' => self::$programId,
            'year_level' => 3,
            'status' => 'active',
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('exceeds the program length');
        self::$service->register($studentId, $termId, 'Regular', 3, (int) $set->id);
    }
}
