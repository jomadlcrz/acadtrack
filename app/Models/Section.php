<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Section extends Model
{
    protected $table = 'sections';

    protected $fillable = [
        'name',
        'year_level',
        'academic_term_id',
        'department_id',
        'status',
    ];

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'section_id');
    }

    public static function getActiveByTerm(int $academicTermId): array
    {
        return self::where('academic_term_id', $academicTermId)
            ->where('status', 'active')
            ->orderBy('year_level', 'asc')
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();
    }

    public static function getByTermWithDetails(int $academicTermId): array
    {
        return self::where('academic_term_id', $academicTermId)
            ->with(['department'])
            ->withCount('students')
            ->orderBy('year_level', 'asc')
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();
    }

    public static function getByYearAndTerm(int $yearLevel, int $academicTermId): array
    {
        return self::where('academic_term_id', $academicTermId)
            ->where('year_level', $yearLevel)
            ->where('status', 'active')
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();
    }
}
