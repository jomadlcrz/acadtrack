<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
    ];

    public function faculty()
    {
        return $this->hasMany(Faculty::class, 'department_id');
    }

    public static function getActive(): array
    {
        return self::where('status', 'active')
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();
    }

    public static function findByCode(string $code): ?self
    {
        return self::where('code', $code)->first();
    }
}
