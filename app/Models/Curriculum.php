<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Curriculum extends Model
{
    protected $table = 'curricula';

    protected $fillable = [
        'program_id',
        'status',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function subjects()
    {
        return $this->hasMany(CurriculumSubject::class, 'curriculum_id')->orderBy('display_order')->orderBy('id');
    }
}
