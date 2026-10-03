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
            $roleId = \App\Models\Role::getIdByName($data['role']) ?? 4;
            UserRole::updateOrCreate(
                ['user_id' => $id],
                ['role_id' => $roleId]
            );
        }

        $role = $user->role;

        if ($role === 'Admin') {
            if ($firstName || $lastName) {
                AdminDetail::updateOrCreate(
                    ['user_id' => $id],
                    array_filter([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'middle_name' => $middleName,
                        'phone_number' => $data['phone_number'] ?? null,
                    ], fn($val) => $val !== null && $val !== '')
                );
            }
        } elseif (in_array($role, ['Faculty', 'Dean'], true)) {
            if ($firstName || $lastName || isset($data['department_id'])) {
                FacultyDetail::updateOrCreate(
                    ['user_id' => $id],
                    array_filter([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'middle_name' => $middleName,
                        'department_id' => !empty($data['department_id']) ? (int) $data['department_id'] : null,
                        'faculty_type' => $role === 'Dean' ? 'dean' : 'instructor',
                    ], fn($val) => $val !== null && $val !== '')
                );
            }
        } elseif ($role === 'Student') {
            if ($firstName || $lastName || isset($data['student_number']) || isset($data['set_id']) || isset($data['year_level'])) {
                StudentDetail::updateOrCreate(
                    ['user_id' => $id],
                    array_filter([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'middle_name' => $middleName,
                        'student_number' => !empty($data['student_number']) ? trim((string) $data['student_number']) : null,
                        'set_id' => !empty($data['set_id']) ? (int) $data['set_id'] : null,
                        'year_level' => !empty($data['year_level']) ? (int) $data['year_level'] : null,
                        'status' => !empty($data['student_status']) ? (string) $data['student_status'] : null,
                    ], fn($val) => $val !== null && $val !== '')
                );
            }
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

    public function paginate(int $page = 1, int $perPage = 20, string $role = '', string $status = '', string $search = '', string $enrollmentStatus = ''): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $clauses = [];
        $params = [];
        if ($role !== '') {
            $clauses[] = "r.role_name = :role";
            $params['role'] = $role;
        }
        if ($status !== '') {
            $clauses[] = "u.status = :status";
            $params['status'] = $status;
        }
        if ($enrollmentStatus !== '') {
            $clauses[] = "sd.status = :enrollment_status";
            $params['enrollment_status'] = $enrollmentStatus;
        }
        if ($search !== '') {
            $clauses[] = "CONCAT(
                u.email, ' ', 
                COALESCE(ad.first_name, ''), ' ', COALESCE(ad.last_name, ''), ' ', 
                COALESCE(fd.first_name, ''), ' ', COALESCE(fd.last_name, ''), ' ', 
                COALESCE(sd.first_name, ''), ' ', COALESCE(sd.last_name, ''), ' ', 
                COALESCE(sd.student_number, '')
            ) LIKE :search";
            $params['search'] = '%' . $search . '%';
        }

        $where = !empty($clauses) ? "WHERE " . implode(" AND ", $clauses) : "";

        $countSql = "SELECT COUNT(*) FROM users u 
                     LEFT JOIN user_roles ur ON ur.user_id = u.id 
                     LEFT JOIN roles r ON r.id = ur.role_id 
                     LEFT JOIN admin_details ad ON ad.user_id = u.id
                     LEFT JOIN faculty_details fd ON fd.user_id = u.id
                     LEFT JOIN student_details sd ON sd.user_id = u.id
                     {$where}";
        $stmt = Database::getConnection()->prepare($countSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT u.id, u.email, u.status, u.is_temp_password, u.created_at, u.deactivated_at,
                       COALESCE(ad.first_name, fd.first_name, sd.first_name, '') AS first_name,
                       COALESCE(ad.last_name, fd.last_name, sd.last_name, '') AS last_name,
                       COALESCE(r.role_name, 'Student') AS role,
                       sd.student_number,
                       sd.year_level AS student_year_level,
                       sd.status AS student_status,
                       fd.faculty_type,
                       d.dept_name AS department_name, d.dept_abbrev AS department_code, sec.set_name AS set_name
                FROM users u
                LEFT JOIN user_roles ur ON ur.user_id = u.id
                LEFT JOIN roles r ON r.id = ur.role_id
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
            'per_page' => $perPage,
            'lastPage' => max(1, (int) ceil($total / $perPage)),
            'last_page' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    /**
     * Students registered in a term, with their per-term year level, set and Regular/Irregular status.
     *
     * @param array{status?:string,year_level?:int,set_id?:int,account_status?:string,search?:string} $filters
     */
    public function paginateStudents(int $termId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $clauses = ['r.academic_term_id = :term_id'];
        $params = ['term_id' => $termId];

        if (!empty($filters['status'])) {
            $clauses[] = 'r.status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['year_level'])) {
            $clauses[] = 'r.year_level = :year_level';
            $params['year_level'] = (int) $filters['year_level'];
        }
        if (!empty($filters['set_id'])) {
            $clauses[] = 'r.set_id = :set_id';
            $params['set_id'] = (int) $filters['set_id'];
        }
        if (!empty($filters['account_status'])) {
            $clauses[] = 'u.status = :account_status';
            $params['account_status'] = $filters['account_status'];
        }
        if (!empty($filters['search'])) {
            $clauses[] = "CONCAT(u.email, ' ', COALESCE(sd.first_name, ''), ' ', COALESCE(sd.last_name, ''), ' ', COALESCE(sd.student_number, '')) LIKE :search";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        $where = 'WHERE ' . implode(' AND ', $clauses);

        $from = "FROM student_term_registrations r
                 JOIN students s ON s.id = r.student_id
                 JOIN users u ON u.id = s.user_id
                 LEFT JOIN student_details sd ON sd.user_id = u.id
                 LEFT JOIN sets st ON st.id = r.set_id
                 {$where}";

        $stmt = Database::getConnection()->prepare("SELECT COUNT(*) {$from}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $stmt = Database::getConnection()->prepare(
            "SELECT u.id, u.email, u.status, COALESCE(sd.first_name, '') AS first_name, COALESCE(sd.last_name, '') AS last_name,
                    sd.student_number, r.year_level, r.status AS student_status, r.registration_status, st.set_name
             {$from}
             ORDER BY r.year_level, sd.last_name, sd.first_name
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();

        $lastPage = max(1, (int) ceil($total / $perPage));
        return [
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'per_page' => $perPage,
            'lastPage' => $lastPage,
            'last_page' => $lastPage,
        ];
    }

    /** Admin, Dean and Faculty accounts (everything except students). */
    public function paginateStaff(int $page = 1, int $perPage = 20, string $role = '', string $status = '', string $search = '', int $departmentId = 0): array
    {
        $page = max(1, $page);
        $clauses = ["r.role_name <> 'Student'"];
        $params = [];

        if ($role !== '') {
            $clauses[] = 'r.role_name = :role';
            $params['role'] = $role;
        }
        if ($status !== '') {
            $clauses[] = 'u.status = :status';
            $params['status'] = $status;
        }
        if ($departmentId > 0) {
            $clauses[] = 'fd.department_id = :department_id';
            $params['department_id'] = $departmentId;
        }
        if ($search !== '') {
            $clauses[] = "CONCAT(u.email, ' ', COALESCE(ad.first_name, ''), ' ', COALESCE(ad.last_name, ''), ' ', COALESCE(fd.first_name, ''), ' ', COALESCE(fd.last_name, '')) LIKE :search";
            $params['search'] = '%' . $search . '%';
        }
        $where = 'WHERE ' . implode(' AND ', $clauses);

        $from = "FROM users u
                 JOIN user_roles ur ON ur.user_id = u.id
                 JOIN roles r ON r.id = ur.role_id
                 LEFT JOIN admin_details ad ON ad.user_id = u.id
                 LEFT JOIN faculty_details fd ON fd.user_id = u.id
                 LEFT JOIN departments d ON d.id = fd.department_id
                 {$where}";

        $stmt = Database::getConnection()->prepare("SELECT COUNT(*) {$from}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $stmt = Database::getConnection()->prepare(
            "SELECT u.id, u.email, u.status, r.role_name AS role,
                    COALESCE(ad.first_name, fd.first_name, '') AS first_name,
                    COALESCE(ad.last_name, fd.last_name, '') AS last_name,
                    d.dept_name AS department_name, d.dept_abbrev AS department_code
             {$from}
             ORDER BY COALESCE(ad.last_name, fd.last_name, ''), COALESCE(ad.first_name, fd.first_name, '')
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();

        $lastPage = max(1, (int) ceil($total / $perPage));
        return [
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'per_page' => $perPage,
            'lastPage' => $lastPage,
            'last_page' => $lastPage,
        ];
    }
}
