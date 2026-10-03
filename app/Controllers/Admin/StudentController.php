<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\AcademicTerm;
use App\Models\Set;
use App\Models\Student;
use App\Repositories\UserRepository;
use App\Services\StudentRegistrationService;
use Illuminate\Database\Capsule\Manager as Capsule;

/** Admin student directory: students registered per academic term, with term rollover. */
class StudentController
{
    private UserRepository $userRepository;
    private StudentRegistrationService $registrationService;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->registrationService = new StudentRegistrationService();
    }

    public function index(Request $request, Response $response, Session $session): void
    {
        $terms = AcademicTerm::getAll();
        $termId = (int) $request->get('term_id', 0);
        if ($termId === 0) {
            $termId = (int) (AcademicTerm::getActive()['id'] ?? ($terms[0]['id'] ?? 0));
        }

        $status = (string) $request->get('status', '');
        $filters = [
            'status' => in_array($status, Student::STATUSES, true) ? $status : '',
            'year_level' => (int) $request->get('year_level', 0),
            'set_id' => (int) $request->get('set_id', 0),
            'account_status' => in_array((string) $request->get('account_status', ''), ['active', 'inactive'], true)
                ? (string) $request->get('account_status') : '',
            'search' => trim((string) $request->get('search', '')),
        ];

        $students = $this->userRepository->paginateStudents($termId, $filters, (int) $request->get('page', 1), 20);

        $unregistered = (int) Capsule::table('students')
            ->whereNotIn('id', Capsule::table('student_term_registrations')->where('academic_term_id', $termId)->select('student_id'))
            ->count();

        $nextTerm = $this->registrationService->nextTerm($termId);

        $html = (new View())->render('admin.students.index', [
            'terms' => $terms,
            'termId' => $termId,
            'students' => $students,
            'filters' => $filters,
            'sets' => Set::getActiveByTerm($termId),
            'unregistered' => $unregistered,
            'nextTerm' => $nextTerm ? $nextTerm->toArray() : null,
        ]);
        $response->html($html);
    }
}
