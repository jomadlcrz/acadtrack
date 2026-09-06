<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Validators\LoginValidator;
use App\Validators\SubjectValidator;
use App\Validators\GradeValidator;

class ValidatorTest extends TestCase
{
    public function testLoginValidatorWithValidData(): void
    {
        $validator = new LoginValidator();
        $valid = $validator->validate([
            'email' => 'admin@gwc.edu',
            'password' => 'admin123',
        ]);

        $this->assertTrue($valid);
        $this->assertEmpty($validator->errors());
    }

    public function testLoginValidatorWithInvalidEmail(): void
    {
        $validator = new LoginValidator();
        $valid = $validator->validate([
            'email' => 'not-an-email',
            'password' => 'admin123',
        ]);

        $this->assertFalse($valid);
        $this->assertArrayHasKey('email', $validator->errors());
    }

    public function testLoginValidatorWithEmptyFields(): void
    {
        $validator = new LoginValidator();
        $valid = $validator->validate([]);

        $this->assertFalse($valid);
        $this->assertArrayHasKey('email', $validator->errors());
        $this->assertArrayHasKey('password', $validator->errors());
    }

    public function testSubjectValidatorWithValidData(): void
    {
        $validator = new SubjectValidator();
        $valid = $validator->validate([
            'code' => 'CS101',
            'name' => 'Introduction to Computing',
            'year_level' => '1',
            'semester' => '1',
        ]);

        $this->assertTrue($valid);
        $this->assertEmpty($validator->errors());
    }

    public function testSubjectValidatorMissingCode(): void
    {
        $validator = new SubjectValidator();
        $valid = $validator->validate([
            'name' => 'Introduction to Computing',
            'year_level' => '1',
            'semester' => '1',
        ]);

        $this->assertFalse($valid);
        $this->assertArrayHasKey('code', $validator->errors());
    }

    public function testGradeValidatorValidScore(): void
    {
        $validator = new GradeValidator();
        $valid = $validator->validate([
            'grades' => [1 => 85.5],
        ]);

        $this->assertTrue($valid);
    }

    public function testGradeValidatorOutOfRange(): void
    {
        $validator = new GradeValidator();
        $valid = $validator->validate([
            'grades' => [1 => 105],
        ]);

        $this->assertFalse($valid);
    }
}