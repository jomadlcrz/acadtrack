<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class StudentTermRegistration extends Model
{
    public const STATE_ENROLLED = 'enrolled';
    public const STATE_WITHDRAWN = 'withdrawn';
    public const STATE_COMPLETED = 'completed';

    protected $table = 'student_term_registrations';

    protected $fillable = [
        'student_id',
        'academic_term_id',
        'program_id',
        'set_id',
        'year_level',
        'status',
        'registration_status',
        'registered_by',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function set()
    {
        return $this->belongsTo(Set::class, 'set_id');
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }
}
