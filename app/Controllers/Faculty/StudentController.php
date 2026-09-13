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
        $selectedSem = (string) $request->get('semester', '');
        if ($selectedSem === '1' || $selectedSem === '2') {
            $academicTerm = \App\Models\AcademicTerm::getBySemester($selectedSem);
        } else {
            $academicTerm = \App\Models\AcademicTerm::getActive();
        }
        $termId = (int) ($academicTerm['id'] ?? 0);
        $facultyId = (int) ($user['id'] ?? 0);

        $assignedSubjects = \App\Models\Faculty::getAssignedSubjects($facultyId, $termId);
        $subjectId = (int) $request->get('subject_id', !empty($assignedSubjects) ? $assignedSubjects[0]['id'] : 0);
        $sectionFilter = !empty($request->get('section_id')) ? (int) $request->get('section_id') : null;

        $students = $this->studentRepository->getBySubject($subjectId, $termId, $sectionFilter);
        $availableStudents = $this->studentRepository->getAllAvailable();
        $currentSubject = \App\Models\Subject::find($subjectId);
        $sections = \App\Models\Section::getActiveByTerm($termId);

        $html = (new View())->render('faculty.students.index', [
            'students' => $students,
            'availableStudents' => $availableStudents,
            'subjectId' => $subjectId,
            'currentSubject' => $currentSubject,
            'assignedSubjects' => $assignedSubjects,
            'academicTerm' => $academicTerm,
            'selectedSemester' => (string) ($academicTerm['semester'] ?? '1'),
            'sections' => $sections,
            'selectedSection' => $sectionFilter,
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
        $studentNumber = $studentNumber !== '' ? $studentNumber : null;

        $yearLevel = (int) $request->post('year_level', 1);
        $sectionId = !empty($request->post('section_id')) ? (int) $request->post('section_id') : null;
        $status = in_array($request->post('status'), ['Regular', 'Irregular'], true) ? $request->post('status') : 'Regular';
        $inputPassword = trim((string) $request->post('password', ''));
        $password = empty($inputPassword) 
            ? \App\Models\User::generateRandomPassword() 
            : $inputPassword;

        $semester = (string) $request->post('semester', '');
        $semQuery = $semester !== '' ? "&semester={$semester}" : '';

        if (empty($firstName) || empty($lastName) || empty($email)) {
            $session->flash('error', 'Student first name, last name, and email are required.');
            redirect("/faculty/students?subject_id={$subjectId}{$semQuery}");
            return;
        }

        if (empty($sectionId)) {
            $session->flash('error', 'Assigned section is required when adding a student.');
            redirect("/faculty/students?subject_id={$subjectId}{$semQuery}");
            return;
        }

        if ($studentNumber !== null && \App\Models\User::where('student_number', $studentNumber)->exists()) {
            $session->flash('error', "Student ID number '{$studentNumber}' is already registered to another student. Student numbers must be unique.");
            redirect("/faculty/students?subject_id={$subjectId}{$semQuery}");
            return;
        }

        $termId = (int) $request->post('academic_term_id', 0);
        if ($termId === 0) {
            $academicTerm = \App\Models\AcademicTerm::getActive();
            $termId = (int) ($academicTerm['id'] ?? 1);
        }

        try {
            // 1. Create User via Eloquent ORM
            $user = \App\Models\User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'student_number' => $studentNumber,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'role' => 'Student',
                'status' => 'active',
                'force_password_change' => true,
            ]);
            $userId = (int) $user->id;

            // 2. Create Student via Eloquent ORM
            $createdStudent = \App\Models\Student::create([
                'user_id' => $userId,
                'section_id' => $sectionId,
                'year_level' => $yearLevel,
                'status' => $status,
            ]);
            $studentId = (int) $createdStudent->id;

            // 3. Enroll into subject
            \App\Models\Student::enroll($studentId, $subjectId, $termId);

            // 4. Send email notification with generated temporary password
            (new \App\Services\NotificationService())->sendStudentCredentials($user->toArray(), $password);

            $session->flash('success', "Student {$firstName} {$lastName} added with temporary credentials and enrolled successfully.");
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), '1062') || str_contains($e->getMessage(), 'student_number') || str_contains($e->getMessage(), 'email')) {
                $session->flash('error', 'A student with this email address or student number already exists.');
            } else {
                $session->flash('error', 'Unable to add student: ' . $e->getMessage());
            }
        }

        $semester = (string) $request->post('semester', '');
        $semQuery = $semester !== '' ? "&semester={$semester}" : '';

        redirect("/faculty/students?subject_id={$subjectId}{$semQuery}");
    }

    public function enrollExisting(Request $request, Response $response, Session $session): void
    {
        $subjectId = (int) $request->post('subject_id');
        $studentId = (int) $request->post('student_id');
        $yearLevel = (int) $request->post('year_level', 0);
        $sectionId = !empty($request->post('section_id')) ? (int) $request->post('section_id') : null;
        $status = $request->post('status', '');

        $semester = (string) $request->post('semester', '');
        $semQuery = $semester !== '' ? "&semester={$semester}" : '';

        $termId = (int) $request->post('academic_term_id', 0);
        if ($termId === 0) {
            $academicTerm = \App\Models\AcademicTerm::getActive();
            $termId = (int) ($academicTerm['id'] ?? 1);
        }

        if ($studentId > 0 && $subjectId > 0) {
            $student = \App\Models\Student::find($studentId);
            if (!$student) {
                $session->flash('error', 'Student record not found.');
                redirect("/faculty/students?subject_id={$subjectId}{$semQuery}");
                return;
            }

            if ($sectionId === null && empty($student->section_id)) {
                $session->flash('error', 'Assigned section is required when enrolling a student.');
                redirect("/faculty/students?subject_id={$subjectId}{$semQuery}");
                return;
            }

            $updates = [];
            if ($yearLevel > 0) {
                $updates['year_level'] = $yearLevel;
            }
            if ($sectionId !== null) {
                $updates['section_id'] = $sectionId;
            }
            if (in_array($status, ['Regular', 'Irregular'], true)) {
                $updates['status'] = $status;
            }
            if (!empty($updates)) {
                $student->update($updates);
            }

            \App\Models\Student::enroll($studentId, $subjectId, $termId);
            $session->flash('success', 'Student enrolled successfully into this class section.');
        } else {
            $session->flash('error', 'Please select a valid student to enroll.');
        }

        redirect("/faculty/students?subject_id={$subjectId}{$semQuery}");
    }

    public function remove(Request $request, Response $response, Session $session): void
    {
        $subjectId = (int) $request->post('subject_id');
        $studentId = (int) $request->post('student_id');

        $semester = (string) $request->post('semester', '');
        $semQuery = $semester !== '' ? "&semester={$semester}" : '';

        $termId = (int) $request->post('academic_term_id', 0);
        if ($termId === 0) {
            $academicTerm = \App\Models\AcademicTerm::getActive();
            $termId = (int) ($academicTerm['id'] ?? 1);
        }

        $hasGrades = \App\Models\Grade::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('academic_term_id', $termId)
            ->exists();

        if ($hasGrades) {
            $session->flash('error', 'Cannot remove student from this course roster because academic grades have already been recorded. Academic history must remain immutable.');
            redirect("/faculty/students?subject_id={$subjectId}{$semQuery}");
            return;
        }

        \App\Models\Enrollment::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('academic_term_id', $termId)
            ->delete();

        $session->flash('success', 'Student removed from this course roster.');
        redirect("/faculty/students?subject_id={$subjectId}{$semQuery}");
    }
}
