<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class AdminDetail extends Model
{
    protected $table = 'admin_details';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'middle_name',
        'phone_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
