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
use App\Models\Set;
use App\Models\Department;
use App\Models\AcademicTerm;
use App\Models\GradingSheet;
use App\Models\GradingSetting;
use App\Models\Enrollment;
use App\Models\AttendanceRecord;
use App\Services\GradeService;
use App\Services\RankingService;

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
                $data = array_merge($data, $this->deanDashboard($user, $request));
                break;

            case 'Faculty':
                $view = 'faculty.dashboard';
                $data = array_merge($data, $this->facultyDashboard($user, $request));
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
        $totalStudents = \App\Models\UserRole::where('role_id', 4)->count();
        $totalFaculty = \App\Models\UserRole::whereIn('role_id', [2, 3])->count();
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

    private function deanDashboard(array $user, ?Request $request = null): array
    {
        $selectedSem = $request ? (string) $request->get('semester', '') : '';
        if ($selectedSem === '1' || $selectedSem === '2') {
            $academicTerm = AcademicTerm::getBySemester($selectedSem);
        } else {
            $academicTerm = AcademicTerm::getActive();
        }
        $termId = (int) ($academicTerm['id'] ?? 1);
        $pendingReviewCount = GradingSheet::where('status', 'SUBMITTED')->count();
        $totalSubjects = Subject::count();
        $totalFaculty = Faculty::count();
        $totalSets = Set::count();

        // Pending submissions awaiting review
        $pendingSheets = GradingSheet::db()->query("
            SELECT gs.*, s.subject_code as subject_code, s.descriptive_title as subject_name,
                   fd.first_name as faculty_first_name, fd.last_name as faculty_last_name,
                   gp.name as period_name
            FROM grading_sheets gs
            JOIN subjects s ON gs.subject_id = s.id
            LEFT JOIN faculty_details fd ON gs.faculty_id = fd.user_id
            JOIN grading_periods gp ON gs.grading_period_id = gp.id
            WHERE gs.status = 'SUBMITTED'
            ORDER BY gs.updated_at DESC
            LIMIT 10
        ")->fetchAll();

        // Recent grading sheets
        $recentSheets = GradingSheet::db()->query("
            SELECT gs.*, s.subject_code as subject_code, s.descriptive_title as subject_name,
                   fd.first_name as faculty_first_name, fd.last_name as faculty_last_name,
                   gp.name as period_name
            FROM grading_sheets gs
            JOIN subjects s ON gs.subject_id = s.id
            LEFT JOIN faculty_details fd ON gs.faculty_id = fd.user_id
            JOIN grading_periods gp ON gs.grading_period_id = gp.id
            ORDER BY gs.updated_at DESC
            LIMIT 6
        ")->fetchAll();

        // Top Performers / Dean's List Qualifiers
        $deanList = (new RankingService())->getTermTopPerformers($termId, null, 10);

        return [
            'academicTerm' => $academicTerm,
            'selectedSemester' => (string) ($academicTerm['semester'] ?? '1'),
            'pendingReviewCount' => $pendingReviewCount,
            'totalSubjects' => $totalSubjects,
            'totalFaculty' => $totalFaculty,
            'totalSets' => $totalSets,
            'pendingSheets' => $pendingSheets,
            'recentSheets' => $recentSheets,
            'deanList' => $deanList,
        ];
    }

    private function facultyDashboard(array $user, ?Request $request = null): array
    {
        $selectedSem = $request ? (string) $request->get('semester', '') : '';
        if ($selectedSem === '1' || $selectedSem === '2') {
            $academicTerm = AcademicTerm::getBySemester($selectedSem);
        } else {
            $academicTerm = AcademicTerm::getActive();
        }
        $termId = (int) ($academicTerm['id'] ?? 1);
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

        // Top Performers & Analytics for assigned subjects
        $selectedSubjectId = $request ? (int) $request->get('subject_id', 0) : 0;
        if ($selectedSubjectId === 0 && !empty($assignedSubjects)) {
            $selectedSubjectId = (int) ($assignedSubjects[0]['id'] ?? 0);
        }

        $selectedSetId = ($request && !empty($request->get('set_id'))) ? (int) $request->get('set_id') : null;

        $rankings = null;
        if ($selectedSubjectId > 0) {
            $rankingService = new RankingService();
            $rankings = $rankingService->getSubjectRankings($selectedSubjectId, $termId, $selectedSetId, 10);
        }

        $sets = Set::getActiveByTerm($termId);

        return [
            'academicTerm' => $academicTerm,
            'selectedSemester' => (string) ($academicTerm['semester'] ?? '1'),
            'assignedSubjectsCount' => $assignedSubjectsCount,
            'assignedSubjects' => $assignedSubjects,
            'selectedSubjectId' => $selectedSubjectId,
            'selectedSetId' => $selectedSetId,
            'sets' => $sets,
            'rankings' => $rankings,
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

        $set = null;
        $setId = (int) ($student['set_id'] ?? 0);
        if ($setId > 0) {
            $s = Set::find($setId);
            if ($s) {
                $set = $s->toArray();
            }
        }

        $overallAttendance = $studentId > 0 ? AttendanceRecord::getStudentOverallSummary($studentId, $termId) : null;

        return [
            'student' => $student,
            'set' => $set,
            'activeTerm' => $activeTerm,
            'enrolledCount' => count($grades),
            'grades' => $grades,
            'summary' => $summary,
            'overallAttendance' => $overallAttendance,
        ];
    }
}
