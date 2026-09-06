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

        return true;
    }
}
