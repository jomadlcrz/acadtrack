<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = [
        'dept_abbrev',
        'dept_name',
        'code',
        'name',
        'description',
        'status',
    ];

    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {
                if (is_string($column)) {
                    if (in_array($column, ['code', 'departments.code'], true)) {
                        $column = ($column === 'code') ? 'dept_abbrev' : 'departments.dept_abbrev';
                    } elseif (in_array($column, ['name', 'departments.name'], true)) {
                        $column = ($column === 'name') ? 'dept_name' : 'departments.dept_name';
                    }
                }
                return parent::where($column, $operator, $value, $boolean);
            }

            public function orderBy($column, $direction = 'asc')
            {
                if (is_string($column)) {
                    if (in_array($column, ['code', 'departments.code'], true)) {
                        $column = ($column === 'code') ? 'dept_abbrev' : 'departments.dept_abbrev';
                    } elseif (in_array($column, ['name', 'departments.name'], true)) {
                        $column = ($column === 'name') ? 'dept_name' : 'departments.dept_name';
                    }
                }
                return parent::orderBy($column, $direction);
            }
        };
    }

    public function getCodeAttribute(): string
    {
        return (string) ($this->attributes['dept_abbrev'] ?? $this->attributes['code'] ?? '');
    }

    public function setCodeAttribute($value): void
    {
        $this->attributes['dept_abbrev'] = $value;
    }

    public function getNameAttribute(): string
    {
        return (string) ($this->attributes['dept_name'] ?? $this->attributes['name'] ?? '');
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['dept_name'] = $value;
    }

    public function getDeptAbbrevAttribute(): string
    {
        return (string) ($this->attributes['dept_abbrev'] ?? $this->attributes['code'] ?? '');
    }

    public function setDeptAbbrevAttribute($value): void
    {
        $this->attributes['dept_abbrev'] = $value;
    }

    public function getDeptNameAttribute(): string
    {
        return (string) ($this->attributes['dept_name'] ?? $this->attributes['name'] ?? '');
    }

    public function setDeptNameAttribute($value): void
    {
        $this->attributes['dept_name'] = $value;
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $abbrev = $this->dept_abbrev;
        $name = $this->dept_name;
        $array['dept_abbrev'] = $abbrev;
        $array['code'] = $abbrev;
        $array['dept_name'] = $name;
        $array['name'] = $name;
        return $array;
    }

    public function faculty()
    {
        return $this->hasMany(Faculty::class, 'department_id');
    }

    public function sets()
    {
        return $this->hasMany(Set::class, 'department_id');
    }

    public function programs()
    {
        return $this->hasMany(Program::class, 'department_id');
    }

    public static function getActive(): array
    {
        return self::where('status', 'active')
            ->orderBy('dept_name', 'asc')
            ->get()
            ->toArray();
    }

    public static function findByCode(string $code): ?self
    {
        return self::where('dept_abbrev', $code)->first();
    }
}
