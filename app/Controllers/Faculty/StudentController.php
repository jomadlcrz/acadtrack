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
        $setFilter = !empty($request->get('set_id')) ? (int) $request->get('set_id') : null;

        $page = max(1, (int) $request->get('page', 1));
        $search = trim((string) $request->get('search', ''));
        $paginated = $this->studentRepository->paginateBySubject($subjectId, $termId, $setFilter, $page, 25, $search);
        $students = $paginated['data'];
        $availableStudents = $this->studentRepository->getAllAvailable();
        $currentSubject = \App\Models\Subject::find($subjectId);
        $sets = \App\Models\Set::getAssignedForFaculty($facultyId, $termId, $subjectId);

        $html = (new View())->render('faculty.students.index', [
            'students' => $students,
            'pagination' => $paginated,
            'availableStudents' => $availableStudents,
            'subjectId' => $subjectId,
            'currentSubject' => $currentSubject,
            'assignedSubjects' => $assignedSubjects,
            'academicTerm' => $academicTerm,
            'selectedSemester' => (string) ($academicTerm['semester'] ?? '1'),
            'sets' => $sets,
            'selectedSet' => $setFilter,
            'currentSearch' => $search,
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

        $subject = \App\Models\Subject::find($subjectId);
        $subjectYearLevel = (int) ($subject->year_level ?? 0);
        $yearLevel = ($subjectYearLevel >= 1 && $subjectYearLevel <= 4)
            ? $subjectYearLevel
            : max(1, min(4, (int) $request->post('year_level', 1)));
        $setId = !empty($request->post('set_id')) ? (int) $request->post('set_id') : null;
        $status = \App\Models\Student::normalizeStatus((string) $request->post('status', ''));
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

        $setId = \App\Models\Student::resolveSetId($status, $setId);
        if (\App\Models\Student::requiresSet($status) && empty($setId)) {
            $session->flash('error', 'Assigned set is required when adding a student.');
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
                'set_id' => $setId,
                'year_level' => $yearLevel,
                'status' => $status,
            ]);
            $studentId = (int) $createdStudent->id;

            (new \App\Services\StudentRegistrationService())->register($studentId, $termId, $status, $yearLevel, $setId);

            // 3. Enroll into subject
            \App\Models\Student::enroll($studentId, $subjectId, $termId);
            $this->logRoster('Student Added to Roster', $studentId, $subjectId, "Created the account for {$firstName} {$lastName} and added them to");

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
        $subject = \App\Models\Subject::find($subjectId);
        $subjectYearLevel = (int) ($subject->year_level ?? 0);
        $yearLevel = ($subjectYearLevel >= 1 && $subjectYearLevel <= 4)
            ? $subjectYearLevel
            : max(1, min(4, (int) $request->post('year_level', 1)));
        $setId = !empty($request->post('set_id')) ? (int) $request->post('set_id') : null;
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

            if (\App\Models\Student::requiresSet($student->status) && $setId === null && empty($student->set_id)) {
                $session->flash('error', 'Assigned set is required when enrolling a student.');
                redirect("/faculty/students?subject_id={$subjectId}{$semQuery}");
                return;
            }

            // Student profile (set, year level, status) is owned by the Admin; faculty only roster the student.
            \App\Models\Student::enroll($studentId, $subjectId, $termId);
            $this->logRoster('Student Added to Roster', $studentId, $subjectId, 'Added');
            $session->flash('success', 'Student enrolled successfully into this class set.');
        } else {
            $session->flash('error', 'Please select a valid student to enroll.');
        }

        redirect("/faculty/students?subject_id={$subjectId}{$semQuery}");
    }

    /** CSV template for the bulk roster import. */
    public function rosterTemplate(Request $request, Response $response): void
    {
        $headers = ['Student Number', 'Email', 'Last Name', 'First Name'];
        $sampleRows = [
            ['2026-0001', 'juan.delacruz@gwc.edu.ph', 'Dela Cruz', 'Juan'],
            ['2026-0002', 'maria.santos@gwc.edu.ph', 'Santos', 'Maria'],
            ['', 'pedro.reyes@gwc.edu.ph', 'Reyes', 'Pedro'],
        ];

        $output = fopen('php://temp', 'r+');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, $headers);
        foreach ($sampleRows as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="GWC_Class_Roster_Template.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $csv;

        if (php_sapi_name() !== 'cli' && !defined('PHPUNIT_RUNNING')) {
            exit;
        }
    }

    /** Bulk-enrolls a class list into the selected course offering. */
    public function importRoster(Request $request, Response $response, Session $session): void
    {
        $isJson = $request->isJson();

        $subjectId = 0;
        $termId = 0;
        $semester = '';
        $rows = [];

        if ($isJson) {
            $subjectId = (int) $request->json('subject_id', 0);
            $termId = (int) $request->json('academic_term_id', 0);
            $semester = (string) $request->json('semester', '');
            $jsonRows = $request->json('students', []);
            $rows = is_array($jsonRows) ? $jsonRows : [];
        } else {
            $subjectId = (int) $request->post('subject_id');
            $termId = (int) $request->post('academic_term_id', 0);
            $semester = (string) $request->post('semester', '');

            if (!empty($_FILES['roster_file']['tmp_name'])) {
                $handle = fopen($_FILES['roster_file']['tmp_name'], 'r');
                if ($handle !== false) {
                    $bom = fread($handle, 3);
                    if ($bom !== "\xEF\xBB\xBF") {
                        rewind($handle);
                    }
                    fgetcsv($handle);
                    while (($data = fgetcsv($handle)) !== false) {
                        if (empty(array_filter($data))) {
                            continue;
                        }
                        $rows[] = [
                            'student_number' => trim((string) ($data[0] ?? '')),
                            'email' => trim((string) ($data[1] ?? '')),
                            'last_name' => trim((string) ($data[2] ?? '')),
                            'first_name' => trim((string) ($data[3] ?? '')),
                        ];
                    }
                    fclose($handle);
                }
            }
        }

        $semQuery = $semester !== '' ? "&semester=" . urlencode($semester) : '';

        $reject = function (string $message) use ($isJson, $response, $session, $subjectId, $semQuery): void {
            if ($isJson) {
                $response->statusCode(422)->json(['success' => false, 'message' => $message]);
                return;
            }
            $session->flash('error', $message);
            redirect("/faculty/students?subject_id={$subjectId}{$semQuery}");
        };

        if ($termId === 0) {
            $academicTerm = \App\Models\AcademicTerm::getActive();
            $termId = $academicTerm ? (int) $academicTerm['id'] : 0;
        }

        $facultyId = (int) ($session->get('user')['id'] ?? 0);
        $assignedSubjectIds = array_map(
            static fn(array $row): int => (int) $row['id'],
            \App\Models\Faculty::getAssignedSubjects($facultyId, $termId)
        );

        if ($subjectId <= 0 || !in_array($subjectId, $assignedSubjectIds, true)) {
            $reject('You are not assigned to this course offering.');
            return;
        }

        if ((new \App\Services\TermClosureService())->isTermClosed($termId)) {
            $reject('This academic term is officially closed and sealed. Rosters can no longer be changed.');
            return;
        }

        if (empty($rows)) {
            $reject('No student records received for import.');
            return;
        }

        set_time_limit(0);

        $targetSetId = (int) ($isJson ? $request->json('set_id', 0) : $request->post('set_id', 0));
        $facultySets = \App\Models\Set::getAssignedForFaculty($facultyId, $termId, $subjectId);
        if ($targetSetId <= 0 && count($facultySets) === 1) {
            $targetSetId = (int) ($facultySets[0]['id'] ?? 0);
        }
        $currentSubject = \App\Models\Subject::find($subjectId);
        $defaultYearLevel = $currentSubject ? (int) ($currentSubject->year_level ?? 1) : 1;

        $enrolledCount = 0;
        $autoCreatedCount = 0;
        $alreadyOnRoster = 0;
        $errors = [];
        $processed = [];
        $seenNumbers = [];
        $seenEmails = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 1;
            $row = is_array($row) ? $row : [];

            $studentNumber = trim((string) ($row['student_number'] ?? ''));
            $email = trim((string) ($row['email'] ?? ''));
            $firstName = trim((string) ($row['first_name'] ?? ''));
            $lastName = trim((string) ($row['last_name'] ?? ''));
            $rowSection = trim((string) ($row['section'] ?? ''));

            $givenName = trim($lastName . ', ' . $firstName);
            $label = $givenName !== '' ? $givenName : "Row {$rowNum}";

            if ($studentNumber === '' && $email === '') {
                $errors[] = [
                    'row' => $rowNum,
                    'name' => $label,
                    'message' => 'Provide a student ID number or an email address.',
                ];
                continue;
            }

            // Deduplicate duplicate rows within the same batch
            if ($studentNumber !== '' && isset($seenNumbers[$studentNumber])) {
                $alreadyOnRoster++;
                continue;
            }
            if ($email !== '' && isset($seenEmails[strtolower($email)])) {
                $alreadyOnRoster++;
                continue;
            }

            $student = $this->studentRepository->findByIdentifier($studentNumber, $email);

            if (!$student) {
                // If student is not registered in the system yet, automatically provision them
                // provided we have their names from the class list.
                if ($firstName !== '' || $lastName !== '') {
                    try {
                        if ($firstName === '') $firstName = 'Student';
                        if ($lastName === '') $lastName = ($studentNumber !== '' ? $studentNumber : 'GWC');

                        // Resolve Section and Year Level
                        $rowSetId = $targetSetId;
                        $rowYearLevel = $defaultYearLevel;
                        $rowStatus = \App\Models\Student::STATUS_IRREGULAR;

                        if ($rowSection !== '') {
                            $matchedSet = \App\Models\Set::where('academic_term_id', $termId)
                                ->where(function ($q) use ($rowSection) {
                                    $q->where('set_name', $rowSection)->orWhere('name', $rowSection);
                                })->first();
                            if ($matchedSet) {
                                $rowSetId = (int) $matchedSet->id;
                                $rowYearLevel = (int) ($matchedSet->year_level ?? $defaultYearLevel);
                            }
                        } elseif ($rowSetId > 0) {
                            $setObj = \App\Models\Set::find($rowSetId);
                            if ($setObj) {
                                $rowYearLevel = (int) ($setObj->year_level ?? $defaultYearLevel);
                            }
                        }

                        if ($rowSetId > 0) {
                            $rowStatus = \App\Models\Student::STATUS_REGULAR;
                        }

                        // Generate unique email address if not provided
                        $candidateEmail = $email;
                        if ($candidateEmail === '' || !filter_var($candidateEmail, FILTER_VALIDATE_EMAIL)) {
                            $cleanNum = preg_replace('/[^a-zA-Z0-9]/', '', $studentNumber);
                            $baseEmail = $cleanNum !== ''
                                ? strtolower($cleanNum)
                                : strtolower(preg_replace('/[^a-z0-9]/', '', $firstName) . '.' . preg_replace('/[^a-z0-9]/', '', $lastName));
                            $baseEmail = $baseEmail !== '' ? $baseEmail : 'student_' . mt_rand(1000, 9999);
                            $candidateEmail = $baseEmail . '@gwc.edu.ph';

                            $c = 1;
                            while (\App\Models\User::where('email', $candidateEmail)->exists()) {
                                $candidateEmail = str_replace('@gwc.edu.ph', '', $baseEmail) . $c . '@gwc.edu.ph';
                                $c++;
                            }
                        }
                        $email = $candidateEmail;

                        // Check if a User already exists with this student_number or email
                        $existingUser = null;
                        if ($studentNumber !== '') {
                            $existingUser = \App\Models\User::where('student_number', $studentNumber)->first();
                        }
                        if (!$existingUser && $email !== '') {
                            $existingUser = \App\Models\User::where('email', $email)->first();
                        }

                        if ($existingUser) {
                            $studentModel = \App\Models\Student::where('user_id', $existingUser->id)->first();
                            if (!$studentModel) {
                                $studentModel = \App\Models\Student::create([
                                    'user_id' => (int) $existingUser->id,
                                    'set_id' => $rowSetId > 0 ? $rowSetId : null,
                                    'year_level' => $rowYearLevel,
                                    'status' => $rowStatus,
                                ]);
                                try {
                                    (new \App\Services\StudentRegistrationService())->register(
                                        (int) $studentModel->id,
                                        $termId,
                                        $rowStatus,
                                        $rowYearLevel,
                                        $rowSetId > 0 ? $rowSetId : null
                                    );
                                } catch (\Throwable $e) {}
                            }
                            $studentId = (int) $studentModel->id;
                        } else {
                            $tempPassword = \App\Models\User::generateRandomPassword();
                            $newUser = \App\Models\User::create([
                                'first_name' => $firstName,
                                'last_name' => $lastName,
                                'email' => $email,
                                'student_number' => $studentNumber !== '' ? $studentNumber : null,
                                'password' => password_hash($tempPassword, PASSWORD_BCRYPT),
                                'role' => 'Student',
                                'status' => 'active',
                                'force_password_change' => true,
                            ]);
                            $userId = (int) $newUser->id;

                            $newStudent = \App\Models\Student::create([
                                'user_id' => $userId,
                                'set_id' => $rowSetId > 0 ? $rowSetId : null,
                                'year_level' => $rowYearLevel,
                                'status' => $rowStatus,
                            ]);
                            $studentId = (int) $newStudent->id;

                            try {
                                (new \App\Services\StudentRegistrationService())->register(
                                    $studentId,
                                    $termId,
                                    $rowStatus,
                                    $rowYearLevel,
                                    $rowSetId > 0 ? $rowSetId : null
                                );
                            } catch (\Throwable $e) {}

                            $autoCreatedCount++;
                        }
                    } catch (\Throwable $e) {
                        $errors[] = [
                            'row' => $rowNum,
                            'name' => $label,
                            'message' => 'Unable to auto-register student: ' . $e->getMessage(),
                        ];
                        continue;
                    }
                } else {
                    $errors[] = [
                        'row' => $rowNum,
                        'name' => $label,
                        'message' => 'No registered student matches ' . ($studentNumber !== ''
                            ? "student ID '{$studentNumber}'"
                            : "'{$email}'") . '. Students must exist before being rostered.',
                    ];
                    continue;
                }
            } else {
                $studentId = (int) $student['id'];
            }

            if ($studentNumber !== '') {
                $seenNumbers[$studentNumber] = $studentId;
            }
            if ($email !== '') {
                $seenEmails[strtolower($email)] = $studentId;
            }

            if (isset($processed[$studentId])) {
                $alreadyOnRoster++;
                continue;
            }

            if (\App\Models\Enrollment::isEnrolled($studentId, $subjectId, $termId)) {
                $processed[$studentId] = true;
                $alreadyOnRoster++;
                continue;
            }

            $this->studentRepository->enroll($studentId, $subjectId, $termId);
            $processed[$studentId] = true;
            $this->logRoster('Student Added to Roster', $studentId, $subjectId, 'Imported');
            $enrolledCount++;
        }

        if ($enrolledCount > 0) {
            \App\Services\ActivityLogService::record([
                'category' => \App\Services\ActivityLogService::CATEGORY_REGISTRATION,
                'action' => 'Roster Imported',
                'target_type' => 'import',
                'target_id' => $subjectId,
                'summary' => "Imported {$enrolledCount} of " . count($rows) . ' students into a class roster.'
                    . ($autoCreatedCount > 0 ? " ({$autoCreatedCount} new accounts created)." : '')
                    . ($alreadyOnRoster > 0 ? " {$alreadyOnRoster} were already on the roster." : '')
                    . (!empty($errors) ? ' ' . count($errors) . ' rows were skipped.' : ''),
            ]);
        }

        $message = "Roster import complete: {$enrolledCount} of " . count($rows) . ' students enrolled.';
        if ($autoCreatedCount > 0) {
            $message .= " ({$autoCreatedCount} new student accounts registered).";
        }
        if ($alreadyOnRoster > 0) {
            $message .= " {$alreadyOnRoster} already on the roster.";
        }
        if (!empty($errors)) {
            $message .= ' ' . count($errors) . ' could not be matched.';
        }

        if ($isJson) {
            $response->json([
                'success' => $enrolledCount > 0 || ($alreadyOnRoster > 0 && empty($errors)),
                'total' => count($rows),
                'enrolled' => $enrolledCount,
                'created' => $autoCreatedCount,
                'already_enrolled' => $alreadyOnRoster,
                'failed' => count($errors),
                'errors' => $errors,
                'message' => $message,
            ]);
            return;
        }

        if ($enrolledCount > 0) {
            $session->flash('success', $message);
        } else {
            $session->flash('error', !empty($errors)
                ? 'Import failed: ' . implode('; ', array_column($errors, 'message'))
                : $message);
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

        $this->logRoster('Student Removed from Roster', $studentId, $subjectId, 'Removed');
        $session->flash('success', 'Student removed from this course roster.');
        redirect("/faculty/students?subject_id={$subjectId}{$semQuery}");
    }

    public function getPassData(Request $request, Response $response): void
    {
        $studentId = (int) $request->get('student_id');
        $subjectId = (int) $request->get('subject_id');
        $termId = (int) $request->get('term_id');

        if ($studentId <= 0 || $subjectId <= 0 || $termId <= 0) {
            $response->json(['error' => 'Missing parameters'], 400);
            return;
        }

        $pass = (new \App\Services\EvaluationService())->getDigitalPass($studentId, $subjectId, $termId);
        $response->json($pass ?: ['error' => 'Student not found']);
    }

    private function logRoster(string $action, int $studentId, int $subjectId, string $lead): void
    {
        $student = \App\Models\Student::find($studentId);
        $detail = $student ? \App\Models\StudentDetail::where('user_id', $student->user_id)->first() : null;
        $name = $detail ? trim($detail->first_name . ' ' . $detail->last_name) : "student #{$studentId}";
        $subject = \App\Models\Subject::find($subjectId);
        $code = $subject ? $subject->subject_code : "subject #{$subjectId}";

        // "Added X to CODE" / "Removed X from CODE" / "Created the account for X and added them to CODE"
        $summary = str_contains($lead, 'Created')
            ? "{$lead} {$code}."
            : ($lead === 'Removed' ? "Removed {$name} from the {$code} roster." : "Added {$name} to the {$code} roster.");

        \App\Services\ActivityLogService::record([
            'category' => \App\Services\ActivityLogService::CATEGORY_REGISTRATION,
            'action' => $action,
            'target_type' => 'student',
            'target_id' => $studentId,
            'target_label' => $name,
            'summary' => $summary,
        ]);
    }
}
