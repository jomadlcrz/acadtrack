<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use App\Core\Session;

class AuthService
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function login(string $email, string $password, Session $session): bool
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $session->flash('error', 'Invalid email or password.');
            return false;
        }

        if (isset($user['status']) && $user['status'] === 'inactive') {
            $session->flash('error', 'Your account has been deactivated.');
            return false;
        }

        $session->set('user', [
            'id' => $user['id'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role' => $user['role'],
            'student_number' => $user['student_number'] ?? null,
        ]);

        $session->regenerate();
        return true;
    }

    public function logout(Session $session): void
    {
        $session->destroy();
    }

    public function register(array $data, Session $session): int|false
    {
        $existing = $this->userRepository->findByEmail($data['email']);
        if ($existing) {
            $session->flash('error', 'Email already registered.');
            return false;
        }

        return $this->userRepository->create($data);
    }

    public function getDashboardRoute(string $role): string
    {
        return match ($role) {
            'Admin' => '/admin/dashboard',
            'Dean' => '/dean/dashboard',
            'Faculty' => '/faculty/dashboard',
            'Student' => '/student/dashboard',
            default => '/dashboard',
        };
    }
}
