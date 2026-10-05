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
        $sets = \App\Models\Set::where('academic_term_id', $termId)->where('status', 'active')->orderBy('year_level')->orderBy('set_name')->get()->toArray();

        $html = (new View())->render('dean.faculty-assignments.index', [
            'subjects' => $subjects,
            'faculty' => $faculty,
            'sets' => $sets,
            'academicTerm' => $academicTerm,
            'termLabel' => $termLabel,
            'selectedSemester' => (string) $semInt,
            'totalAssignments' => count($assignments),
        ]);
        $response->html($html);
    }

    public function assign(Request $request, Response $response, Session $session): void
    {
        $rawFacultyIds = $request->post('faculty_ids', $request->post('faculty_id', []));
        $facultyIds = array_values(array_unique(array_filter(
            array_map('intval', is_array($rawFacultyIds) ? $rawFacultyIds : [$rawFacultyIds]),
            static fn (int $id): bool => $id > 0
        )));
        $rawSetIds = $request->post('set_ids', $request->post('set_id', []));
        $setIds = array_values(array_unique(array_filter(
            array_map('intval', is_array($rawSetIds) ? $rawSetIds : [$rawSetIds]),
            static fn (int $id): bool => $id > 0
        )));
        $subjectId = (int) $request->post('subject_id');
        $termId = (int) $request->post('academic_term_id', 0);
        $semester = (string) $request->post('semester', '');
        $semQuery = $semester !== '' ? "?semester={$semester}" : '';

        if ($facultyIds === [] || $subjectId <= 0) {
            $session->flash('error', 'Please select a subject offering and at least one instructor.');
            redirect("/dean/faculty-assignments{$semQuery}");
            return;
        }

        if ($termId === 0) {
            $academicTerm = \App\Models\AcademicTerm::getActive();
            $termId = (int) ($academicTerm['id'] ?? 1);
        }

        if (empty($setIds)) {
            foreach ($facultyIds as $facultyId) {
                $this->facultyRepository->assignSubject($facultyId, $subjectId, $termId, null);
                $this->logAssignment('Instructor Assigned', true, $facultyId, $subjectId);
            }
        } else {
            foreach ($facultyIds as $facultyId) {
                foreach ($setIds as $setId) {
                    $this->facultyRepository->assignSubject($facultyId, $subjectId, $termId, $setId);
                    $this->logAssignment('Instructor Assigned', true, $facultyId, $subjectId, $setId);
                }
            }
        }

        $session->flash('success', count($facultyIds) === 1
            ? 'Instructor assigned successfully to course offering.'
            : count($facultyIds) . ' instructors assigned successfully to course offering.');
        redirect("/dean/faculty-assignments{$semQuery}");
    }

    public function remove(Request $request, Response $response, Session $session): void
    {
        $facultyId = (int) $request->post('faculty_id');
        $subjectId = (int) $request->post('subject_id');
        $setId = !empty($request->post('set_id')) ? (int) $request->post('set_id') : null;
        $assignmentId = !empty($request->post('assignment_id')) ? (int) $request->post('assignment_id') : null;
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

        $this->facultyRepository->removeAssignment($facultyId, $subjectId, $termId, $setId, $assignmentId);
        $this->logAssignment('Assignment Removed', false, $facultyId, $subjectId, $setId);
        $session->flash('success', 'Instructor assignment removed successfully.');
        redirect("/dean/faculty-assignments{$semQuery}");
    }

    private function logAssignment(string $action, bool $assigned, int $facultyId, int $subjectId, ?int $setId = null): void
    {
        $faculty = \App\Models\FacultyDetail::where('user_id', $facultyId)->first();
        $subject = \App\Models\Subject::find($subjectId);
        $set = $setId ? \App\Models\Set::find($setId) : null;
        $name = $faculty ? trim($faculty->first_name . ' ' . $faculty->last_name) : "faculty #{$facultyId}";
        $code = $subject ? $subject->subject_code : "subject #{$subjectId}";
        $setStr = $set ? " ({$set->set_name})" : '';

        \App\Services\ActivityLogService::record([
            'category' => \App\Services\ActivityLogService::CATEGORY_ASSIGNMENTS,
            'action' => $action,
            'target_type' => 'subject',
            'target_id' => $subjectId,
            'target_label' => $code,
            'summary' => $assigned ? "Assigned {$name} to {$code}{$setStr}." : "Removed {$name} from {$code}{$setStr}.",
        ]);
    }
}
