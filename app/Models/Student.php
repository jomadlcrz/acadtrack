<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Student extends Model
{
    protected $table = 'students';

    protected $fillable = [
        'user_id',
        'section_id',
        'year_level',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class, 'student_id');
    }

    public static function findByUserId(int $userId): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM students WHERE user_id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function getBySection(int $sectionId): array
    {
        $stmt = self::db()->prepare("
            SELECT s.*, u.first_name, u.last_name, u.email, u.student_number
            FROM students s
            JOIN users u ON s.user_id = u.id
            WHERE s.section_id = :section_id
            ORDER BY u.last_name, u.first_name
        ");
        $stmt->execute(['section_id' => $sectionId]);
        return $stmt->fetchAll();
    }

    public static function getBySubject(int $subjectId, int $academicTermId, ?int $sectionId = null): array
    {
        $sectionClause = $sectionId ? "AND s.section_id = :section_id" : "";
        $stmt = self::db()->prepare("
            SELECT s.*, u.first_name, u.last_name, u.email, u.student_number,
                   sec.name AS section_name
            FROM students s
            JOIN users u ON s.user_id = u.id
            JOIN enrollments e ON e.student_id = s.id
            LEFT JOIN sections sec ON sec.id = s.section_id
            WHERE e.subject_id = :subject_id AND e.academic_term_id = :academic_term_id
            {$sectionClause}
            ORDER BY u.last_name, u.first_name
        ");
        $params = ['subject_id' => $subjectId, 'academic_term_id' => $academicTermId];
        if ($sectionId) {
            $params['section_id'] = $sectionId;
        }
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function enroll(int $studentId, int $subjectId, int $academicTermId): bool
    {
        $stmt = self::db()->prepare("
            INSERT IGNORE INTO enrollments (student_id, subject_id, academic_term_id, enrolled_at)
            VALUES (:student_id, :subject_id, :academic_term_id, NOW())
        ");
        return $stmt->execute([
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'academic_term_id' => $academicTermId,
        ]);
    }

    public static function getAllAvailable(): array
    {
        $stmt = self::db()->query("
            SELECT s.*, u.first_name, u.last_name, u.email, u.student_number,
                   sec.name AS section_name
            FROM students s
            JOIN users u ON s.user_id = u.id
            LEFT JOIN sections sec ON sec.id = s.section_id
            WHERE u.role = 'Student'
            ORDER BY u.last_name, u.first_name
        ");
        return $stmt->fetchAll();
    }
}
