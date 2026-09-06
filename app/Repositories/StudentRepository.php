<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Student;

class StudentRepository
{
    public function findById(int $id): ?array
    {
        return Student::find($id);
    }

    public function findByUserId(int $userId): ?array
    {
        return Student::findByUserId($userId);
    }

    public function create(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return Student::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return Student::update($id, $data);
    }

    public function getBySection(int $sectionId): array
    {
        return Student::getBySection($sectionId);
    }

    public function getBySubject(int $subjectId, int $academicTermId): array
    {
        return Student::getBySubject($subjectId, $academicTermId);
    }
}
