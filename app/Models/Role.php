<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'role_name',
        'name',
        'description',
    ];

    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {
                if (is_string($column) && in_array(strtolower($column), ['name', 'roles.name'], true)) {
                    $column = ($column === 'name') ? 'role_name' : 'roles.role_name';
                }
                return parent::where($column, $operator, $value, $boolean);
            }
        };
    }

    public function getNameAttribute(): string
    {
        return (string) ($this->attributes['role_name'] ?? $this->attributes['name'] ?? '');
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['role_name'] = $value;
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $name = (string) ($this->role_name ?? $this->name ?? '');
        $array['role_name'] = $name;
        $array['name'] = $name;
        return $array;
    }

    public function userRoles()
    {
        return $this->hasMany(UserRole::class, 'role_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id');
    }

    public static function getIdByName(string $name): ?int
    {
        $clean = ucfirst(strtolower(trim($name)));
        $role = self::where('role_name', $clean)->first();
        return $role ? (int) $role->id : null;
    }
}
