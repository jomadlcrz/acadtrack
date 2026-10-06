<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Faculty;

class FacultyRepository
{
    public function findById(int $id): ?array
    {
        $faculty = Faculty::find($id);
        return $faculty ? $faculty->toArray() : null;
    }

    public function findByUserId(int $userId): ?array
    {
        return Faculty::findByUserId($userId);
    }

    public function create(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $faculty = Faculty::create($data);
        return (int) $faculty->id;
    }

    public function getAssignedSubjects(int $facultyId, int $academicTermId): array
    {
        return Faculty::getAssignedSubjects($facultyId, $academicTermId);
    }

    public function hasAssignment(int $facultyId, int $subjectId, int $academicTermId, ?int $setId = null): bool
    {
        $db = \App\Core\Database::getConnection();
        if ($setId !== null && $setId > 0) {
            $stmt = $db->prepare("
                SELECT id FROM faculty_subjects
                WHERE faculty_id = :faculty_id
                  AND subject_id = :subject_id
                  AND academic_term_id = :academic_term_id
                  AND set_id = :set_id
                LIMIT 1
            ");
            $stmt->execute([
                'faculty_id' => $facultyId,
                'subject_id' => $subjectId,
                'academic_term_id' => $academicTermId,
                'set_id' => $setId,
            ]);
        } else {
            $stmt = $db->prepare("
                SELECT id FROM faculty_subjects
                WHERE faculty_id = :faculty_id
                  AND subject_id = :subject_id
                  AND academic_term_id = :academic_term_id
                  AND set_id IS NULL
                LIMIT 1
            ");
            $stmt->execute([
                'faculty_id' => $facultyId,
                'subject_id' => $subjectId,
                'academic_term_id' => $academicTermId,
            ]);
        }

        return (bool) $stmt->fetchColumn();
    }

    public function assignSubject(int $facultyId, int $subjectId, int $academicTermId, ?int $setId = null): int
    {
        $db = \App\Core\Database::getConnection();

        // Prevent duplicate assignment
        if ($setId !== null && $setId > 0) {
            $checkStmt = $db->prepare("
                SELECT id FROM faculty_subjects
                WHERE faculty_id = :faculty_id
                  AND subject_id = :subject_id
                  AND academic_term_id = :academic_term_id
                  AND set_id = :set_id
                LIMIT 1
            ");
            $checkStmt->execute([
                'faculty_id' => $facultyId,
                'subject_id' => $subjectId,
                'academic_term_id' => $academicTermId,
                'set_id' => $setId,
            ]);
        } else {
            $checkStmt = $db->prepare("
                SELECT id FROM faculty_subjects
                WHERE faculty_id = :faculty_id
                  AND subject_id = :subject_id
                  AND academic_term_id = :academic_term_id
                  AND set_id IS NULL
                LIMIT 1
            ");
            $checkStmt->execute([
                'faculty_id' => $facultyId,
                'subject_id' => $subjectId,
                'academic_term_id' => $academicTermId,
            ]);
        }

        $existingId = $checkStmt->fetchColumn();
        if ($existingId) {
            return (int) $existingId;
        }

        $stmt = $db->prepare("
            INSERT INTO faculty_subjects (faculty_id, subject_id, set_id, academic_term_id, assigned_at)
            VALUES (:faculty_id, :subject_id, :set_id, :academic_term_id, NOW())
        ");
        $stmt->execute([
            'faculty_id' => $facultyId,
            'subject_id' => $subjectId,
            'set_id' => $setId,
            'academic_term_id' => $academicTermId,
        ]);
        return (int) $db->lastInsertId();
    }

    public function getAssignmentsForTerm(int $academicTermId): array
    {
        $stmt = \App\Core\Database::getConnection()->prepare("
            SELECT fs.id as assignment_id, fs.faculty_id, fs.subject_id, fs.set_id, fs.academic_term_id, fs.assigned_at,
                   u.email,
                   fd.first_name, fd.last_name, fd.employee_id,
                   d.dept_name, d.dept_abbrev,
                   s_set.set_name, s_set.year_level as set_year_level
            FROM faculty_subjects fs
            JOIN users u ON fs.faculty_id = u.id
            LEFT JOIN faculty_details fd ON u.id = fd.user_id
            LEFT JOIN departments d ON fd.department_id = d.id
            LEFT JOIN sets s_set ON fs.set_id = s_set.id
            WHERE fs.academic_term_id = :academic_term_id
            ORDER BY fd.last_name ASC, fd.first_name ASC, s_set.set_name ASC
        ");
        $stmt->execute(['academic_term_id' => $academicTermId]);
        return $stmt->fetchAll() ?: [];
    }

    public function removeAssignment(int $facultyId, int $subjectId, int $academicTermId, ?int $setId = null, ?int $assignmentId = null): bool
    {
        if ($assignmentId !== null && $assignmentId > 0) {
            $stmt = \App\Core\Database::getConnection()->prepare("
                DELETE FROM faculty_subjects WHERE id = :id
            ");
            return $stmt->execute(['id' => $assignmentId]);
        }

        if ($facultyId > 0) {
            if ($setId !== null && $setId > 0) {
                $stmt = \App\Core\Database::getConnection()->prepare("
                    DELETE FROM faculty_subjects
                    WHERE faculty_id = :faculty_id AND subject_id = :subject_id AND set_id = :set_id AND academic_term_id = :academic_term_id
                ");
                return $stmt->execute([
                    'faculty_id' => $facultyId,
                    'subject_id' => $subjectId,
                    'set_id' => $setId,
                    'academic_term_id' => $academicTermId,
                ]);
            }

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

        $stmt = \App\Core\Database::getConnection()->prepare("
            DELETE FROM faculty_subjects
            WHERE subject_id = :subject_id AND academic_term_id = :academic_term_id
        ");
        return $stmt->execute([
            'subject_id' => $subjectId,
            'academic_term_id' => $academicTermId,
        ]);
    }
}
