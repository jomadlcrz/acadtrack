<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class AcademicTerm extends Model
{
    protected $table = 'academic_terms';

    protected $appends = ['school_year', 'academic_year_name'];

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

    /**
     * Resolve school_year dynamically from parent academic_years table (3NF compliant).
     */
    public function getSchoolYearAttribute(): ?string
    {
        if (array_key_exists('school_year', $this->attributes) && $this->attributes['school_year'] !== null) {
            return (string) $this->attributes['school_year'];
        }
        return $this->academicYear?->school_year;
    }

    /**
     * Alias for school_year.
     */
    public function getAcademicYearNameAttribute(): ?string
    {
        return $this->getSchoolYearAttribute();
    }

    /**
     * Intercept setting school_year on AcademicTerm to maintain 3NF compliance.
     * Maps to academic_year_id and prevents writing to the dropped school_year column.
     */
    public function setSchoolYearAttribute($value): void
    {
        if (!empty($value)) {
            $year = AcademicYear::firstOrCreate(['school_year' => (string) $value], ['is_active' => 0]);
            $this->attributes['academic_year_id'] = $year->id;
        }
    }

    /**
     * Intercept queries targeting school_year on AcademicTerm and delegate to academicYear relation.
     */
    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {
                if ($column === 'school_year' || $column === 'academic_terms.school_year') {
                    if (func_num_args() === 2) {
                        $value = $operator;
                        $operator = '=';
                    }
                    return $this->whereHas('academicYear', function ($q) use ($operator, $value) {
                        $q->where('school_year', $operator, $value);
                    });
                }
                return parent::where($column, $operator, $value, $boolean);
            }
        };
    }

    public static function getActive(): ?array
    {
        $stmt = self::db()->query("
            SELECT at.*, 
                   COALESCE(ay.school_year, '2026-2027') as academic_year_name,
                   COALESCE(ay.school_year, '2026-2027') as school_year,
                   COALESCE(ay.school_year, '2026-2027') as school_year_display
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
            SELECT at.*, 
                   ay.school_year as academic_year_name,
                   ay.school_year as school_year,
                   ay.school_year as school_year_display
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
            SELECT at.*, 
                   ay.school_year as academic_year_name,
                   ay.school_year as school_year,
                   ay.school_year as school_year_display
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
