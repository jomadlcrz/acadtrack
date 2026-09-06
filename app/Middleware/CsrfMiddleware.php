<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CsrfMiddleware
{
    public function handle(Request $request, Response $response, Session $session): bool
    {
        if ($request->isGet()) {
            return true;
        }

        $token = $request->post('_token') ?? $request->header('X-CSRF-Token');

        if (!csrf_verify($token)) {
            if (str_contains($request->header('Accept', ''), 'application/json')) {
                $response->json(['error' => 'CSRF token mismatch'], 419);
            } else {
                $response->statusCode(419)->html('CSRF token mismatch');
            }
            return false;
        }

        return true;
    }
}
