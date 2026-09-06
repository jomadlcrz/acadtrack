<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class RoleMiddleware
{
    public function __construct(
        private array $allowedRoles
    ) {}

    public function handle(Request $request, Response $response, Session $session): bool
    {
        $userRole = $_SESSION['user']['role'] ?? null;

        if (!$userRole || !in_array($userRole, $this->allowedRoles, true)) {
            $response->statusCode(403)->html('403 - Forbidden');
            return false;
        }

        return true;
    }
}
