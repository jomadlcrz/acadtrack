<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Student;

class StudentRepository
{
    public function findById(int $id): ?array
    {
        $student = Student::find($id);
        return $student ? $student->toArray() : null;
    }

    public function findByUserId(int $userId): ?array
    {
        return Student::findByUserId($userId);
    }

    public function create(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $student = Student::create($data);
        return (int) $student->id;
    }

    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return (bool) Student::where('id', $id)->update($data);
    }

    public function getBySection(int $sectionId): array
    {
        return Student::getBySection($sectionId);
    }

    public function getBySubject(int $subjectId, int $academicTermId): array
    {
        return Student::getBySubject($subjectId, $academicTermId);
    }

    public function enroll(int $studentId, int $subjectId, int $academicTermId): bool
    {
        return Student::enroll($studentId, $subjectId, $academicTermId);
    }

    public function getAllAvailable(): array
    {
        return Student::getAllAvailable();
    }
}
