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
        $role = (string) $request->get('role', '');
        $status = (string) $request->get('status', '');
        $users = $this->userRepository->paginate($page, 20, $role, $status);

        $html = (new View())->render('admin.users.index', [
            'users' => $users,
            'currentRole' => $role,
            'currentStatus' => $status,
            'currentUserId' => (int) ($session->get('user')['id'] ?? 0),
        ]);
        $response->html($html);
    }

    public function create(Request $request, Response $response, Session $session): void
    {
        $departments = \App\Models\Department::getActive();
        $academicTerm = \App\Models\AcademicTerm::getActive();
        $sections = $academicTerm ? \App\Models\Section::getActiveByTerm((int) $academicTerm['id']) : [];
        $html = (new View())->render('admin.users.create', [
            'departments' => $departments,
            'sections' => $sections,
        ]);
        $response->html($html);
    }

    public function store(Request $request, Response $response, Session $session): void
    {
        $data = $request->all();
        $validator = new \App\Validators\UserValidator();

        if (!$validator->validate($data)) {
            $session->flash('error', $validator->firstError());
            redirect('/admin/users/create');
            return;
        }

        $plainPassword = \App\Models\User::generateRandomPassword();
        $role = (string) $data['role'];
        $forceChange = true;

        $studentNumber = null;
        if ($role === 'Student') {
            $studentNumber = trim((string) ($data['student_number'] ?? '')) ?: null;
        }

        $user = \App\Models\User::create([
            'first_name' => trim((string) $data['first_name']),
            'last_name' => trim((string) $data['last_name']),
            'email' => trim((string) $data['email']),
            'student_number' => $studentNumber,
            'password' => password_hash($plainPassword, PASSWORD_BCRYPT),
            'role' => $role,
            'status' => $data['status'] ?? 'active',
            'force_password_change' => $forceChange,
        ]);

        if ($user && $user->id) {
            if ($role === 'Student') {
                $sectionId = !empty($data['section_id']) ? (int) $data['section_id'] : null;
                $yearLevel = !empty($data['year_level']) ? (int) $data['year_level'] : 1;
                $studentStatus = !empty($data['student_status']) ? (string) $data['student_status'] : 'Regular';
                \App\Models\Student::firstOrCreate([
                    'user_id' => (int) $user->id,
                ], [
                    'section_id' => $sectionId,
                    'year_level' => $yearLevel,
                    'status' => $studentStatus,
                ]);
            } elseif (in_array($role, ['Faculty', 'Dean'], true)) {
                $deptId = !empty($data['department_id']) ? (int) $data['department_id'] : null;
                \App\Models\Faculty::updateOrCreate(
                    ['user_id' => (int) $user->id],
                    ['department_id' => $deptId]
                );
            }

            (new \App\Services\NotificationService())->sendStudentCredentials($user->toArray(), $plainPassword);
        }

        $session->flash('success', 'User created successfully.');
        redirect('/admin/users');
    }

    public function edit(Request $request, Response $response, Session $session, string $id): void
    {
        $user = \App\Models\User::with(['faculty.department', 'student.section'])->find((int) $id);
        if (!$user) {
            $response->statusCode(404)->html('User not found');
            return;
        }

        $departments = \App\Models\Department::getActive();
        $academicTerm = \App\Models\AcademicTerm::getActive();
        $sections = $academicTerm ? \App\Models\Section::getActiveByTerm((int) $academicTerm['id']) : [];
        $html = (new View())->render('admin.users.edit', [
            'user' => $user->toArray(),
            'departments' => $departments,
            'sections' => $sections,
        ]);
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
        // Email and system role are locked to existing database record
        $data['email'] = $user->email;
        $data['role'] = $user->role;

        $validator = new \App\Validators\UserValidator();

        if (!$validator->validate($data, (int) $user->id)) {
            $session->flash('error', $validator->firstError());
            redirect('/admin/users/' . $id . '/edit');
            return;
        }

        $updateData = [
            'first_name' => trim((string) $data['first_name']),
            'last_name' => trim((string) $data['last_name']),
            'status' => $data['status'] ?? $user->status,
        ];

        if ($user->role === 'Student') {
            $studentNumber = trim((string) ($data['student_number'] ?? '')) ?: null;
            $updateData['student_number'] = $studentNumber;

            $sectionId = !empty($data['section_id']) ? (int) $data['section_id'] : null;
            $yearLevel = !empty($data['year_level']) ? (int) $data['year_level'] : 1;
            $studentStatus = !empty($data['student_status']) ? (string) $data['student_status'] : 'Regular';

            \App\Models\Student::updateOrCreate(
                ['user_id' => (int) $user->id],
                [
                    'section_id' => $sectionId,
                    'year_level' => $yearLevel,
                    'status' => $studentStatus,
                ]
            );
        } elseif (in_array($user->role, ['Faculty', 'Dean'], true)) {
            $deptId = !empty($data['department_id']) ? (int) $data['department_id'] : null;
            \App\Models\Faculty::updateOrCreate(
                ['user_id' => (int) $user->id],
                ['department_id' => $deptId]
            );
        }

        $user->update($updateData);

        $session->flash('success', 'User updated successfully.');
        redirect('/admin/users');
    }

    public function toggleStatus(Request $request, Response $response, Session $session, string $id): void
    {
        $user = \App\Models\User::find((int) $id);
        if (!$user) {
            $session->flash('error', 'User account not found.');
            redirect('/admin/users');
            return;
        }

        $currentUserId = (int) ($session->get('user')['id'] ?? 0);
        if ((int) $user->id === $currentUserId) {
            $session->flash('error', 'You cannot deactivate your own active account.');
            redirect('/admin/users');
            return;
        }

        if ($user->role === 'Admin' && $user->status === 'active') {
            $session->flash('error', 'Administrator accounts cannot be deactivated to prevent system lockout.');
            redirect('/admin/users');
            return;
        }

        $newStatus = ($user->status === 'inactive') ? 'active' : 'inactive';
        $user->status = $newStatus;
        $user->save();

        $actionText = ($newStatus === 'inactive') ? 'deactivated' : 'activated';
        $fullName = trim($user->first_name . ' ' . $user->last_name);
        $session->flash('success', "User account for '{$fullName}' has been {$actionText} successfully.");
        redirect('/admin/users');
    }

    public function deactivate(Request $request, Response $response, Session $session, string $id): void
    {
        $user = \App\Models\User::find((int) $id);
        if (!$user) {
            $session->flash('error', 'User account not found.');
            redirect('/admin/users');
            return;
        }

        $currentUserId = (int) ($session->get('user')['id'] ?? 0);
        if ((int) $user->id === $currentUserId) {
            $session->flash('error', 'You cannot deactivate your own active account.');
            redirect('/admin/users');
            return;
        }

        if ($user->role === 'Admin') {
            $session->flash('error', 'Administrator accounts cannot be deactivated to prevent system lockout.');
            redirect('/admin/users');
            return;
        }

        $user->status = 'inactive';
        $user->save();

        $fullName = trim($user->first_name . ' ' . $user->last_name);
        $session->flash('success', "User account for '{$fullName}' has been deactivated successfully.");
        redirect('/admin/users');
    }

    public function activate(Request $request, Response $response, Session $session, string $id): void
    {
        $user = \App\Models\User::find((int) $id);
        if (!$user) {
            $session->flash('error', 'User account not found.');
            redirect('/admin/users');
            return;
        }

        $user->status = 'active';
        $user->save();

        $fullName = trim($user->first_name . ' ' . $user->last_name);
        $session->flash('success', "User account for '{$fullName}' has been activated successfully.");
        redirect('/admin/users');
    }
}

