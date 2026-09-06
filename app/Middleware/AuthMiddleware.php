<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware
{
    public function handle(Request $request, Response $response, Session $session): bool
    {
        if (!isset($_SESSION['user']['id'])) {
            $session->flash('error', 'Please log in to continue.');
            $response->redirect('/login');
            return false;
        }

        if (!empty($_SESSION['user']['force_password_change'])) {
            $path = $request->path();
            if ($path !== '/change-password' && $path !== '/logout') {
                $session->flash('info', 'Please set a new password before continuing.');
                $response->redirect('/change-password');
                return false;
            }
        }

        return true;
    }
}
