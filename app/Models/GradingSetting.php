<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class GradingSetting extends Model
{
    protected $table = 'grading_settings';

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }

    public static function getForSubject(int $subjectId, int $academicTermId, ?int $facultyId = null): array
    {
        $query = static::query()
            ->where('subject_id', $subjectId)
            ->where('academic_term_id', $academicTermId);

        if ($facultyId !== null) {
            $query->where('faculty_id', $facultyId);
        }

        $setting = $query->first();

        if ($setting) {
            return is_array($setting) ? $setting : $setting->toArray();
        }

        // Fallback to term default
        $default = static::query()
            ->where('academic_term_id', $academicTermId)
            ->whereNull('subject_id')
            ->first();

        if ($default) {
            return is_array($default) ? $default : $default->toArray();
        }

        return [
            'grading_method' => 'zero_based',
            'min_grade' => 0.00,
            'max_grade' => 100.00,
            'prelim_weight' => 20.00,
            'midterm_weight' => 20.00,
            'semi_final_weight' => 20.00,
            'final_weight' => 40.00,
        ];
    }
}
