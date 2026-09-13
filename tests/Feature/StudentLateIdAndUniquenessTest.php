<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Models\User;
use App\Validators\StudentValidator;

class StudentLateIdAndUniquenessTest extends TestCase
{
    private static array $createdUserIds = [];

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
        if (!empty(self::$createdUserIds)) {
            User::whereIn('id', self::$createdUserIds)->delete();
        }
    }

    public function testMultipleStudentsCanHaveNullStudentNumber(): void
    {
        $unique = time() . '_' . rand(1000, 9999);

        // Student 1 with null student_number
        $student1 = User::create([
            'first_name' => 'LateID',
            'last_name' => 'StudentOne',
            'email' => "lateid1_{$unique}@example.com",
            'password' => password_hash('secret123', PASSWORD_BCRYPT),
            'role' => 'student',
            'student_number' => null,
            'force_password_change' => 0,
        ]);
        self::$createdUserIds[] = $student1->id;

        // Student 2 with null student_number
        $student2 = User::create([
            'first_name' => 'LateID',
            'last_name' => 'StudentTwo',
            'email' => "lateid2_{$unique}@example.com",
            'password' => password_hash('secret123', PASSWORD_BCRYPT),
            'role' => 'student',
            'student_number' => null,
            'force_password_change' => 0,
        ]);
        self::$createdUserIds[] = $student2->id;

        $this->assertNotNull($student1->id);
        $this->assertNotNull($student2->id);
        $this->assertNull($student1->student_number);
        $this->assertNull($student2->student_number);

        // Verify both exist in DB
        $found1 = User::find($student1->id);
        $found2 = User::find($student2->id);
        $this->assertNull($found1->student_number);
        $this->assertNull($found2->student_number);
    }

    public function testUniqueStudentNumberIsEnforced(): void
    {
        $unique = time() . '_' . rand(1000, 9999);
        $targetNumber = "SN-TEST-{$unique}";

        // Student with unique student_number
        $student = User::create([
            'first_name' => 'Existing',
            'last_name' => 'Student',
            'email' => "existing_{$unique}@example.com",
            'password' => password_hash('secret123', PASSWORD_BCRYPT),
            'role' => 'student',
            'student_number' => $targetNumber,
            'force_password_change' => 0,
        ]);
        self::$createdUserIds[] = $student->id;

        $this->assertSame($targetNumber, $student->student_number);

        // Validator should reject duplicate student_number
        $validator = new StudentValidator();
        $isValid = $validator->validate([
            'first_name' => 'Duplicate',
            'last_name' => 'Student',
            'email' => "duplicate_{$unique}@example.com",
            'set_id' => 1,
            'student_number' => $targetNumber,
        ]);

        $this->assertFalse($isValid);
        $this->assertArrayHasKey('student_number', $validator->errors());
        $this->assertSame('Student number is already taken.', $validator->errors()['student_number']);

        // Database constraint should also reject duplicate insertion
        $this->expectException(\Illuminate\Database\QueryException::class);
        User::create([
            'first_name' => 'Direct',
            'last_name' => 'Duplicate',
            'email' => "direct_dup_{$unique}@example.com",
            'password' => password_hash('secret123', PASSWORD_BCRYPT),
            'role' => 'student',
            'student_number' => $targetNumber,
            'force_password_change' => 0,
        ]);
    }

    public function testLateIdIssuanceUpdate(): void
    {
        $unique = time() . '_' . rand(1000, 9999);
        $assignedNumber = "SN-LATE-{$unique}";

        // Student initially created with null student_number
        $student = User::create([
            'first_name' => 'Pending',
            'last_name' => 'Student',
            'email' => "pending_{$unique}@example.com",
            'password' => password_hash('secret123', PASSWORD_BCRYPT),
            'role' => 'student',
            'student_number' => null,
            'force_password_change' => 0,
        ]);
        self::$createdUserIds[] = $student->id;

        $this->assertNull($student->student_number);

        // Later issued student ID
        $student->student_number = $assignedNumber;
        $student->save();

        $refreshed = User::find($student->id);
        $this->assertSame($assignedNumber, $refreshed->student_number);
    }

    public function testValidatorPermitsEmptyOrNullStudentNumber(): void
    {
        $unique = time() . '_' . rand(1000, 9999);
        $validator = new StudentValidator();

        $validWithEmpty = $validator->validate([
            'first_name' => 'NoID',
            'last_name' => 'Student',
            'email' => "no_id_{$unique}@example.com",
            'set_id' => 1,
            'student_number' => '',
        ]);
        $this->assertTrue($validWithEmpty, 'Empty student_number should be valid');

        $validWithNull = $validator->validate([
            'first_name' => 'NullID',
            'last_name' => 'Student',
            'email' => "null_id_{$unique}@example.com",
            'set_id' => 1,
            'student_number' => null,
        ]);
        $this->assertTrue($validWithNull, 'Null student_number should be valid');
    }

    public function testFindByStudentNumberHelper(): void
    {
        $this->assertNull(User::findByStudentNumber(null));
        $this->assertNull(User::findByStudentNumber(''));
        $this->assertNull(User::findByStudentNumber('NON_EXISTENT_ID_99999'));
    }
}
