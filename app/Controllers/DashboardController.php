<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

use App\Models\User;
use App\Models\Student;
use App\Models\Faculty;
use App\Models\Subject;
use App\Models\Section;
use App\Models\Department;
use App\Models\AcademicTerm;
use App\Models\GradingSheet;
use App\Models\GradingSetting;
use App\Models\Enrollment;
use App\Services\GradeService;

class DashboardController
{
    public function index(Request $request, Response $response, Session $session): void
    {
        $user = $session->get('user');
        $role = $user['role'] ?? 'Guest';

        $data = ['user' => $user];

        switch ($role) {
            case 'Admin':
                $view = 'admin.dashboard';
                $data = array_merge($data, $this->adminDashboard($user));
                break;

            case 'Dean':
                $view = 'dean.dashboard';
                $data = array_merge($data, $this->deanDashboard($user));
                break;

            case 'Faculty':
                $view = 'faculty.dashboard';
                $data = array_merge($data, $this->facultyDashboard($user));
                break;

            case 'Student':
                $view = 'student.dashboard';
                $data = array_merge($data, $this->studentDashboard($user));
                break;

            default:
                $response->redirect('/login');
                return;
        }

        $html = (new View())->render($view, $data);
        $response->html($html);
    }

    private function adminDashboard(array $user): array
    {
        $activeTerm = AcademicTerm::getActive();
        $totalUsers = User::count();
        $totalStudents = User::where('role', 'Student')->count();
        $totalFaculty = User::where('role', 'Faculty')->count();
        $totalDepts = Department::count();
        $recentUsers = User::orderBy('id', 'desc')->limit(5)->get()->toArray();
        $gradingSetting = GradingSetting::first();

        return [
            'activeTerm' => $activeTerm,
            'totalUsers' => $totalUsers,
            'totalStudents' => $totalStudents,
            'totalFaculty' => $totalFaculty,
            'totalDepts' => $totalDepts,
            'recentUsers' => $recentUsers,
            'gradingSetting' => $gradingSetting ? $gradingSetting->toArray() : null,
        ];
    }

    private function deanDashboard(array $user): array
    {
        $activeTerm = AcademicTerm::getActive();
        $termId = (int) ($activeTerm['id'] ?? 1);
        $pendingReviewCount = GradingSheet::where('status', 'SUBMITTED')->count();
        $totalSubjects = Subject::count();
        $totalFaculty = Faculty::count();
        $totalSections = Section::count();

        // Pending submissions awaiting review
        $pendingSheets = GradingSheet::db()->query("
            SELECT gs.*, s.code as subject_code, s.name as subject_name,
                   u.first_name as faculty_first_name, u.last_name as faculty_last_name,
                   gp.name as period_name
            FROM grading_sheets gs
            JOIN subjects s ON gs.subject_id = s.id
            JOIN users u ON gs.faculty_id = u.id
            JOIN grading_periods gp ON gs.grading_period_id = gp.id
            WHERE gs.status = 'SUBMITTED'
            ORDER BY gs.updated_at DESC
            LIMIT 10
        ")->fetchAll();

        // Recent grading sheets
        $recentSheets = GradingSheet::db()->query("
            SELECT gs.*, s.code as subject_code, s.name as subject_name,
                   u.first_name as faculty_first_name, u.last_name as faculty_last_name,
                   gp.name as period_name
            FROM grading_sheets gs
            JOIN subjects s ON gs.subject_id = s.id
            JOIN users u ON gs.faculty_id = u.id
            JOIN grading_periods gp ON gs.grading_period_id = gp.id
            ORDER BY gs.updated_at DESC
            LIMIT 6
        ")->fetchAll();

        return [
            'activeTerm' => $activeTerm,
            'pendingReviewCount' => $pendingReviewCount,
            'totalSubjects' => $totalSubjects,
            'totalFaculty' => $totalFaculty,
            'totalSections' => $totalSections,
            'pendingSheets' => $pendingSheets,
            'recentSheets' => $recentSheets,
        ];
    }

    private function facultyDashboard(array $user): array
    {
        $activeTerm = AcademicTerm::getActive();
        $termId = (int) ($activeTerm['id'] ?? 1);
        $facultyId = (int) ($user['id'] ?? 0);

        $assignedSubjects = Faculty::getAssignedSubjects($facultyId, $termId);
        $assignedSubjectsCount = count($assignedSubjects);

        $subjectIds = array_column($assignedSubjects, 'id');
        $totalStudents = 0;
        if (!empty($subjectIds)) {
            $totalStudents = Enrollment::whereIn('subject_id', $subjectIds)
                ->where('academic_term_id', $termId)
                ->distinct('student_id')
                ->count('student_id');
        }

        $submittedCount = GradingSheet::where('faculty_id', $facultyId)
            ->whereIn('status', ['SUBMITTED', 'APPROVED'])
            ->count();

        // Enhance workload subjects with enrolled count and grading status
        $workload = [];
        foreach ($assignedSubjects as $sub) {
            $count = Enrollment::where('subject_id', $sub['id'])
                ->where('academic_term_id', $termId)
                ->distinct('student_id')
                ->count('student_id');

            $latestSheet = GradingSheet::where('faculty_id', $facultyId)
                ->where('subject_id', $sub['id'])
                ->where('academic_term_id', $termId)
                ->orderBy('updated_at', 'desc')
                ->first();

            $sub['enrolled_count'] = $count;
            $sub['sheet_status'] = $latestSheet ? $latestSheet->status : 'DRAFT';
            $workload[] = $sub;
        }

        return [
            'activeTerm' => $activeTerm,
            'assignedSubjectsCount' => $assignedSubjectsCount,
            'totalStudents' => $totalStudents,
            'submittedCount' => $submittedCount,
            'workload' => $workload,
        ];
    }

    private function studentDashboard(array $user): array
    {
        $activeTerm = AcademicTerm::getActive();
        $termId = (int) ($activeTerm['id'] ?? 1);
        $student = Student::findByUserId((int) ($user['id'] ?? 0));
        $studentId = (int) ($student['id'] ?? 0);

        $gradeService = new GradeService();
        $grades = $studentId > 0 ? $gradeService->getStudentGrades($studentId, $termId) : [];
        $summary = $studentId > 0 ? $gradeService->getGradeSummary($studentId, $termId) : [];

        $section = null;
        if (!empty($student['section_id'])) {
            $sec = Section::find((int) $student['section_id']);
            if ($sec) {
                $section = $sec->toArray();
            }
        }

        return [
            'student' => $student,
            'section' => $section,
            'activeTerm' => $activeTerm,
            'enrolledCount' => count($grades),
            'grades' => $grades,
            'summary' => $summary,
        ];
    }
}
