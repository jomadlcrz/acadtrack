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
        $selectedSem = (string) $request->get('semester', '');
        if ($selectedSem === '1' || $selectedSem === '2') {
            $academicTerm = \App\Models\AcademicTerm::getBySemester($selectedSem);
        } else {
            $academicTerm = \App\Models\AcademicTerm::getActive();
        }

        $termId = (int) ($academicTerm['id'] ?? 0);
        $subjects = $this->subjectRepository->getByDean($termId);
        $faculty = (new \App\Models\User())->getFaculty();

        $html = (new View())->render('dean.faculty-assignments.index', [
            'subjects' => $subjects,
            'faculty' => $faculty,
            'academicTerm' => $academicTerm,
            'selectedSemester' => (string) ($academicTerm['semester'] ?? '1'),
        ]);
        $response->html($html);
    }

    public function assign(Request $request, Response $response, Session $session): void
    {
        $facultyId = (int) $request->post('faculty_id');
        $subjectId = (int) $request->post('subject_id');
        $termId = (int) $request->post('academic_term_id', 0);
        $semester = (string) $request->post('semester', '');
        $semQuery = $semester !== '' ? "?semester={$semester}" : '';

        if ($termId === 0) {
            $academicTerm = \App\Models\AcademicTerm::getActive();
            $termId = (int) ($academicTerm['id'] ?? 1);
        }

        $this->facultyRepository->assignSubject($facultyId, $subjectId, $termId);
        $session->flash('success', 'Faculty assigned successfully.');
        redirect("/dean/faculty-assignments{$semQuery}");
    }

    public function remove(Request $request, Response $response, Session $session): void
    {
        $facultyId = (int) $request->post('faculty_id');
        $subjectId = (int) $request->post('subject_id');
        $termId = (int) $request->post('academic_term_id', 0);
        $semester = (string) $request->post('semester', '');
        $semQuery = $semester !== '' ? "?semester={$semester}" : '';

        if ($termId === 0) {
            $academicTerm = \App\Models\AcademicTerm::getActive();
            $termId = (int) ($academicTerm['id'] ?? 1);
        }

        $this->facultyRepository->removeAssignment($facultyId, $subjectId, $termId);
        $session->flash('success', 'Assignment removed.');
        redirect("/dean/faculty-assignments{$semQuery}");
    }
}
