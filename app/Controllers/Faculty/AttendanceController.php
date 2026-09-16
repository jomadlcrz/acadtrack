<?php

declare(strict_types=1);

namespace App\Controllers\Faculty;

use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\AcademicTerm;
use App\Models\AttendanceRecord;
use App\Models\Faculty;
use App\Models\Set;
use App\Models\Subject;
use App\Repositories\AttendanceRepository;
use App\Repositories\StudentRepository;

class AttendanceController
{
    private AttendanceRepository $attendanceRepository;
    private StudentRepository $studentRepository;

    public function __construct()
    {
        $this->attendanceRepository = new AttendanceRepository();
        $this->studentRepository = new StudentRepository();
    }

    public function index(Request $request, Response $response, Session $session): void
    {
        $user = $session->get('user');
        $facultyId = (int) ($user['id'] ?? 0);

        $selectedSem = (string) $request->get('semester', '');
        if ($selectedSem === '1' || $selectedSem === '2') {
            $academicTerm = AcademicTerm::getBySemester($selectedSem);
        } else {
            $academicTerm = AcademicTerm::getActive();
        }
        $termId = (int) ($academicTerm['id'] ?? 1);

        $assignedSubjects = Faculty::getAssignedSubjects($facultyId, $termId);
        $subjectId = (int) $request->get('subject_id', !empty($assignedSubjects) ? $assignedSubjects[0]['id'] : 0);

        $date = trim((string) $request->get('date', date('Y-m-d')));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        $setFilter = !empty($request->get('set_id')) ? (int) $request->get('set_id') : null;

        // Current subject details
        $currentSubject = null;
        foreach ($assignedSubjects as $sub) {
            if ((int) $sub['id'] === $subjectId) {
                $currentSubject = $sub;
                break;
            }
        }
        if (!$currentSubject && $subjectId > 0) {
            $subObj = Subject::find($subjectId);
            if ($subObj) {
                $currentSubject = $subObj->toArray();
            }
        }

        // Students roster for this subject
        $students = [];
        if ($subjectId > 0) {
            $students = $this->studentRepository->getBySubject($subjectId, $termId);
            if ($setFilter !== null) {
                $students = array_values(array_filter($students, function ($s) use ($setFilter) {
                    return (int) ($s['set_id'] ?? 0) === $setFilter;
                }));
            }
        }

        // Existing attendance records for selected date
        $attendanceMap = [];
        if ($subjectId > 0) {
            $attendanceMap = AttendanceRecord::getBySubjectAndDate($subjectId, $termId, $date);
        }

        // Attendance history logs for this subject
        $historyLogs = [];
        if ($subjectId > 0) {
            $historyLogs = AttendanceRecord::getSubjectAttendanceLogs($subjectId, $termId, 10);
        }

        $sets = Set::getActiveByTerm($termId);

        // Pre-calculate attendance stats for today
        $presentCount = 0;
        $absentCount = 0;
        $excusedCount = 0;
        $lateCount = 0;

        foreach ($students as $s) {
            $stId = (int) $s['id'];
            $status = $attendanceMap[$stId]['status'] ?? null;
            if ($status === 'Present') $presentCount++;
            elseif ($status === 'Absent') $absentCount++;
            elseif ($status === 'Excused') $excusedCount++;
            elseif ($status === 'Late') $lateCount++;
        }

        // Batch calculate student accumulated absence counts in a single query (prevents N+1 in view)
        $studentAbsenceMap = [];
        if ($subjectId > 0 && !empty($students)) {
            $studentIds = array_column($students, 'id');
            if (!empty($studentIds)) {
                $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
                $pdo = Database::getConnection();
                $stmt = $pdo->prepare("
                    SELECT student_id,
                           SUM(CASE WHEN status IN ('Absent', 'Excused') THEN 1 ELSE 0 END) as total_absences,
                           SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
                           SUM(CASE WHEN status = 'Excused' THEN 1 ELSE 0 END) as excused_count,
                           SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late_count,
                           SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_count
                    FROM attendance_records
                    WHERE subject_id = ?
                      AND academic_term_id = ?
                      AND student_id IN ($placeholders)
                    GROUP BY student_id
                ");
                $params = array_merge([$subjectId, $termId], $studentIds);
                $stmt->execute($params);
                $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($rows as $row) {
                    $tot = (int) $row['total_absences'];
                    $studentAbsenceMap[(int) $row['student_id']] = [
                        'total_absences' => $tot,
                        'absent_count' => (int) $row['absent_count'],
                        'excused_count' => (int) $row['excused_count'],
                        'late_count' => (int) $row['late_count'],
                        'present_count' => (int) $row['present_count'],
                        'has_warning' => $tot >= 5,
                    ];
                }
            }
        }

        $html = (new View())->render('faculty.attendance.index', [
            'academicTerm' => $academicTerm,
            'selectedSemester' => (string) ($academicTerm['semester'] ?? '1'),
            'assignedSubjects' => $assignedSubjects,
            'subjectId' => $subjectId,
            'currentSubject' => $currentSubject,
            'date' => $date,
            'sets' => $sets,
            'selectedSet' => $setFilter,
            'students' => $students,
            'attendanceMap' => $attendanceMap,
            'studentAbsenceMap' => $studentAbsenceMap,
            'historyLogs' => $historyLogs,
            'presentCount' => $presentCount,
            'absentCount' => $absentCount,
            'excusedCount' => $excusedCount,
            'lateCount' => $lateCount,
        ]);

        $response->html($html);
    }

    public function save(Request $request, Response $response, Session $session): void
    {
        $user = $session->get('user');
        $facultyId = (int) ($user['id'] ?? 0);

        $subjectId = (int) $request->post('subject_id');
        $termId = (int) $request->post('academic_term_id');
        $date = trim((string) $request->post('attendance_date', date('Y-m-d')));
        $semester = (string) $request->post('semester', '1');
        $setId = $request->post('set_id') ? (int) $request->post('set_id') : null;

        if ($subjectId <= 0 || $termId <= 0) {
            $session->flash('error', 'Invalid subject or academic term for attendance.');
            redirect('/faculty/attendance');
            return;
        }

        $rawStatuses = $request->post('attendance', []);
        $rawRemarks = $request->post('remarks', []);

        $entries = [];
        if (is_array($rawStatuses)) {
            foreach ($rawStatuses as $studentId => $status) {
                $entries[] = [
                    'student_id' => (int) $studentId,
                    'status' => (string) $status,
                    'remarks' => isset($rawRemarks[$studentId]) ? (string) $rawRemarks[$studentId] : '',
                ];
            }
        }

        $saved = $this->attendanceRepository->bulkSave($termId, $subjectId, $facultyId, $date, $entries);

        $session->flash('success', "Attendance successfully recorded for " . date('M j, Y', strtotime($date)) . " ({$saved} students).");

        $setParam = $setId ? "&set_id={$setId}" : "";
        redirect("/faculty/attendance?subject_id={$subjectId}&semester={$semester}&date={$date}{$setParam}");
    }

    public function getStudentHistory(Request $request, Response $response): void
    {
        $studentId = (int) $request->get('student_id');
        $subjectId = (int) $request->get('subject_id');
        $termId = (int) $request->get('term_id');

        if ($studentId <= 0 || $subjectId <= 0 || $termId <= 0) {
            $response->json(['error' => 'Missing parameters'], 400);
            return;
        }

        $summary = AttendanceRecord::getStudentAbsenceSummary($studentId, $subjectId, $termId);
        $response->json($summary);
    }
}
