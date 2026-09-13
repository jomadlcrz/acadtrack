<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use Illuminate\Database\Capsule\Manager as DB;

class User extends Model
{
    protected $table = 'users';

    const UPDATED_AT = null;

    protected $fillable = [
        'email',
        'password',
        'is_temp_password',
        'status',
        'deactivated_at',
    ];

    protected $casts = [
        'is_temp_password' => 'boolean',
    ];

    // Temporary storage during creation / update before relations save
    public ?string $pendingFirstName = null;
    public ?string $pendingLastName = null;
    public ?string $pendingRole = null;
    public ?string $pendingStudentNumber = null;

    public function userRole()
    {
        return $this->hasOne(UserRole::class, 'user_id');
    }

    public function userRoles()
    {
        return $this->hasMany(UserRole::class, 'user_id');
    }

    public function role()
    {
        return $this->hasOneThrough(Role::class, UserRole::class, 'user_id', 'id', 'id', 'role_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function adminDetail()
    {
        return $this->hasOne(AdminDetail::class, 'user_id');
    }

    public function facultyDetail()
    {
        return $this->hasOne(FacultyDetail::class, 'user_id');
    }

    public function studentDetail()
    {
        return $this->hasOne(StudentDetail::class, 'user_id');
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    public function faculty()
    {
        return $this->hasOne(Faculty::class, 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {
                if (is_string($column) && in_array(strtolower($column), ['role', 'users.role'], true)) {
                    $targetRole = ucfirst(strtolower((string) (($value === null) ? $operator : $value)));
                    $targetOp = ($value === null) ? '=' : $operator;
                    return $this->whereHas('userRole', function ($q) use ($targetOp, $targetRole) {
                        $q->where('role_name', $targetOp, $targetRole);
                    });
                }
                if (is_string($column) && in_array(strtolower($column), ['student_number', 'users.student_number'], true)) {
                    $val = ($value === null) ? $operator : $value;
                    $op = ($value === null) ? '=' : $operator;
                    return $this->whereHas('studentDetail', function ($q) use ($op, $val) {
                        $q->where('student_number', $op, $val);
                    });
                }
                return parent::where($column, $operator, $value, $boolean);
            }
        };
    }

    // Accessors & Mutators
    public function getFirstNameAttribute(): string
    {
        if ($this->pendingFirstName !== null) {
            return $this->pendingFirstName;
        }
        if (isset($this->attributes['first_name'])) {
            return (string) $this->attributes['first_name'];
        }
        if ($this->relationLoaded('adminDetail') && $this->getRelation('adminDetail')) {
            return (string) $this->getRelation('adminDetail')->first_name;
        }
        if ($this->relationLoaded('facultyDetail') && $this->getRelation('facultyDetail')) {
            return (string) $this->getRelation('facultyDetail')->first_name;
        }
        if ($this->relationLoaded('studentDetail') && $this->getRelation('studentDetail')) {
            return (string) $this->getRelation('studentDetail')->first_name;
        }

        $uid = $this->id ?? null;
        if (!$uid) return '';

        $row = DB::table('admin_details')->where('user_id', $uid)->value('first_name')
            ?? DB::table('faculty_details')->where('user_id', $uid)->value('first_name')
            ?? DB::table('student_details')->where('user_id', $uid)->value('first_name');

        return (string) ($row ?? '');
    }

    public function setFirstNameAttribute(?string $value): void
    {
        $this->pendingFirstName = $value;
    }

    public function getLastNameAttribute(): string
    {
        if ($this->pendingLastName !== null) {
            return $this->pendingLastName;
        }
        if (isset($this->attributes['last_name'])) {
            return (string) $this->attributes['last_name'];
        }
        if ($this->relationLoaded('adminDetail') && $this->getRelation('adminDetail')) {
            return (string) $this->getRelation('adminDetail')->last_name;
        }
        if ($this->relationLoaded('facultyDetail') && $this->getRelation('facultyDetail')) {
            return (string) $this->getRelation('facultyDetail')->last_name;
        }
        if ($this->relationLoaded('studentDetail') && $this->getRelation('studentDetail')) {
            return (string) $this->getRelation('studentDetail')->last_name;
        }

        $uid = $this->id ?? null;
        if (!$uid) return '';

        $row = DB::table('admin_details')->where('user_id', $uid)->value('last_name')
            ?? DB::table('faculty_details')->where('user_id', $uid)->value('last_name')
            ?? DB::table('student_details')->where('user_id', $uid)->value('last_name');

        return (string) ($row ?? '');
    }

    public function setLastNameAttribute(?string $value): void
    {
        $this->pendingLastName = $value;
    }

    public function getRoleAttribute(): string
    {
        if ($this->pendingRole !== null) {
            return $this->pendingRole;
        }
        if (isset($this->attributes['role'])) {
            return (string) $this->attributes['role'];
        }
        if ($this->relationLoaded('userRole') && $this->getRelation('userRole')) {
            return (string) $this->getRelation('userRole')->role_name;
        }

        $uid = $this->id ?? null;
        if (!$uid) return 'Student';

        $roleName = DB::table('user_roles')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('user_roles.user_id', $uid)
            ->value('roles.role_name');
        if ($roleName) return (string) $roleName;

        return 'Student';
    }

    public function setRoleAttribute(?string $value): void
    {
        $this->pendingRole = $value;
    }

    public function getStudentNumberAttribute(): ?string
    {
        if ($this->pendingStudentNumber !== null) {
            return $this->pendingStudentNumber;
        }
        if (isset($this->attributes['student_number'])) {
            return $this->attributes['student_number'];
        }
        if ($this->relationLoaded('studentDetail') && $this->getRelation('studentDetail')) {
            return $this->getRelation('studentDetail')->student_number;
        }

        $uid = $this->id ?? null;
        if (!$uid) return null;

        return DB::table('student_details')->where('user_id', $uid)->value('student_number');
    }

    public function setStudentNumberAttribute(?string $value): void
    {
        $this->pendingStudentNumber = $value;
    }

    public function getForcePasswordChangeAttribute(): bool
    {
        return (bool) ($this->attributes['is_temp_password'] ?? false);
    }

    public function setForcePasswordChangeAttribute($value): void
    {
        $this->attributes['is_temp_password'] = (int) (bool) $value;
    }

    public function getIsTempPasswordAttribute(): bool
    {
        return (bool) ($this->attributes['is_temp_password'] ?? false);
    }

    public function setIsTempPasswordAttribute($value): void
    {
        $this->attributes['is_temp_password'] = (int) (bool) $value;
    }

    public function save(array $options = [])
    {
        return DB::connection()->transaction(function () use ($options) {
            $saved = parent::save($options);

            if ($saved && $this->id) {
                $uid = (int) $this->id;
                if ($this->pendingStudentNumber !== null) {
                    StudentDetail::updateOrCreate(['user_id' => $uid], ['student_number' => $this->pendingStudentNumber]);
                    $this->pendingStudentNumber = null;
                }
                if ($this->pendingFirstName !== null || $this->pendingLastName !== null) {
                    $fn = $this->first_name;
                    $ln = $this->last_name;
                    $role = ucfirst(strtolower($this->role));
                    if ($role === 'Admin') {
                        AdminDetail::updateOrCreate(['user_id' => $uid], ['first_name' => $fn, 'last_name' => $ln]);
                    } elseif (in_array($role, ['Faculty', 'Dean'], true)) {
                        FacultyDetail::updateOrCreate(['user_id' => $uid], ['first_name' => $fn, 'last_name' => $ln]);
                    } elseif ($role === 'Student') {
                        StudentDetail::updateOrCreate(['user_id' => $uid], ['first_name' => $fn, 'last_name' => $ln]);
                    }
                    $this->pendingFirstName = null;
                    $this->pendingLastName = null;
                }
                if ($this->pendingRole !== null) {
                    $roleName = ucfirst(strtolower((string) $this->pendingRole));
                    $roleId = Role::getIdByName($roleName) ?? 4;
                    UserRole::updateOrCreate(['user_id' => $uid], ['role_id' => $roleId]);
                    $this->pendingRole = null;
                }
            }

            return $saved;
        });
    }

    public function update(array $attributes = [], array $options = [])
    {
        if (array_key_exists('student_number', $attributes)) {
            StudentDetail::updateOrCreate(['user_id' => $this->id], ['student_number' => $attributes['student_number']]);
            $this->pendingStudentNumber = null;
            unset($attributes['student_number']);
        }
        if (array_key_exists('first_name', $attributes) || array_key_exists('last_name', $attributes)) {
            $fn = $attributes['first_name'] ?? $this->first_name;
            $ln = $attributes['last_name'] ?? $this->last_name;
            $role = ucfirst(strtolower($this->role));
            if ($role === 'Admin') {
                AdminDetail::updateOrCreate(['user_id' => $this->id], ['first_name' => $fn, 'last_name' => $ln]);
            } elseif (in_array($role, ['Faculty', 'Dean'], true)) {
                FacultyDetail::updateOrCreate(['user_id' => $this->id], ['first_name' => $fn, 'last_name' => $ln]);
            } elseif ($role === 'Student') {
                StudentDetail::updateOrCreate(['user_id' => $this->id], ['first_name' => $fn, 'last_name' => $ln]);
            }
            $this->pendingFirstName = null;
            $this->pendingLastName = null;
            unset($attributes['first_name'], $attributes['last_name']);
        }
        if (array_key_exists('force_password_change', $attributes)) {
            $attributes['is_temp_password'] = (int) (bool) $attributes['force_password_change'];
            unset($attributes['force_password_change']);
        }
        if (array_key_exists('role', $attributes)) {
            $roleName = ucfirst(strtolower((string) $attributes['role']));
            $roleId = Role::getIdByName($roleName) ?? 4;
            UserRole::updateOrCreate(['user_id' => $this->id], ['role_id' => $roleId]);
            $this->pendingRole = null;
            unset($attributes['role']);
        }

        return parent::update($attributes, $options);
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['first_name'] = $this->first_name;
        $array['last_name'] = $this->last_name;
        $array['role'] = $this->role;
        $array['student_number'] = $this->student_number;
        $array['force_password_change'] = $this->force_password_change;
        $array['is_temp_password'] = $this->is_temp_password;
        return $array;
    }

    /**
     * Intercept create to populate normalized tables: user_roles, admin_details, student_details, faculty_details
     */
    public static function create(array $attributes = [])
    {
        return DB::connection()->transaction(function () use ($attributes) {
            $firstName = trim((string) ($attributes['first_name'] ?? ''));
            $lastName = trim((string) ($attributes['last_name'] ?? ''));
            $middleName = !empty($attributes['middle_name']) ? trim((string) $attributes['middle_name']) : null;
            $role = ucfirst(strtolower(trim((string) ($attributes['role'] ?? 'Student'))));
            $studentNumber = !empty($attributes['student_number']) ? trim((string) $attributes['student_number']) : null;
            $isTempPassword = !empty($attributes['is_temp_password']) || !empty($attributes['force_password_change']) ? 1 : 0;
            $departmentId = !empty($attributes['department_id']) ? (int) $attributes['department_id'] : null;
            $setId = !empty($attributes['set_id']) ? (int) $attributes['set_id'] : null;
            $yearLevel = !empty($attributes['year_level']) ? (int) $attributes['year_level'] : 1;
            $studentStatus = !empty($attributes['status']) && in_array($attributes['status'], ['Regular', 'Irregular'], true)
                ? $attributes['status']
                : (!empty($attributes['student_status']) ? (string) $attributes['student_status'] : 'Regular');

            $userAttrs = [
                'email' => trim((string) ($attributes['email'] ?? '')),
                'password' => $attributes['password'] ?? '',
                'is_temp_password' => $isTempPassword,
                'status' => in_array($attributes['status'] ?? '', ['active', 'inactive'], true) ? $attributes['status'] : 'active',
                'deactivated_at' => $attributes['deactivated_at'] ?? null,
            ];

            $user = new static($userAttrs);
            $user->save();

            if ($user && $user->id) {
                $uid = (int) $user->id;

                // 1. Create user_roles
                $roleId = Role::getIdByName($role) ?? 4;
                UserRole::create([
                    'user_id' => $uid,
                    'role_id' => $roleId,
                ]);

                // 2. Create detail records based on role
                if ($role === 'Admin') {
                    AdminDetail::create([
                        'user_id' => $uid,
                        'first_name' => $firstName ?: 'Admin',
                        'last_name' => $lastName ?: 'User',
                        'middle_name' => $middleName,
                    ]);
                } elseif ($role === 'Dean') {
                    FacultyDetail::create([
                        'user_id' => $uid,
                        'first_name' => $firstName ?: 'Dean',
                        'last_name' => $lastName ?: 'User',
                        'middle_name' => $middleName,
                        'faculty_type' => 'dean',
                        'department_id' => $departmentId,
                    ]);
                } elseif ($role === 'Faculty') {
                    FacultyDetail::create([
                        'user_id' => $uid,
                        'first_name' => $firstName ?: 'Faculty',
                        'last_name' => $lastName ?: 'User',
                        'middle_name' => $middleName,
                        'faculty_type' => 'instructor',
                        'department_id' => $departmentId,
                    ]);
                } elseif ($role === 'Student') {
                    StudentDetail::create([
                        'user_id' => $uid,
                        'student_number' => $studentNumber,
                        'first_name' => $firstName ?: 'Student',
                        'last_name' => $lastName ?: 'User',
                        'middle_name' => $middleName,
                        'set_id' => $setId,
                        'year_level' => $yearLevel,
                        'status' => $studentStatus,
                    ]);
                }

                $user->pendingFirstName = $firstName;
                $user->pendingLastName = $lastName;
                $user->pendingRole = $role;
                $user->pendingStudentNumber = $studentNumber;
            }

            return $user;
        });
    }

    /**
     * Generate a cryptographically secure, random temporary password.
     */
    public static function generateRandomPassword(int $length = 10): string
    {
        $lower = 'abcdefghjkmnpqrstuvwxyz';
        $upper = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        $digits = '23456789';
        $specials = '@#$%&*';

        $password = [
            $lower[random_int(0, strlen($lower) - 1)],
            $upper[random_int(0, strlen($upper) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
            $specials[random_int(0, strlen($specials) - 1)],
        ];

        $all = $lower . $upper . $digits . $specials;
        for ($i = count($password); $i < $length; $i++) {
            $password[] = $all[random_int(0, strlen($all) - 1)];
        }

        shuffle($password);
        return implode('', $password);
    }

    public static function findByEmail(string $email): ?array
    {
        $user = self::where('email', $email)->first();
        return $user ? $user->toArray() : null;
    }

    public static function findByStudentNumber(?string $studentNumber): ?array
    {
        if (empty($studentNumber)) {
            return null;
        }
        $detail = StudentDetail::where('student_number', $studentNumber)->first();
        if (!$detail) {
            return null;
        }
        $user = self::find($detail->user_id);
        return $user ? $user->toArray() : null;
    }

    public static function getStudents(): array
    {
        $users = self::whereHas('userRole', function ($q) {
            $q->where('role_id', 4);
        })
        ->get();

        return $users->sortBy(fn($u) => $u->last_name . ' ' . $u->first_name)->values()->toArray();
    }

    public static function getFaculty(): array
    {
        $users = self::whereHas('userRole', function ($q) {
            $q->where('role_id', 3);
        })
        ->with(['faculty.department'])
        ->get();

        return $users->sortBy(fn($u) => $u->last_name . ' ' . $u->first_name)->values()->toArray();
    }
}
