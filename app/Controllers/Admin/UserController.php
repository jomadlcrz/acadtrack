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
        $plainPassword = trim((string) ($data['password'] ?? ''));
        $role = (string) ($data['role'] ?? 'Student');
        $forceChange = !empty($request->post('force_password_change')) || $role === 'Student' || empty($plainPassword);

        if (empty($plainPassword)) {
            $plainPassword = \App\Models\User::generateRandomPassword();
        }

        $studentNumber = null;
        if ($role === 'Student') {
            $studentNumber = trim((string) ($data['student_number'] ?? '')) ?: null;
            if ($studentNumber !== null && \App\Models\User::where('student_number', $studentNumber)->exists()) {
                $session->flash('error', "Student ID number '{$studentNumber}' is already registered to another user.");
                redirect('/admin/users/create');
                return;
            }
        }

        $user = \App\Models\User::create([
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'email' => $data['email'] ?? '',
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
        $updateData = [
            'first_name' => $data['first_name'] ?? $user->first_name,
            'last_name' => $data['last_name'] ?? $user->last_name,
            'email' => $data['email'] ?? $user->email,
            'role' => $data['role'] ?? $user->role,
            'status' => $data['status'] ?? $user->status,
        ];

        $newRole = $updateData['role'] ?? $user->role;
        if ($newRole === 'Student') {
            $studentNumber = trim((string) ($data['student_number'] ?? '')) ?: null;
            if ($studentNumber !== null && \App\Models\User::where('student_number', $studentNumber)->where('id', '!=', $user->id)->exists()) {
                $session->flash('error', "Student ID number '{$studentNumber}' is already registered to another user.");
                redirect('/admin/users/' . $id . '/edit');
                return;
            }
            $updateData['student_number'] = $studentNumber;
            \App\Models\Faculty::where('user_id', $user->id)->delete();

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
        } else {
            $updateData['student_number'] = null;
            if (in_array($newRole, ['Faculty', 'Dean'], true)) {
                $deptId = !empty($data['department_id']) ? (int) $data['department_id'] : null;
                \App\Models\Faculty::updateOrCreate(
                    ['user_id' => (int) $user->id],
                    ['department_id' => $deptId]
                );
            } else {
                \App\Models\Faculty::where('user_id', $user->id)->delete();
            }
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
