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
        $semInt = (int) ($academicTerm['semester'] ?? ($selectedSem !== '' ? (int)$selectedSem : 1));
        $subjects = $this->subjectRepository->getByDean($termId, null, $semInt);
        $faculty = (new \App\Models\User())->getFaculty();
        $assignments = $this->facultyRepository->getAssignmentsForTerm($termId);

        // Group assignments by subject and faculty
        $assignmentsBySubject = [];
        $assignmentsByFaculty = [];
        foreach ($assignments as $a) {
            $assignmentsBySubject[$a['subject_id']][] = $a;
            $assignmentsByFaculty[$a['faculty_id']][] = $a;
        }

        foreach ($subjects as &$s) {
            $s['assigned_faculty'] = $assignmentsBySubject[$s['id']] ?? [];
            $s['assigned_faculty_count'] = count($s['assigned_faculty']);
        }
        unset($s);

        foreach ($faculty as &$f) {
            $f['assigned_count'] = count($assignmentsByFaculty[$f['id']] ?? []);
        }
        unset($f);

        $academicYearName = $academicTerm['academic_year_name'] ?? ($academicTerm['school_year'] ?? '2026-2027');
        $termLabel = $academicYearName . ' · ' . ($semInt === 2 ? '2nd Semester' : '1st Semester');

        $html = (new View())->render('dean.faculty-assignments.index', [
            'subjects' => $subjects,
            'faculty' => $faculty,
            'academicTerm' => $academicTerm,
            'termLabel' => $termLabel,
            'selectedSemester' => (string) $semInt,
            'totalAssignments' => count($assignments),
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

        if ($facultyId <= 0 || $subjectId <= 0) {
            $session->flash('error', 'Please select both an instructor and a subject offering.');
            redirect("/dean/faculty-assignments{$semQuery}");
            return;
        }

        if ($termId === 0) {
            $academicTerm = \App\Models\AcademicTerm::getActive();
            $termId = (int) ($academicTerm['id'] ?? 1);
        }

        $this->facultyRepository->assignSubject($facultyId, $subjectId, $termId);
        $session->flash('success', 'Instructor assigned successfully to course offering.');
        redirect("/dean/faculty-assignments{$semQuery}");
    }

    public function remove(Request $request, Response $response, Session $session): void
    {
        $facultyId = (int) $request->post('faculty_id');
        $subjectId = (int) $request->post('subject_id');
        $termId = (int) $request->post('academic_term_id', 0);
        $semester = (string) $request->post('semester', '');
        $semQuery = $semester !== '' ? "?semester={$semester}" : '';

        if ($subjectId <= 0) {
            $session->flash('error', 'Invalid subject specified.');
            redirect("/dean/faculty-assignments{$semQuery}");
            return;
        }

        if ($termId === 0) {
            $academicTerm = \App\Models\AcademicTerm::getActive();
            $termId = (int) ($academicTerm['id'] ?? 1);
        }

        $sheetQuery = \App\Models\GradingSheet::where('subject_id', $subjectId)
            ->where('academic_term_id', $termId)
            ->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW', 'APPROVED', 'FINALIZED']);

        if ($facultyId > 0) {
            $sheetQuery->where('faculty_id', $facultyId);
        }

        if ($sheetQuery->exists()) {
            $session->flash('error', 'Cannot remove faculty assignment because submitted or approved grading sheets exist for this course in this academic term.');
            redirect("/dean/faculty-assignments{$semQuery}");
            return;
        }

        $this->facultyRepository->removeAssignment($facultyId, $subjectId, $termId);
        $session->flash('success', 'Instructor assignment removed successfully.');
        redirect("/dean/faculty-assignments{$semQuery}");
    }
}
