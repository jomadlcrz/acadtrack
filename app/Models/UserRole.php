<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class UserRole extends Model
{
    protected $table = 'user_roles';

    protected $fillable = [
        'user_id',
        'role_id',
        'role_name',
    ];

    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {
                if (is_string($column) && in_array(strtolower($column), ['role_name', 'user_roles.role_name'], true)) {
                    $targetRole = ucfirst(strtolower((string) (($value === null) ? $operator : $value)));
                    $targetOp = ($value === null) ? '=' : $operator;
                    return $this->whereHas('role', function ($q) use ($targetOp, $targetRole) {
                        $q->where('role_name', $targetOp, $targetRole);
                    });
                }
                return parent::where($column, $operator, $value, $boolean);
            }

            public function whereIn($column, $values, $boolean = 'and', $not = false)
            {
                if (is_string($column) && in_array(strtolower($column), ['role_name', 'user_roles.role_name'], true)) {
                    $normalized = array_map(fn($v) => ucfirst(strtolower((string) $v)), (array) $values);
                    return $this->whereHas('role', function ($q) use ($normalized) {
                        $q->whereIn('role_name', $normalized);
                    });
                }
                return parent::whereIn($column, $values, $boolean, $not);
            }
        };
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function getRoleNameAttribute(): string
    {
        if ($this->relationLoaded('role') && $this->getRelation('role')) {
            return (string) ($this->getRelation('role')->role_name ?? $this->getRelation('role')->name);
        }
        if (!empty($this->attributes['role_name'])) {
            return (string) $this->attributes['role_name'];
        }
        if (!empty($this->role_id)) {
            $role = Role::find($this->role_id);
            return $role ? (string) $role->name : 'Student';
        }
        return 'Student';
    }

    public function setRoleNameAttribute($value): void
    {
        $roleId = Role::getIdByName((string) $value) ?? 4;
        $this->attributes['role_id'] = $roleId;
    }

    public function fill(array $attributes)
    {
        if (!empty($attributes['role_name']) && empty($attributes['role_id'])) {
            $attributes['role_id'] = Role::getIdByName((string) $attributes['role_name']) ?? 4;
            unset($attributes['role_name']);
        }
        return parent::fill($attributes);
    }

    public static function create(array $attributes = [])
    {
        if (!empty($attributes['role_name']) && empty($attributes['role_id'])) {
            $attributes['role_id'] = Role::getIdByName((string) $attributes['role_name']) ?? 4;
            unset($attributes['role_name']);
        }
        $model = new static($attributes);
        $model->save();
        return $model;
    }
}
