<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\Models\UserRole;
use App\Models\AdminDetail;
use App\Models\FacultyDetail;
use App\Models\StudentDetail;
use App\Core\Database;
use PDO;

class UserRepository
{
    public function findById(int $id): ?array
    {
        $user = User::with(['userRole', 'adminDetail', 'facultyDetail', 'studentDetail'])->find($id);
        return $user ? $user->toArray() : null;
    }

    public function findByEmail(string $email): ?array
    {
        return User::findByEmail($email);
    }

    public function create(array $data): int
    {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $user = User::create($data);
        return (int) $user->id;
    }

    public function update(int $id, array $data): bool
    {
        $user = User::find($id);
        if (!$user) {
            return false;
        }

        $userAttrs = [];
        if (isset($data['email'])) {
            $userAttrs['email'] = trim((string) $data['email']);
        }
        if (isset($data['password'])) {
            $userAttrs['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        if (isset($data['status'])) {
            $userAttrs['status'] = $data['status'];
            if ($data['status'] === 'inactive' && empty($user->deactivated_at)) {
                $userAttrs['deactivated_at'] = date('Y-m-d H:i:s');
            } elseif ($data['status'] === 'active') {
                $userAttrs['deactivated_at'] = null;
            }
        }
        if (isset($data['is_temp_password'])) {
            $userAttrs['is_temp_password'] = (int) (bool) $data['is_temp_password'];
        } elseif (isset($data['force_password_change'])) {
            $userAttrs['is_temp_password'] = (int) (bool) $data['force_password_change'];
        }

        if (!empty($userAttrs)) {
            $user->update($userAttrs);
        }

        $firstName = trim((string) ($data['first_name'] ?? ''));
        $lastName = trim((string) ($data['last_name'] ?? ''));
        $middleName = isset($data['middle_name']) ? trim((string) $data['middle_name']) : null;

        // Update role if provided
        if (!empty($data['role'])) {
            UserRole::updateOrCreate(
                ['user_id' => $id],
                ['role_name' => $data['role']]
            );
        }

        $role = $user->role;

        if ($role === 'Admin') {
            if ($firstName || $lastName) {
                AdminDetail::updateOrCreate(
                    ['user_id' => $id],
                    array_filter([
                        'first_name' => $firstName ?: null,
                        'last_name' => $lastName ?: null,
                        'middle_name' => $middleName,
                    ])
                );
            }
        } elseif (in_array($role, ['Faculty', 'Dean'], true)) {
            $deptId = isset($data['department_id']) ? (int) $data['department_id'] : null;
            $fType = $role === 'Dean' ? 'dean' : 'instructor';
            FacultyDetail::updateOrCreate(
                ['user_id' => $id],
                array_filter([
                    'first_name' => $firstName ?: null,
                    'last_name' => $lastName ?: null,
                    'middle_name' => $middleName,
                    'faculty_type' => $fType,
                    'department_id' => $deptId ?: null,
                ])
            );
        } elseif ($role === 'Student') {
            $studentNumber = isset($data['student_number']) ? trim((string) $data['student_number']) : null;
            $setId = isset($data['set_id']) ? (int) $data['set_id'] : null;
            $yearLevel = isset($data['year_level']) ? (int) $data['year_level'] : null;
            $status = isset($data['status']) && in_array($data['status'], ['Regular', 'Irregular'], true)
                ? $data['status']
                : (isset($data['student_status']) ? $data['student_status'] : null);

            StudentDetail::updateOrCreate(
                ['user_id' => $id],
                array_filter([
                    'first_name' => $firstName ?: null,
                    'last_name' => $lastName ?: null,
                    'middle_name' => $middleName,
                    'student_number' => $studentNumber,
                    'set_id' => $setId,
                    'year_level' => $yearLevel,
                    'status' => $status,
                ])
            );
        }

        return true;
    }

    public function delete(int $id): bool
    {
        return (bool) User::destroy($id);
    }

    public function getStudents(): array
    {
        return User::getStudents();
    }

    public function getFaculty(): array
    {
        return User::getFaculty();
    }

    public function paginate(int $page = 1, int $perPage = 20, string $role = '', string $status = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $clauses = [];
        $params = [];
        if ($role !== '') {
            $clauses[] = "ur.role_name = :role";
            $params['role'] = $role;
        }
        if ($status !== '') {
            $clauses[] = "u.status = :status";
            $params['status'] = $status;
        }

        $where = !empty($clauses) ? "WHERE " . implode(" AND ", $clauses) : "";

        $countSql = "SELECT COUNT(*) FROM users u LEFT JOIN user_roles ur ON ur.user_id = u.id {$where}";
        $stmt = Database::getConnection()->prepare($countSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT u.id, u.email, u.status, u.is_temp_password, u.created_at, u.deactivated_at,
                       COALESCE(ad.first_name, fd.first_name, sd.first_name, '') AS first_name,
                       COALESCE(ad.last_name, fd.last_name, sd.last_name, '') AS last_name,
                       COALESCE(ur.role_name, 'Student') AS role,
                       sd.student_number,
                       fd.faculty_type,
                       d.name AS department_name, d.code AS department_code, sec.set_name AS set_name
                FROM users u
                LEFT JOIN user_roles ur ON ur.user_id = u.id
                LEFT JOIN admin_details ad ON ad.user_id = u.id
                LEFT JOIN faculty_details fd ON fd.user_id = u.id
                LEFT JOIN student_details sd ON sd.user_id = u.id
                LEFT JOIN departments d ON d.id = fd.department_id
                LEFT JOIN sets sec ON sec.id = sd.set_id
                {$where}
                ORDER BY COALESCE(ad.last_name, fd.last_name, sd.last_name, ''), COALESCE(ad.first_name, fd.first_name, sd.first_name, '')
                LIMIT :limit OFFSET :offset";
        $stmt = Database::getConnection()->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'lastPage' => (int) ceil($total / $perPage),
        ];
    }
}
