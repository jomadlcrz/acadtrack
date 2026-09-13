<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class StudentDetail extends Model
{
    protected $table = 'student_details';

    protected $fillable = [
        'user_id',
        'student_number',
        'first_name',
        'last_name',
        'middle_name',
        'program_id',
        'set_id',
        'year_level',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function set()
    {
        return $this->belongsTo(Set::class, 'set_id');
    }
}
