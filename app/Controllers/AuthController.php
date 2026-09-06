<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AuthService;
use App\Validators\LoginValidator;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function showLogin(Request $request, Response $response, Session $session): void
    {
        if (isset($_SESSION['user']['id'])) {
            if (!empty($_SESSION['user']['force_password_change'])) {
                redirect('/change-password');
                return;
            }
            redirect($this->authService->getDashboardRoute($_SESSION['user']['role']));
            return;
        }

        $html = (new \App\Core\View())->render('auth.login', [
            'error' => $session->getFlash('error'),
        ]);
        $response->html($html);
    }

    public function login(Request $request, Response $response, Session $session): void
    {
        $validator = new LoginValidator();
        $data = $request->all();

        if (!$validator->validate($data)) {
            $session->flash('error', $validator->firstError());
            redirect('/login');
            return;
        }

        $success = $this->authService->login($data['email'], $data['password'], $session);

        if ($success) {
            if (!empty($_SESSION['user']['force_password_change'])) {
                $session->flash('info', 'You are signed in with temporary credentials. Please set your new password.');
                redirect('/change-password');
                return;
            }
            $role = $_SESSION['user']['role'];
            redirect($this->authService->getDashboardRoute($role));
        } else {
            redirect('/login');
        }
    }

    public function showChangePassword(Request $request, Response $response, Session $session): void
    {
        if (!isset($_SESSION['user']['id'])) {
            redirect('/login');
            return;
        }

        $html = (new \App\Core\View())->render('auth.change-password', [
            'error' => $session->getFlash('error'),
            'info' => $session->getFlash('info'),
            'success' => $session->getFlash('success'),
            'user' => $_SESSION['user'] ?? [],
        ]);
        $response->html($html);
    }

    public function changePassword(Request $request, Response $response, Session $session): void
    {
        $userId = (int) ($_SESSION['user']['id'] ?? 0);
        if ($userId <= 0) {
            $session->flash('error', 'Session expired. Please log in again.');
            redirect('/login');
            return;
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            $session->flash('error', 'User account not found.');
            redirect('/login');
            return;
        }

        $newPassword = (string) $request->post('new_password', '');
        $confirmPassword = (string) $request->post('confirm_password', '');

        if (empty($newPassword) || empty($confirmPassword)) {
            $session->flash('error', 'Please enter and confirm your new password.');
            redirect('/change-password');
            return;
        }

        if (strlen($newPassword) < 8) {
            $session->flash('error', 'New password must be at least 8 characters long.');
            redirect('/change-password');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $session->flash('error', 'New password and confirmation do not match.');
            redirect('/change-password');
            return;
        }

        if (password_verify($newPassword, $user->password)) {
            $session->flash('error', 'Your new password cannot be the same as your temporary password.');
            redirect('/change-password');
            return;
        }

        // Update password and clear force_password_change flag using Eloquent ORM
        $user->password = password_hash($newPassword, PASSWORD_BCRYPT);
        $user->force_password_change = false;
        $user->save();

        $_SESSION['user']['force_password_change'] = false;

        $session->flash('success', 'Your password has been changed successfully! Welcome to Acadtrack.');
        $role = $_SESSION['user']['role'];
        redirect($this->authService->getDashboardRoute($role));
    }

    public function logout(Request $request, Response $response, Session $session): void
    {
        $this->authService->logout($session);
        redirect('/login');
    }
}
