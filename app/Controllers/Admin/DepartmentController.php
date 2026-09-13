<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Department;

class DepartmentController
{
    public function index(Request $request, Response $response, Session $session): void
    {
        $departments = Department::withCount('faculty')
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();

        $html = (new View())->render('admin.departments.index', [
            'departments' => $departments,
        ]);
        $response->html($html);
    }

    public function store(Request $request, Response $response, Session $session): void
    {
        $code = strtoupper(trim((string) ($request->post('dept_abbrev') ?: $request->post('code', ''))));
        $name = trim((string) ($request->post('dept_name') ?: $request->post('name', '')));
        $description = trim((string) $request->post('description', '')) ?: null;
        $status = in_array($request->post('status'), ['active', 'inactive'], true) ? $request->post('status') : 'active';

        if (empty($code) || empty($name)) {
            $session->flash('error', 'Department code and department name are required.');
            redirect('/admin/departments');
            return;
        }

        if (Department::where('dept_abbrev', $code)->exists()) {
            $session->flash('error', "Department code '{$code}' already exists.");
            redirect('/admin/departments');
            return;
        }

        Department::create([
            'dept_abbrev' => $code,
            'dept_name' => $name,
            'description' => $description,
            'status' => $status,
        ]);

        $session->flash('success', "Department '{$name}' created successfully.");
        redirect('/admin/departments');
    }

    public function update(Request $request, Response $response, Session $session, string $id): void
    {
        $department = Department::find((int) $id);
        if (!$department) {
            $session->flash('error', 'Department not found.');
            redirect('/admin/departments');
            return;
        }

        $code = strtoupper(trim((string) ($request->post('dept_abbrev') ?: $request->post('code', ''))));
        $name = trim((string) ($request->post('dept_name') ?: $request->post('name', '')));
        $description = trim((string) $request->post('description', '')) ?: null;
        $status = in_array($request->post('status'), ['active', 'inactive'], true) ? $request->post('status') : 'active';

        if (empty($code) || empty($name)) {
            $session->flash('error', 'Department code and department name are required.');
            redirect('/admin/departments');
            return;
        }

        if (Department::where('dept_abbrev', $code)->where('id', '!=', $department->id)->exists()) {
            $session->flash('error', "Department code '{$code}' is already assigned to another department.");
            redirect('/admin/departments');
            return;
        }

        $department->update([
            'dept_abbrev' => $code,
            'dept_name' => $name,
            'description' => $description,
            'status' => $status,
        ]);

        $session->flash('success', "Department '{$name}' updated successfully.");
        redirect('/admin/departments');
    }

    public function archive(Request $request, Response $response, Session $session, string $id): void
    {
        $department = Department::find((int) $id);
        if (!$department) {
            $session->flash('error', 'Department not found.');
            redirect('/admin/departments');
            return;
        }

        $department->update(['status' => 'inactive']);
        $session->flash('success', "Department '{$department->name}' archived successfully.");
        redirect('/admin/departments');
    }

    public function restore(Request $request, Response $response, Session $session, string $id): void
    {
        $department = Department::find((int) $id);
        if (!$department) {
            $session->flash('error', 'Department not found.');
            redirect('/admin/departments');
            return;
        }

        $department->update(['status' => 'active']);
        $session->flash('success', "Department '{$department->name}' restored to active status.");
        redirect('/admin/departments');
    }
}
