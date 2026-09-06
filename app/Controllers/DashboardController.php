<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

class DashboardController
{
    public function index(Request $request, Response $response, Session $session): void
    {
        $user = $session->get('user');
        $role = $user['role'] ?? 'Guest';

        $view = match ($role) {
            'Admin' => 'admin.dashboard',
            'Dean' => 'dean.dashboard',
            'Faculty' => 'faculty.dashboard',
            'Student' => 'student.dashboard',
            default => 'auth.login',
        };

        $html = (new View())->render($view, ['user' => $user]);
        $response->html($html);
    }
}
