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
            $role = $_SESSION['user']['role'];
            redirect($this->authService->getDashboardRoute($role));
        } else {
            redirect('/login');
        }
    }

    public function logout(Request $request, Response $response, Session $session): void
    {
        $this->authService->logout($session);
        redirect('/login');
    }
}
