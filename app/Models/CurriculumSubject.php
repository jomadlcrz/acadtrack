<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class CurriculumSubject extends Model
{
    protected $table = 'curriculum_subjects';

    protected $fillable = [
        'curriculum_id',
        'subject_id',
        'year_level',
        'semester',
        'subject_code',
        'descriptive_title',
        'units',
        'subject_type',
        'prerequisites',
        'display_order',
    ];

    public function curriculum()
    {
        return $this->belongsTo(Curriculum::class, 'curriculum_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
