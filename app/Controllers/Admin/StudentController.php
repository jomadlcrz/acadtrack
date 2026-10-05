<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Department;
use App\Models\Set;
use App\Models\Student;
use App\Models\User;
use App\Repositories\UserRepository;

/** Admin student directory: manage student accounts, year levels, and sections. */
class StudentController
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function index(Request $request, Response $response, Session $session, ?array $editingUser = null): void
    {
        $status = (string) $request->get('status', '');
        $filters = [
            'status' => in_array($status, Student::STATUSES, true) ? $status : '',
            'year_level' => (int) $request->get('year_level', 0),
            'set_id' => (int) $request->get('set_id', 0),
            'account_status' => in_array((string) $request->get('account_status', ''), ['active', 'inactive'], true)
                ? (string) $request->get('account_status') : '',
            'search' => trim((string) $request->get('search', '')),
        ];

        $students = $this->userRepository->paginateStudents($filters, (int) $request->get('page', 1), 20);

        if ($editingUser === null && $request->get('edit')) {
            $editId = (int) $request->get('edit');
            if ($editId > 0) {
                $found = User::with(['student.set', 'studentDetail'])->find($editId);
                if ($found) {
                    $editingUser = $found->toArray();
                }
            }
        }

        $sets = Set::where('status', 'active')
            ->orderBy('year_level')
            ->orderBy('set_name')
            ->get()
            ->toArray();

        $html = (new View())->render('admin.students.index', [
            'students' => $students,
            'filters' => $filters,
            'sets' => $sets,
            'departments' => Department::getActive(),
            'editingUser' => $editingUser,
        ]);
        $response->html($html);
    }
}
