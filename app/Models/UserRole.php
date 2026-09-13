<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class UserRole extends Model
{
    protected $table = 'user_roles';

    protected $fillable = [
        'user_id',
        'role_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
