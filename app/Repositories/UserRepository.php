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
     * Student accounts directory with optional filtering.
     *
     * @param array{status?:string,year_level?:int,set_id?:int,account_status?:string,search?:string,term_id?:int}|int $filters
     * @param int|array $pageOrFilters
     * @param int $page
     * @param int $perPage
     * @return array{data:array,total:int,page:int,perPage:int,per_page:int,lastPage:int,last_page:int}
     */
    public function paginateStudents(array|int $filters = [], int|array $pageOrFilters = 1, int $page = 1, int $perPage = 20): array
    {
        if (is_int($filters)) {
            $termId = $filters;
            $filters = is_array($pageOrFilters) ? $pageOrFilters : [];
            if ($termId > 0 && !isset($filters['term_id'])) {
                $filters['term_id'] = $termId;
            }
        } elseif (is_int($pageOrFilters)) {
            $page = $pageOrFilters;
        }

        $page = max(1, $page);
        $clauses = ["r.role_name = 'Student'"];
        $params = [];

        if (!empty($filters['status'])) {
            $clauses[] = 'COALESCE(sd.status, s.status) = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['year_level'])) {
            $clauses[] = 'COALESCE(sd.year_level, s.year_level) = :year_level';
            $params['year_level'] = (int) $filters['year_level'];
        }
        if (!empty($filters['set_id'])) {
            $clauses[] = 'COALESCE(sd.set_id, s.set_id) = :set_id';
            $params['set_id'] = (int) $filters['set_id'];
        }
        if (!empty($filters['account_status'])) {
            $clauses[] = 'u.status = :account_status';
            $params['account_status'] = $filters['account_status'];
        }
        if (!empty($filters['search'])) {
            $clauses[] = "CONCAT(
                u.email, ' ', 
                COALESCE(sd.first_name, ''), ' ', 
                COALESCE(sd.last_name, ''), ' ', 
                COALESCE(sd.student_number, '')
            ) LIKE :search";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['term_id'])) {
            $clauses[] = 'EXISTS (SELECT 1 FROM student_term_registrations str WHERE str.student_id = s.id AND str.academic_term_id = :term_id)';
            $params['term_id'] = (int) $filters['term_id'];
        }
        $where = 'WHERE ' . implode(' AND ', $clauses);

        $from = "FROM users u
                 JOIN user_roles ur ON ur.user_id = u.id
                 JOIN roles r ON r.id = ur.role_id
                 LEFT JOIN students s ON s.user_id = u.id
                 LEFT JOIN student_details sd ON sd.user_id = u.id
                 LEFT JOIN sets st ON st.id = COALESCE(sd.set_id, s.set_id)
                 {$where}";

        $stmt = Database::getConnection()->prepare("SELECT COUNT(DISTINCT u.id) {$from}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $stmt = Database::getConnection()->prepare(
            "SELECT u.id, u.email, u.status, 'Student' AS role,
                    COALESCE(sd.first_name, '') AS first_name,
                    COALESCE(sd.last_name, '') AS last_name,
                    COALESCE(sd.student_number, '') AS student_number,
                    COALESCE(sd.year_level, s.year_level, 1) AS year_level,
                    COALESCE(sd.status, s.status, 'Regular') AS student_status,
                    'enrolled' AS registration_status,
                    COALESCE(sd.set_id, s.set_id) AS set_id,
                    st.set_name
             {$from}
             ORDER BY COALESCE(sd.year_level, s.year_level, 1), sd.last_name, sd.first_name
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
                    fd.department_id,
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

    /** Faculty and Dean accounts. */
    public function paginateFaculty(int $page = 1, int $perPage = 20, string $role = '', string $status = '', string $search = '', int $departmentId = 0): array
    {
        $page = max(1, $page);
        $clauses = ["r.role_name IN ('Faculty', 'Dean')"];
        $params = [];

        if ($role !== '' && in_array($role, ['Faculty', 'Dean'], true)) {
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
            $clauses[] = "CONCAT(u.email, ' ', COALESCE(fd.first_name, ''), ' ', COALESCE(fd.last_name, '')) LIKE :search";
            $params['search'] = '%' . $search . '%';
        }
        $where = 'WHERE ' . implode(' AND ', $clauses);

        $from = "FROM users u
                 JOIN user_roles ur ON ur.user_id = u.id
                 JOIN roles r ON r.id = ur.role_id
                 LEFT JOIN faculty_details fd ON fd.user_id = u.id
                 LEFT JOIN departments d ON d.id = fd.department_id
                 {$where}";

        $stmt = Database::getConnection()->prepare("SELECT COUNT(*) {$from}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $stmt = Database::getConnection()->prepare(
            "SELECT u.id, u.email, u.status, r.role_name AS role,
                    COALESCE(fd.first_name, '') AS first_name,
                    COALESCE(fd.last_name, '') AS last_name,
                    fd.department_id,
                    d.dept_name AS department_name, d.dept_abbrev AS department_code
             {$from}
             ORDER BY COALESCE(fd.last_name, ''), COALESCE(fd.first_name, '')
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

    /** Administrator accounts. */
    public function paginateAdministrators(int $page = 1, int $perPage = 20, string $status = '', string $search = ''): array
    {
        $page = max(1, $page);
        $clauses = ["r.role_name = 'Admin'"];
        $params = [];

        if ($status !== '') {
            $clauses[] = 'u.status = :status';
            $params['status'] = $status;
        }
        if ($search !== '') {
            $clauses[] = "CONCAT(u.email, ' ', COALESCE(ad.first_name, ''), ' ', COALESCE(ad.last_name, '')) LIKE :search";
            $params['search'] = '%' . $search . '%';
        }
        $where = 'WHERE ' . implode(' AND ', $clauses);

        $from = "FROM users u
                 JOIN user_roles ur ON ur.user_id = u.id
                 JOIN roles r ON r.id = ur.role_id
                 LEFT JOIN admin_details ad ON ad.user_id = u.id
                 {$where}";

        $stmt = Database::getConnection()->prepare("SELECT COUNT(*) {$from}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $stmt = Database::getConnection()->prepare(
            "SELECT u.id, u.email, u.status, r.role_name AS role,
                    COALESCE(ad.first_name, '') AS first_name,
                    COALESCE(ad.last_name, '') AS last_name
             {$from}
             ORDER BY COALESCE(ad.last_name, ''), COALESCE(ad.first_name, '')
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

