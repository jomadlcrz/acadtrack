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
        $user = $session->get('user');
        $academicTerm = \App\Models\AcademicTerm::getActive();
        $termId = (int) ($academicTerm['id'] ?? 0);
        $facultyId = (int) ($user['id'] ?? 0);

        $assignedSubjects = \App\Models\Faculty::getAssignedSubjects($facultyId, $termId);
        $subjectId = (int) $request->get('subject_id', !empty($assignedSubjects) ? $assignedSubjects[0]['id'] : 0);

        $students = $this->studentRepository->getBySubject($subjectId, $termId);
        $availableStudents = $this->studentRepository->getAllAvailable();
        $currentSubject = \App\Models\Subject::find($subjectId);

        $html = (new View())->render('faculty.students.index', [
            'students' => $students,
            'availableStudents' => $availableStudents,
            'subjectId' => $subjectId,
            'currentSubject' => $currentSubject,
            'assignedSubjects' => $assignedSubjects,
            'academicTerm' => $academicTerm,
        ]);
        $response->html($html);
    }

    public function addStudent(Request $request, Response $response, Session $session): void
    {
        $subjectId = (int) $request->post('subject_id');
        $firstName = trim((string) $request->post('first_name', ''));
        $lastName = trim((string) $request->post('last_name', ''));
        $email = trim((string) $request->post('email', ''));
        $studentNumber = trim((string) $request->post('student_number', ''));
        $yearLevel = (int) $request->post('year_level', 1);
        $status = in_array($request->post('status'), ['Regular', 'Irregular'], true) ? $request->post('status') : 'Regular';
        $password = (string) $request->post('password', 'student123');

        if (empty($firstName) || empty($lastName) || empty($email) || empty($studentNumber)) {
            $session->flash('error', 'All student details (name, email, student number) are required.');
            redirect("/faculty/students?subject_id={$subjectId}");
            return;
        }

        $academicTerm = \App\Models\AcademicTerm::getActive();
        $termId = (int) ($academicTerm['id'] ?? 1);

        try {
            // 1. Create User
            $userRepo = new \App\Repositories\UserRepository();
            $userId = $userRepo->create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'student_number' => $studentNumber,
                'password' => $password,
                'role' => 'Student',
                'status' => 'active',
            ]);

            // 2. Create Student
            $createdStudent = \App\Models\Student::create([
                'user_id' => $userId,
                'year_level' => $yearLevel,
                'status' => $status,
            ]);
            $studentId = (int) $createdStudent->id;

            // 3. Enroll into subject
            \App\Models\Student::enroll($studentId, $subjectId, $termId);

            // 4. Send email notification
            $createdUser = $userRepo->findById($userId);
            if ($createdUser) {
                (new \App\Services\NotificationService())->sendStudentCredentials($createdUser, $password);
            }

            $session->flash('success', "Student {$firstName} {$lastName} added and enrolled successfully.");
        } catch (\PDOException $e) {
            if (str_contains($e->getMessage(), '1062') || str_contains($e->getMessage(), 'email')) {
                $session->flash('error', 'A student with this email address or student number already exists.');
            } else {
                $session->flash('error', 'Unable to add student: ' . $e->getMessage());
            }
        }

        redirect("/faculty/students?subject_id={$subjectId}");
    }

    public function enrollExisting(Request $request, Response $response, Session $session): void
    {
        $subjectId = (int) $request->post('subject_id');
        $studentId = (int) $request->post('student_id');
        $yearLevel = (int) $request->post('year_level', 0);
        $status = $request->post('status', '');

        $academicTerm = \App\Models\AcademicTerm::getActive();
        $termId = (int) ($academicTerm['id'] ?? 1);

        if ($studentId > 0 && $subjectId > 0) {
            if ($yearLevel > 0 || in_array($status, ['Regular', 'Irregular'], true)) {
                $updates = [];
                if ($yearLevel > 0) {
                    $updates['year_level'] = $yearLevel;
                }
                if (in_array($status, ['Regular', 'Irregular'], true)) {
                    $updates['status'] = $status;
                }
                \App\Models\Student::where('id', $studentId)->update($updates);
            }

            \App\Models\Student::enroll($studentId, $subjectId, $termId);
            $session->flash('success', 'Student enrolled successfully into this class section.');
        } else {
            $session->flash('error', 'Please select a valid student to enroll.');
        }

        redirect("/faculty/students?subject_id={$subjectId}");
    }

    public function remove(Request $request, Response $response, Session $session): void
    {
        $subjectId = (int) $request->post('subject_id');
        $studentId = (int) $request->post('student_id');
        $academicTerm = \App\Models\AcademicTerm::getActive();
        $termId = (int) ($academicTerm['id'] ?? 1);

        $pdo = \App\Core\Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM enrollments WHERE student_id = :sid AND subject_id = :subid AND academic_term_id = :tid");
        $stmt->execute(['sid' => $studentId, 'subid' => $subjectId, 'tid' => $termId]);

        $session->flash('success', 'Student removed from this course roster.');
        redirect("/faculty/students?subject_id={$subjectId}");
    }
}
