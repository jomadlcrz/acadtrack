<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AuthService;
use App\Services\PasswordResetService;
use App\Validators\LoginValidator;

class AuthController
{
    private AuthService $authService;
    private PasswordResetService $passwordResetService;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->passwordResetService = new PasswordResetService();
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

    public function showForgotPassword(Request $request, Response $response, Session $session): void
    {
        if (isset($_SESSION['user']['id'])) {
            redirect($this->authService->getDashboardRoute($_SESSION['user']['role']));
            return;
        }

        $html = (new \App\Core\View())->render('auth.forgot-password', [
            'error' => $session->getFlash('error'),
            'success' => $session->getFlash('success'),
            'info' => $session->getFlash('info'),
        ]);
        $response->html($html);
    }

    public function forgotPassword(Request $request, Response $response, Session $session): void
    {
        $email = trim((string) $request->post('email', ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $session->flash('error', 'Please enter a valid email address.');
            redirect('/forgot-password');
            return;
        }

        $user = \App\Models\User::where('email', $email)->first();

        // Prevent account enumeration by providing a safe confirmation message
        if (!$user) {
            $session->flash('success', 'If an account exists with that email, a password reset link has been sent to your inbox.');
            redirect('/forgot-password');
            return;
        }

        if ($user->status === 'inactive') {
            $session->flash('error', 'This account has been deactivated. Please contact the administrator.');
            redirect('/forgot-password');
            return;
        }

        $token = $this->passwordResetService->createResetToken($email);
        $sent = $this->passwordResetService->sendResetEmail($email, $token);

        if (!$sent) {
            if (env('APP_DEBUG') === 'true') {
                $resetUrl = $this->passwordResetService->getResetUrl($token);
                $session->flash('info', 'Notice: Email delivery failed via SMTP. Local reset URL: ' . $resetUrl);
            } else {
                $session->flash('error', 'Unable to send recovery email at this moment. Please contact the system administrator.');
            }
            redirect('/forgot-password');
            return;
        }

        $session->flash('success', 'A password reset link has been sent to ' . htmlspecialchars($email) . '. Please check your inbox.');
        redirect('/forgot-password');
    }

    public function showResetPassword(Request $request, Response $response, Session $session): void
    {
        $token = (string) $request->get('token', '');

        if (empty($token) || !$this->passwordResetService->validateToken($token)) {
            $session->flash('error', 'This password reset link is invalid or has expired.');
            redirect('/forgot-password');
            return;
        }

        $html = (new \App\Core\View())->render('auth.reset-password', [
            'token' => $token,
            'error' => $session->getFlash('error'),
            'success' => $session->getFlash('success'),
            'info' => $session->getFlash('info'),
        ]);
        $response->html($html);
    }

    public function resetPassword(Request $request, Response $response, Session $session): void
    {
        $token = (string) $request->post('token', '');
        $password = (string) $request->post('password', '');
        $confirmPassword = (string) $request->post('confirm_password', '');

        if (empty($token) || !$this->passwordResetService->validateToken($token)) {
            $session->flash('error', 'This password reset link is invalid or has expired.');
            redirect('/forgot-password');
            return;
        }

        if (empty($password) || empty($confirmPassword)) {
            $session->flash('error', 'Please fill in both password fields.');
            redirect('/reset-password?token=' . urlencode($token));
            return;
        }

        if (strlen($password) < 8) {
            $session->flash('error', 'Password must be at least 8 characters long.');
            redirect('/reset-password?token=' . urlencode($token));
            return;
        }

        if ($password !== $confirmPassword) {
            $session->flash('error', 'Passwords do not match.');
            redirect('/reset-password?token=' . urlencode($token));
            return;
        }

        $success = $this->passwordResetService->resetPassword($token, $password);

        if (!$success) {
            $session->flash('error', 'Unable to update your password. Please request a new link.');
            redirect('/forgot-password');
            return;
        }

        $session->flash('success', 'Your password has been reset. You can now sign in.');
        redirect('/login');
    }

    public function logout(Request $request, Response $response, Session $session): void
    {
        $this->authService->logout($session);
        redirect('/login');
    }
}
