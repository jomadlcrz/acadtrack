<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class FacultyDetail extends Model
{
    protected $table = 'faculty_details';

    protected $fillable = [
        'user_id',
        'employee_id',
        'first_name',
        'last_name',
        'middle_name',
        'faculty_type',
        'department_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
