<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function findById(int $id): ?array
    {
        $user = User::find($id);
        return $user ? $user->toArray() : null;
    }

    public function findByEmail(string $email): ?array
    {
        return User::findByEmail($email);
    }

    public function create(array $data): int
    {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $data['created_at'] = date('Y-m-d H:i:s');
        $user = User::create($data);
        return (int) $user->id;
    }

    public function update(int $id, array $data): bool
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        return (bool) User::where('id', $id)->update($data);
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

    public function paginate(int $page = 1, int $perPage = 20, string $role = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $where = $role ? "WHERE u.role = :role" : "";
        $params = $role ? ['role' => $role] : [];

        $countSql = "SELECT COUNT(*) FROM users u {$where}";
        $stmt = \App\Core\Database::getConnection()->prepare($countSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT u.*, d.name AS department_name, d.code AS department_code
                FROM users u
                LEFT JOIN faculty f ON f.user_id = u.id
                LEFT JOIN departments d ON d.id = f.department_id
                {$where}
                ORDER BY u.last_name, u.first_name
                LIMIT :limit OFFSET :offset";
        $stmt = \App\Core\Database::getConnection()->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'lastPage' => (int) ceil($total / $perPage),
        ];
    }
}
