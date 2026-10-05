<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Set;

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

    public function show(Request $request, Response $response, Session $session, string $id): void
    {
        $department = Department::with(['programs', 'faculty.user.facultyDetail'])
            ->withCount('faculty')
            ->find((int) $id);

        if (!$department) {
            $session->flash('error', 'Department not found.');
            redirect('/admin/departments');
            return;
        }

        $html = (new View())->render('admin.departments.show', [
            'department' => $department->toArray(),
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
            $session->flash('error', 'Department abbrev and department name are required.');
            redirect('/admin/departments');
            return;
        }

        if (Department::where('dept_abbrev', $code)->exists()) {
            $session->flash('error', "Department abbrev '{$code}' already exists.");
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
            $session->flash('error', 'Department abbrev and department name are required.');
            redirect('/admin/departments');
            return;
        }

        if (Department::where('dept_abbrev', $code)->where('id', '!=', $department->id)->exists()) {
            $session->flash('error', "Department abbrev '{$code}' is already assigned to another department.");
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
        $redirectUrl = (string) $request->post('_redirect', '');
        if (empty($redirectUrl) && isset($_SERVER['HTTP_REFERER']) && str_contains($_SERVER['HTTP_REFERER'], '/admin/departments/' . $id)) {
            $redirectUrl = '/admin/departments/' . $id;
        }
        redirect(!empty($redirectUrl) ? $redirectUrl : '/admin/departments');
    }

    public function archive(Request $request, Response $response, Session $session, string $id): void
    {
        $department = Department::find((int) $id);
        if (!$department) {
            $session->flash('error', 'Department not found.');
            redirect('/admin/departments');
            return;
        }

        $redirectUrl = isset($_SERVER['HTTP_REFERER']) && str_contains($_SERVER['HTTP_REFERER'], '/admin/departments/' . $id)
            ? '/admin/departments/' . $id
            : '/admin/departments';

        // Constraint 1: Check active programs
        $activeProgramsCount = Program::where('department_id', $department->id)
            ->where('status', 'active')
            ->count();
        if ($activeProgramsCount > 0) {
            $session->flash('error', "Cannot archive department '{$department->name}'. It still has {$activeProgramsCount} active program(s). Please archive or reassign programs first.");
            redirect($redirectUrl);
            return;
        }

        // Constraint 2: Check assigned faculty
        $facultyCount = Faculty::where('department_id', $department->id)->count();
        if ($facultyCount > 0) {
            $session->flash('error', "Cannot archive department '{$department->name}'. It still has {$facultyCount} assigned faculty member(s). Please reassign faculty members first.");
            redirect($redirectUrl);
            return;
        }

        // Constraint 3: Check active sections (sets)
        $activeSetsCount = Set::where('department_id', $department->id)
            ->where('status', 'active')
            ->count();
        if ($activeSetsCount > 0) {
            $session->flash('error', "Cannot archive department '{$department->name}'. It still has {$activeSetsCount} active section(s). Please archive or reassign sections first.");
            redirect($redirectUrl);
            return;
        }

        $department->update(['status' => 'inactive']);
        $session->flash('success', "Department '{$department->name}' archived successfully.");
        redirect($redirectUrl);
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
        $redirectUrl = isset($_SERVER['HTTP_REFERER']) && str_contains($_SERVER['HTTP_REFERER'], '/admin/departments/' . $id)
            ? '/admin/departments/' . $id
            : '/admin/departments';
        redirect($redirectUrl);
    }
}
