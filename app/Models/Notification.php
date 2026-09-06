<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function getByUser(int $userId): array
    {
        return static::where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->toArray();
    }
}
