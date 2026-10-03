<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Department;
use App\Repositories\UserRepository;

/** Admin directory of Faculty, Dean and Admin accounts. */
class StaffController
{
    public function index(Request $request, Response $response, Session $session, ?array $editingUser = null): void
    {
        $role = (string) $request->get('role', '');
        $status = (string) $request->get('status', '');

        $staff = (new UserRepository())->paginateStaff(
            (int) $request->get('page', 1),
            20,
            in_array($role, ['Admin', 'Dean', 'Faculty'], true) ? $role : '',
            in_array($status, ['active', 'inactive'], true) ? $status : '',
            trim((string) $request->get('search', '')),
            (int) $request->get('department_id', 0)
        );

        if ($editingUser === null && $request->get('edit')) {
            $editId = (int) $request->get('edit');
            if ($editId > 0) {
                $found = \App\Models\User::with(['faculty.department'])->find($editId);
                if ($found) {
                    $editingUser = $found->toArray();
                }
            }
        }

        $activeTerm = \App\Models\AcademicTerm::getActive();
        $sets = $activeTerm ? \App\Models\Set::getActiveByTerm((int) $activeTerm['id']) : [];

        $html = (new View())->render('admin.staff.index', [
            'staff' => $staff,
            'departments' => Department::getActive(),
            'sets' => $sets,
            'editingUser' => $editingUser,
            'currentRole' => $role,
            'currentStatus' => $status,
            'currentDepartment' => (int) $request->get('department_id', 0),
            'currentSearch' => trim((string) $request->get('search', '')),
        ]);
        $response->html($html);
    }
}
