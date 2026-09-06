<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Faculty;

class FacultyRepository
{
    public function findById(int $id): ?array
    {
        return Faculty::find($id);
    }

    public function findByUserId(int $userId): ?array
    {
        return Faculty::findByUserId($userId);
    }

    public function create(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return Faculty::create($data);
    }

    public function getAssignedSubjects(int $facultyId, int $academicTermId): array
    {
        return Faculty::getAssignedSubjects($facultyId, $academicTermId);
    }

    public function assignSubject(int $facultyId, int $subjectId, int $academicTermId): int
    {
        $stmt = \App\Core\Database::getConnection()->prepare("
            INSERT INTO faculty_subjects (faculty_id, subject_id, academic_term_id, assigned_at)
            VALUES (:faculty_id, :subject_id, :academic_term_id, NOW())
        ");
        $stmt->execute([
            'faculty_id' => $facultyId,
            'subject_id' => $subjectId,
            'academic_term_id' => $academicTermId,
        ]);
        return (int) \App\Core\Database::getConnection()->lastInsertId();
    }

    public function removeAssignment(int $facultyId, int $subjectId, int $academicTermId): bool
    {
        $stmt = \App\Core\Database::getConnection()->prepare("
            DELETE FROM faculty_subjects
            WHERE faculty_id = :faculty_id AND subject_id = :subject_id AND academic_term_id = :academic_term_id
        ");
        return $stmt->execute([
            'faculty_id' => $facultyId,
            'subject_id' => $subjectId,
            'academic_term_id' => $academicTermId,
        ]);
    }
}
