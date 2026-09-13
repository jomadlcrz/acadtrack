<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Set extends Model
{
    protected $table = 'sets';

    protected $fillable = [
        'set_name',
        'name',
        'program_id',
        'year_level',
        'set_code',
        'academic_term_id',
        'department_id',
        'status',
    ];

    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {
                if (is_string($column) && in_array($column, ['name', 'sets.name'], true)) {
                    $column = ($column === 'name') ? 'set_name' : 'sets.set_name';
                }
                return parent::where($column, $operator, $value, $boolean);
            }

            public function orderBy($column, $direction = 'asc')
            {
                if (is_string($column) && in_array($column, ['name', 'sets.name'], true)) {
                    $column = ($column === 'name') ? 'set_name' : 'sets.set_name';
                }
                return parent::orderBy($column, $direction);
            }
        };
    }

    public function getNameAttribute(): string
    {
        return (string) ($this->attributes['set_name'] ?? $this->attributes['name'] ?? '');
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['set_name'] = $value;
    }

    public function getSetNameAttribute(): string
    {
        return (string) ($this->attributes['set_name'] ?? $this->attributes['name'] ?? '');
    }

    public function setSetNameAttribute($value): void
    {
        $this->attributes['set_name'] = $value;
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $name = $this->set_name;
        $array['set_name'] = $name;
        $array['name'] = $name;
        return $array;
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }

    public static function deriveSetName(string $programAbbrev, int $yearLevel, string $setCode): string
    {
        return strtoupper(trim($programAbbrev)) . '-' . $yearLevel . strtoupper(trim($setCode));
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'set_id');
    }

    public static function getActiveByTerm(int $academicTermId): array
    {
        return self::where('academic_term_id', $academicTermId)
            ->where('status', 'active')
            ->orderBy('year_level', 'asc')
            ->orderBy('set_name', 'asc')
            ->get()
            ->toArray();
    }

    public static function getByTermWithDetails(int $academicTermId): array
    {
        return self::where('academic_term_id', $academicTermId)
            ->with(['department'])
            ->withCount('students')
            ->orderBy('year_level', 'asc')
            ->orderBy('set_name', 'asc')
            ->get()
            ->toArray();
    }

    public static function getByYearAndTerm(int $yearLevel, int $academicTermId): array
    {
        return self::where('academic_term_id', $academicTermId)
            ->where('year_level', $yearLevel)
            ->where('status', 'active')
            ->orderBy('set_name', 'asc')
            ->get()
            ->toArray();
    }
}
