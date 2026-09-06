<?php

declare(strict_types=1);

namespace App\Controllers\Faculty;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Faculty;
use App\Models\AcademicTerm;

class SubjectController
{
    public function index(Request $request, Response $response, Session $session): void
    {
        $user = $session->get('user');
        $selectedSem = (string) $request->get('semester', '');
        if ($selectedSem === '1' || $selectedSem === '2') {
            $academicTerm = AcademicTerm::getBySemester($selectedSem);
        } else {
            $academicTerm = AcademicTerm::getActive();
        }

        $termId = (int) ($academicTerm['id'] ?? 0);
        $subjects = Faculty::getAssignedSubjects((int) $user['id'], $termId);

        $html = (new View())->render('faculty.subjects.index', [
            'subjects' => $subjects,
            'academicTerm' => $academicTerm,
            'selectedSemester' => (string) ($academicTerm['semester'] ?? '1'),
        ]);
        $response->html($html);
    }

    public function setup(Request $request, Response $response, Session $session, string $id): void
    {
        $subjectId = (int) $id;
        $nature = (string) $request->post('nature', 'Lecture');
        $gradingMethod = (string) $request->post('grading_method', 'zero_based');
        $prelimWeight = (float) $request->post('prelim_weight', 20.0);
        $midtermWeight = (float) $request->post('midterm_weight', 20.0);
        $semiFinalWeight = (float) $request->post('semi_final_weight', 20.0);
        $finalWeight = (float) $request->post('final_weight', 40.0);

        $totalWeight = $prelimWeight + $midtermWeight + $semiFinalWeight + $finalWeight;
        if (abs($totalWeight - 100.0) > 0.01) {
            $session->flash('error', "Grading period weights must total exactly 100%. (Current: {$totalWeight}%)");
            redirect('/faculty/subjects');
            return;
        }

        \App\Models\Subject::where('id', $subjectId)->update(['nature' => $nature]);

        $user = $session->get('user');
        $termId = (int) $request->post('academic_term_id', 0);
        if ($termId === 0) {
            $academicTerm = AcademicTerm::getActive();
            $termId = (int) ($academicTerm['id'] ?? 1);
        }
        $facultyId = (int) ($user['id'] ?? 0);

        $existing = \App\Models\GradingSetting::query()
            ->where('subject_id', $subjectId)
            ->where('academic_term_id', $termId)
            ->first();

        if ($existing) {
            \App\Models\GradingSetting::where('id', (int) $existing['id'])->update([
                'faculty_id' => $facultyId,
                'grading_method' => $gradingMethod,
                'prelim_weight' => $prelimWeight,
                'midterm_weight' => $midtermWeight,
                'semi_final_weight' => $semiFinalWeight,
                'final_weight' => $finalWeight,
            ]);
        } else {
            \App\Models\GradingSetting::create([
                'academic_term_id' => $termId,
                'faculty_id' => $facultyId,
                'subject_id' => $subjectId,
                'grading_method' => $gradingMethod,
                'prelim_weight' => $prelimWeight,
                'midterm_weight' => $midtermWeight,
                'semi_final_weight' => $semiFinalWeight,
                'final_weight' => $finalWeight,
            ]);
        }

        $session->flash('success', 'Subject nature and grading settings saved successfully.');
        redirect('/faculty/subjects');
    }
}
