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
        $plainPassword = $data['password'] ?? '';
        $userId = $this->userRepository->create($data);

        if ($userId > 0 && !empty($plainPassword)) {
            $createdUser = $this->userRepository->findById($userId);
            if ($createdUser) {
                (new \App\Services\NotificationService())->sendStudentCredentials($createdUser, $plainPassword);
            }
        }

        $session->flash('success', 'User created successfully.');
        redirect('/admin/users');
    }

    public function edit(Request $request, Response $response, Session $session, string $id): void
    {
        $user = $this->userRepository->findById((int) $id);
        if (!$user) {
            $response->statusCode(404)->html('User not found');
            return;
        }

        $html = (new View())->render('admin.users.edit', ['user' => $user]);
        $response->html($html);
    }

    public function update(Request $request, Response $response, Session $session, string $id): void
    {
        $data = $request->all();
        unset($data['password']);
        if (!empty($data['password'])) {
            $data['password'] = $data['password'];
        } else {
            unset($data['password']);
        }

        $this->userRepository->update((int) $id, $data);
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
