<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Program extends Model
{
    protected $table = 'programs';

    protected $fillable = [
        'program_abbrev',
        'program_name',
        'name',
        'program_type',
        'program_length',
        'department_id',
        'status',
        'description',
    ];

    public function getNameAttribute(): ?string
    {
        return $this->attributes['program_name'] ?? null;
    }

    public function setNameAttribute(?string $value): void
    {
        $this->attributes['program_name'] = $value;
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function curricula()
    {
        return $this->hasMany(Curriculum::class, 'program_id');
    }

    public function activeCurriculum()
    {
        return $this->hasOne(Curriculum::class, 'program_id')->where('status', 'active');
    }

    public function sets()
    {
        return $this->hasMany(Set::class, 'program_id');
    }

    public static function getAllWithCurricula(): array
    {
        return self::with(['department', 'curricula.subjects'])->orderBy('program_abbrev')->get()->toArray();
    }
}
