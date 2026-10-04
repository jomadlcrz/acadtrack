<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/** Backward-compatible redirect from /admin/staff to /admin/faculty. */
class StaffController
{
    public function index(Request $request, Response $response, Session $session, ?array $editingUser = null): void
    {
        $query = $_GET ? '?' . http_build_query($_GET) : '';
        redirect('/admin/faculty' . $query);
    }
}
