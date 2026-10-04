<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\UserRepository;

/** Admin directory of Administrator accounts. */
class AdministratorController
{
    public function index(Request $request, Response $response, Session $session): void
    {
        $status = (string) $request->get('status', '');

        $administrators = (new UserRepository())->paginateAdministrators(
            (int) $request->get('page', 1),
            20,
            in_array($status, ['active', 'inactive'], true) ? $status : '',
            trim((string) $request->get('search', ''))
        );

        $html = (new View())->render('admin.administrators.index', [
            'administrators' => $administrators,
            'currentStatus' => $status,
            'currentSearch' => trim((string) $request->get('search', '')),
        ]);
        $response->html($html);
    }
}
