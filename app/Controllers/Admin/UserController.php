<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\UserRepository;

class UserController
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function index(Request $request, Response $response, Session $session): void
    {
        $page = (int) $request->get('page', 1);
        $role = $request->get('role', '');
        $users = $this->userRepository->paginate($page, 20, $role);

        $html = (new View())->render('admin.users.index', [
            'users' => $users,
            'currentRole' => $role,
        ]);
        $response->html($html);
    }

    public function create(Request $request, Response $response, Session $session): void
    {
        $html = (new View())->render('admin.users.create');
        $response->html($html);
    }

    public function store(Request $request, Response $response, Session $session): void
    {
        $data = $request->all();
        $plainPassword = trim((string) ($data['password'] ?? ''));
        $role = (string) ($data['role'] ?? 'Student');
        $forceChange = !empty($request->post('force_password_change')) || $role === 'Student' || empty($plainPassword);

        if (empty($plainPassword) || $plainPassword === 'student123') {
            $plainPassword = \App\Models\User::generateRandomPassword();
        }

        $user = \App\Models\User::create([
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'email' => $data['email'] ?? '',
            'student_number' => !empty($data['student_number']) ? $data['student_number'] : null,
            'password' => password_hash($plainPassword, PASSWORD_BCRYPT),
            'role' => $role,
            'status' => $data['status'] ?? 'active',
            'force_password_change' => $forceChange,
        ]);

        if ($user && $user->id) {
            if ($role === 'Student') {
                \App\Models\Student::firstOrCreate([
                    'user_id' => (int) $user->id,
                ], [
                    'year_level' => 1,
                    'status' => 'Regular',
                ]);
            }

            (new \App\Services\NotificationService())->sendStudentCredentials($user->toArray(), $plainPassword);
        }

        $session->flash('success', 'User created successfully.');
        redirect('/admin/users');
    }

    public function edit(Request $request, Response $response, Session $session, string $id): void
    {
        $user = \App\Models\User::find((int) $id);
        if (!$user) {
            $response->statusCode(404)->html('User not found');
            return;
        }

        $html = (new View())->render('admin.users.edit', ['user' => $user->toArray()]);
        $response->html($html);
    }

    public function update(Request $request, Response $response, Session $session, string $id): void
    {
        $user = \App\Models\User::find((int) $id);
        if (!$user) {
            $response->statusCode(404)->html('User not found');
            return;
        }

        $data = $request->all();
        $updateData = [
            'first_name' => $data['first_name'] ?? $user->first_name,
            'last_name' => $data['last_name'] ?? $user->last_name,
            'email' => $data['email'] ?? $user->email,
            'role' => $data['role'] ?? $user->role,
            'status' => $data['status'] ?? $user->status,
        ];

        if (isset($data['student_number'])) {
            $updateData['student_number'] = !empty($data['student_number']) ? $data['student_number'] : null;
        }

        if (!empty($data['password'])) {
            $updateData['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if ($request->has('force_password_change')) {
            $updateData['force_password_change'] = (bool) $request->post('force_password_change');
        }

        $user->update($updateData);
        $session->flash('success', 'User updated successfully.');
        redirect('/admin/users');
    }

    public function destroy(Request $request, Response $response, Session $session, string $id): void
    {
        $this->userRepository->delete((int) $id);
        $session->flash('success', 'User deleted successfully.');
        redirect('/admin/users');
    }
}
