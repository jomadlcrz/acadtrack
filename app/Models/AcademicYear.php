<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class AcademicYear extends Model
{
    protected $table = 'academic_years';

    public function academicTerms()
    {
        return $this->hasMany(AcademicTerm::class, 'academic_year_id');
    }
}
