<?php

declare(strict_types=1);

namespace App\Controllers\Faculty;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\StudentRepository;

class StudentController
{
    private StudentRepository $studentRepository;

    public function __construct()
    {
        $this->studentRepository = new StudentRepository();
    }

    public function index(Request $request, Response $response, Session $session): void
    {
        $subjectId = (int) $request->get('subject_id');
        $academicTerm = (new \App\Models\AcademicTerm())->getActive();
        $students = $this->studentRepository->getBySubject($subjectId, $academicTerm['id'] ?? 0);

        $html = (new View())->render('faculty.students.index', [
            'students' => $students,
            'subjectId' => $subjectId,
            'academicTerm' => $academicTerm,
        ]);
        $response->html($html);
    }
}
