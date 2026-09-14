<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class AcademicTerm extends Model
{
    protected $table = 'academic_terms';

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function gradingPeriods()
    {
        return $this->hasMany(GradingPeriod::class, 'academic_term_id');
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class, 'academic_term_id');
    }

    public function sets()
    {
        return $this->hasMany(Set::class, 'academic_term_id');
    }

    public static function getActive(): ?array
    {
        $stmt = self::db()->query("
            SELECT at.*, COALESCE(at.school_year, ay.school_year, '2026-2027') as academic_year_name
            FROM academic_terms at
            LEFT JOIN academic_years ay ON at.academic_year_id = ay.id
            WHERE at.is_active = 1
            LIMIT 1
        ");
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function getAll(): array
    {
        $stmt = self::db()->query("
            SELECT at.*, ay.school_year as academic_year_name
            FROM academic_terms at
            JOIN academic_years ay ON at.academic_year_id = ay.id
            ORDER BY ay.school_year DESC, at.semester
        ");
        return $stmt->fetchAll();
    }

    public static function getBySemester(string $semester): ?array
    {
        $active = self::getActive();
        $yearId = $active['academic_year_id'] ?? 1;

        $stmt = self::db()->prepare("
            SELECT at.*, ay.school_year as academic_year_name
            FROM academic_terms at
            JOIN academic_years ay ON at.academic_year_id = ay.id
            WHERE at.academic_year_id = :year_id AND at.semester = :sem
            LIMIT 1
        ");
        $stmt->execute(['year_id' => $yearId, 'sem' => $semester]);
        $result = $stmt->fetch();
        return $result ?: $active;
    }
}
