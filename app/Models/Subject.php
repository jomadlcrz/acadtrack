<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Subject extends Model
{
    protected $table = 'subjects';

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }

    public function gradingSetting()
    {
        return $this->hasOne(GradingSetting::class, 'subject_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'subject_id');
    }

    public function gradingSheets()
    {
        return $this->hasMany(GradingSheet::class, 'subject_id');
    }

    public static function getByDean(int $academicTermId): array
    {
        $stmt = self::db()->prepare("
            SELECT s.*, 
                   COUNT(DISTINCT fs.faculty_id) as assigned_faculty_count
            FROM subjects s
            LEFT JOIN faculty_subjects fs ON fs.subject_id = s.id AND fs.academic_term_id = :academic_term_id
            WHERE s.academic_term_id = :academic_term_id2
            GROUP BY s.id
            ORDER BY s.code
        ");
        $stmt->execute(['academic_term_id' => $academicTermId, 'academic_term_id2' => $academicTermId]);
        return $stmt->fetchAll();
    }

    public static function getActive(): array
    {
        $stmt = self::db()->query("
            SELECT s.*, ay.name as academic_year_name
            FROM subjects s
            JOIN academic_terms at2 ON s.academic_term_id = at2.id
            JOIN academic_years ay ON at2.academic_year_id = ay.id
            WHERE at2.is_active = 1
            ORDER BY s.code
        ");
        return $stmt->fetchAll();
    }
}
