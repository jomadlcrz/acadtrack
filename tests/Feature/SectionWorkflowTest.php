<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Models\Department;
use App\Models\AcademicTerm;

class SectionWorkflowTest extends TestCase
{
    private static array $cleanupUserIds = [];
    private static array $cleanupSectionIds = [];

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
        if (!empty(self::$cleanupUserIds)) {
            Student::whereIn('user_id', self::$cleanupUserIds)->delete();
            User::whereIn('id', self::$cleanupUserIds)->delete();
        }

        if (!empty(self::$cleanupSectionIds)) {
            Section::whereIn('id', self::$cleanupSectionIds)->delete();
        }
    }

    public function testBaselineSectionsExist(): void
    {
        $term = AcademicTerm::getActive();
        $this->assertNotNull($term, 'Active academic term should exist');

        $activeSections = Section::getActiveByTerm((int) $term['id']);
        $this->assertNotEmpty($activeSections, 'Active academic sections should be available for active term');

        $names = array_column($activeSections, 'name');
        $this->assertContains('BSIT-1A', $names);
    }

    public function testCreateUpdateAndRelateSection(): void
    {
        $term = AcademicTerm::getActive();
        $dept = Department::where('code', 'IT')->first();

        $uniqueName = 'TEST-' . rand(100, 999);
        $section = Section::create([
            'name' => $uniqueName,
            'academic_term_id' => (int) $term['id'],
            'year_level' => 3,
            'department_id' => $dept ? $dept->id : null,
            'status' => 'active',
        ]);
        self::$cleanupSectionIds[] = $section->id;

        $this->assertNotNull($section->id);
        $this->assertSame($uniqueName, $section->name);
        $this->assertSame(3, (int) $section->year_level);

        // Test Department relation
        if ($dept) {
            $loadedSection = Section::with('department')->find($section->id);
            $this->assertNotNull($loadedSection->department);
            $this->assertSame($dept->code, $loadedSection->department->code);
        }

        // Test Update
        $updatedName = $uniqueName . '-UPDATED';
        $section->update([
            'name' => $updatedName,
            'status' => 'inactive',
        ]);

        $reloaded = Section::find($section->id);
        $this->assertSame($updatedName, $reloaded->name);
        $this->assertSame('inactive', $reloaded->status);
    }

    public function testSectionUniquenessPerAcademicTerm(): void
    {
        $term = AcademicTerm::getActive();
        $uniqueName = 'UNIQ-' . rand(1000, 9999);

        $section1 = Section::create([
            'name' => $uniqueName,
            'academic_term_id' => (int) $term['id'],
            'year_level' => 1,
            'status' => 'active',
        ]);
        self::$cleanupSectionIds[] = $section1->id;

        $this->expectException(\PDOException::class);
        // Attempt duplicate section name in same academic term
        Section::create([
            'name' => $uniqueName,
            'academic_term_id' => (int) $term['id'],
            'year_level' => 2,
            'status' => 'active',
        ]);
    }

    public function testStudentSectionRelationshipAndRosterFiltering(): void
    {
        $term = AcademicTerm::getActive();
        $termId = (int) $term['id'];

        // 1. Create two test sections
        $secA = Section::create([
            'name' => 'COHORT-A-' . rand(100, 999),
            'academic_term_id' => $termId,
            'year_level' => 2,
            'status' => 'active',
        ]);
        self::$cleanupSectionIds[] = $secA->id;

        $secB = Section::create([
            'name' => 'COHORT-B-' . rand(100, 999),
            'academic_term_id' => $termId,
            'year_level' => 2,
            'status' => 'active',
        ]);
        self::$cleanupSectionIds[] = $secB->id;

        // 2. Create student A assigned to Sec A
        $randA = rand(1000, 9999);
        $userA = User::create([
            'first_name' => 'Student',
            'last_name' => "CohortA_{$randA}",
            'email' => "student_a_{$randA}@gwc.edu",
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'role' => 'Student',
            'status' => 'active',
        ]);
        self::$cleanupUserIds[] = $userA->id;

        $studentA = Student::create([
            'user_id' => $userA->id,
            'section_id' => $secA->id,
            'year_level' => 2,
            'status' => 'Regular',
        ]);

        // 3. Create student B assigned to Sec B
        $randB = rand(1000, 9999);
        $userB = User::create([
            'first_name' => 'Student',
            'last_name' => "CohortB_{$randB}",
            'email' => "student_b_{$randB}@gwc.edu",
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'role' => 'Student',
            'status' => 'active',
        ]);
        self::$cleanupUserIds[] = $userB->id;

        $studentB = Student::create([
            'user_id' => $userB->id,
            'section_id' => $secB->id,
            'year_level' => 2,
            'status' => 'Regular',
        ]);

        // 4. Verify Student -> Section relationship
        $loadedStudentA = Student::with('section')->find($studentA->id);
        $this->assertNotNull($loadedStudentA->section);
        $this->assertSame($secA->name, $loadedStudentA->section->name);

        // 5. Verify Section -> Students relationship
        $loadedSecA = Section::with('students')->find($secA->id);
        $this->assertTrue($loadedSecA->students->contains('id', $studentA->id));
        $this->assertFalse($loadedSecA->students->contains('id', $studentB->id));

        // 6. Test Subject Enrollment and filtering by section
        $subject = \App\Models\Subject::first();
        $this->assertNotNull($subject, 'Subject should exist for test');

        Student::enroll((int) $studentA->id, (int) $subject->id, $termId);
        Student::enroll((int) $studentB->id, (int) $subject->id, $termId);

        // Unfiltered roster contains both
        $allEnrolled = Student::getBySubject((int) $subject->id, $termId);
        $enrolledIds = array_column($allEnrolled, 'id');
        $this->assertContains((int) $studentA->id, $enrolledIds);
        $this->assertContains((int) $studentB->id, $enrolledIds);

        // Filtered by Sec A contains student A and NOT student B
        $secAEnrolled = Student::getBySubject((int) $subject->id, $termId, (int) $secA->id);
        $secAIds = array_column($secAEnrolled, 'id');
        $this->assertContains((int) $studentA->id, $secAIds);
        $this->assertNotContains((int) $studentB->id, $secAIds);

        // Filtered by Sec B contains student B and NOT student A
        $secBEnrolled = Student::getBySubject((int) $subject->id, $termId, (int) $secB->id);
        $secBIds = array_column($secBEnrolled, 'id');
        $this->assertContains((int) $studentB->id, $secBIds);
        $this->assertNotContains((int) $studentA->id, $secBIds);
    }
}
