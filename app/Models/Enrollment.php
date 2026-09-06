<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Enrollment extends Model
{
    protected $table = 'enrollments';
    public $timestamps = false;

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }

    public static function getBySubject(int $subjectId, int $academicTermId): array
    {
        $stmt = self::db()->prepare("
            SELECT e.*, s.id as student_id, u.first_name, u.last_name, u.student_number
            FROM enrollments e
            JOIN students s ON e.student_id = s.id
            JOIN users u ON s.user_id = u.id
            WHERE e.subject_id = :subject_id AND e.academic_term_id = :academic_term_id
            ORDER BY u.last_name, u.first_name
        ");
        $stmt->execute(['subject_id' => $subjectId, 'academic_term_id' => $academicTermId]);
        return $stmt->fetchAll();
    }

    public static function isEnrolled(int $studentId, int $subjectId, int $academicTermId): bool
    {
        $stmt = self::db()->prepare("
            SELECT COUNT(*) FROM enrollments
            WHERE student_id = :student_id AND subject_id = :subject_id AND academic_term_id = :academic_term_id
        ");
        $stmt->execute([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'academic_term_id' => $academicTermId,
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function enroll(int $studentId, int $subjectId, int $academicTermId): int
    {
        $enrollment = self::create([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'academic_term_id' => $academicTermId,
            'enrolled_at' => date('Y-m-d H:i:s'),
        ]);
        return (int) $enrollment->id;
    }
}
