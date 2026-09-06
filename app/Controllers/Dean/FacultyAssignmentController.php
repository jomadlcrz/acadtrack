<?php

declare(strict_types=1);

namespace App\Controllers\Dean;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\FacultyRepository;
use App\Repositories\SubjectRepository;

class FacultyAssignmentController
{
    private FacultyRepository $facultyRepository;
    private SubjectRepository $subjectRepository;

    public function __construct()
    {
        $this->facultyRepository = new FacultyRepository();
        $this->subjectRepository = new SubjectRepository();
    }

    public function index(Request $request, Response $response, Session $session): void
    {
        $academicTerm = (new \App\Models\AcademicTerm())->getActive();
        $subjects = $this->subjectRepository->getByDean($academicTerm['id'] ?? 0);
        $faculty = (new \App\Models\User())->getFaculty();

        $html = (new View())->render('dean.faculty-assignments.index', [
            'subjects' => $subjects,
            'faculty' => $faculty,
            'academicTerm' => $academicTerm,
        ]);
        $response->html($html);
    }

    public function assign(Request $request, Response $response, Session $session): void
    {
        $facultyId = (int) $request->post('faculty_id');
        $subjectId = (int) $request->post('subject_id');
        $academicTerm = (new \App\Models\AcademicTerm())->getActive();

        $this->facultyRepository->assignSubject($facultyId, $subjectId, $academicTerm['id']);
        $session->flash('success', 'Faculty assigned successfully.');
        redirect('/dean/faculty-assignments');
    }

    public function remove(Request $request, Response $response, Session $session): void
    {
        $facultyId = (int) $request->post('faculty_id');
        $subjectId = (int) $request->post('subject_id');
        $academicTerm = (new \App\Models\AcademicTerm())->getActive();

        $this->facultyRepository->removeAssignment($facultyId, $subjectId, $academicTerm['id']);
        $session->flash('success', 'Assignment removed.');
        redirect('/dean/faculty-assignments');
    }
}
