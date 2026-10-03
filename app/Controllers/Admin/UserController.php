<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\UserRepository;

class UserController
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    /** The combined list was split: students and staff each have their own page. */
    public function index(Request $request, Response $response, Session $session): void
    {
        $tab = (string) $request->get('tab', '');
        $role = (string) $request->get('role', '');
        $isStaff = in_array($tab, ['faculty', 'admin'], true) || in_array($role, ['Admin', 'Dean', 'Faculty'], true);

        $query = array_filter([
            'role' => $isStaff ? $role : '',
            'search' => (string) $request->get('search', ''),
            'status' => (string) $request->get('status', ''),
        ]);
        if ($tab === 'irregular') {
            $query['status'] = 'Irregular';
        } elseif ($tab === 'regular') {
            $query['status'] = 'Regular';
        }

        redirect(($isStaff ? '/admin/staff' : '/admin/students') . ($query ? '?' . http_build_query($query) : ''));
    }

    public function create(Request $request, Response $response, Session $session): void
    {
        $departments = \App\Models\Department::getActive();
        $academicTerm = \App\Models\AcademicTerm::getActive();
        $sets = $academicTerm ? \App\Models\Set::getActiveByTerm((int) $academicTerm['id']) : [];
        $selectedRole = (string) $request->get('role', 'Student');
        $selectedStatus = (string) $request->get('enrollment_status', $request->get('status', 'Regular'));
        $selectedYearLevel = (int) $request->get('year_level', 1);
        $html = (new View())->render('admin.users.create', [
            'departments' => $departments,
            'sets' => $sets,
            'academicTerm' => $academicTerm,
            'selectedRole' => $selectedRole,
            'selectedStatus' => $selectedStatus,
            'selectedYearLevel' => $selectedYearLevel,
        ]);
        $response->html($html);
    }

    public function store(Request $request, Response $response, Session $session): void
    {
        $data = $request->all();
        $validator = new \App\Validators\UserValidator();

        if (!$validator->validate($data)) {
            $session->flash('error', $validator->firstError());
            redirect('/admin/users/create');
            return;
        }

        $plainPassword = \App\Models\User::generateRandomPassword();
        $role = (string) $data['role'];
        $forceChange = true;

        $studentNumber = null;
        if ($role === 'Student') {
            $studentNumber = trim((string) ($data['student_number'] ?? '')) ?: null;
            if (!empty($data['set_id'])) {
                $targetSet = \App\Models\Set::find((int) $data['set_id']);
                if ($targetSet && $targetSet->academic_term_id) {
                    $termClosureService = new \App\Services\TermClosureService();
                    if ($termClosureService->isTermClosed((int) $targetSet->academic_term_id)) {
                        $session->flash('error', 'Cannot assign student to a section belonging to a closed or ended academic term.');
                        redirect('/admin/users/create');
                        return;
                    }
                }
            }
        }

        $user = \App\Models\User::create([
            'first_name' => trim((string) $data['first_name']),
            'last_name' => trim((string) $data['last_name']),
            'email' => trim((string) $data['email']),
            'student_number' => $studentNumber,
            'password' => password_hash($plainPassword, PASSWORD_BCRYPT),
            'role' => $role,
            'status' => $data['status'] ?? 'active',
            'force_password_change' => $forceChange,
        ]);

        if ($user && $user->id) {
            if ($role === 'Student') {
                $setId = !empty($data['set_id']) ? (int) $data['set_id'] : null;
                $yearLevel = !empty($data['year_level']) ? (int) $data['year_level'] : 1;
                $studentStatus = \App\Models\Student::normalizeStatus($data['student_status'] ?? null);
                $setId = \App\Models\Student::resolveSetId($studentStatus, $setId);

                \App\Models\Student::updateOrCreate([
                    'user_id' => (int) $user->id,
                ], [
                    'set_id' => $setId,
                    'year_level' => $yearLevel,
                    'status' => $studentStatus,
                ]);

                \App\Models\StudentDetail::updateOrCreate([
                    'user_id' => (int) $user->id,
                ], [
                    'first_name' => trim((string) $data['first_name']),
                    'last_name' => trim((string) $data['last_name']),
                    'student_number' => $studentNumber,
                    'set_id' => $setId,
                    'year_level' => $yearLevel,
                    'status' => $studentStatus,
                ]);

                $this->registerForActiveTerm((int) $user->id, $studentStatus, $yearLevel, $setId, $session);
            } elseif (in_array($role, ['Faculty', 'Dean'], true)) {
                $deptId = !empty($data['department_id']) ? (int) $data['department_id'] : null;
                \App\Models\Faculty::updateOrCreate(
                    ['user_id' => (int) $user->id],
                    ['department_id' => $deptId]
                );
            }

            (new \App\Services\NotificationService())->sendStudentCredentials($user->toArray(), $plainPassword);
        }

        if ($user && $user->id) {
            \App\Services\ActivityLogService::record([
                'category' => \App\Services\ActivityLogService::CATEGORY_ACCOUNTS,
                'action' => 'Account Created',
                'target_type' => 'account',
                'target_id' => (int) $user->id,
                'target_label' => trim($user->first_name . ' ' . $user->last_name),
                'summary' => "Created a {$role} account for " . trim($user->first_name . ' ' . $user->last_name) . " ({$user->email}).",
            ]);
        }

        $session->flash('success', 'User created successfully.');
        redirect($this->listUrl($role));
    }

    public function edit(Request $request, Response $response, Session $session, string $id): void
    {
        $user = \App\Models\User::with(['faculty.department', 'student.set'])->find((int) $id);
        if (!$user) {
            $response->statusCode(404)->html('User not found');
            return;
        }

        if ($user->role === 'Admin') {
            $session->flash('error', 'Administrator accounts cannot be modified.');
            redirect($this->listUrl());
            return;
        }

        $departments = \App\Models\Department::getActive();
        $academicTerm = \App\Models\AcademicTerm::getActive();
        $sets = $academicTerm ? \App\Models\Set::getActiveByTerm((int) $academicTerm['id']) : [];
        $html = (new View())->render('admin.users.edit', [
            'user' => $user->toArray(),
            'departments' => $departments,
            'sets' => $sets,
        ]);
        $response->html($html);
    }

    public function update(Request $request, Response $response, Session $session, string $id): void
    {
        $user = \App\Models\User::find((int) $id);
        if (!$user) {
            $response->statusCode(404)->html('User not found');
            return;
        }

        if ($user->role === 'Admin') {
            $session->flash('error', 'Administrator accounts cannot be modified.');
            redirect($this->listUrl());
            return;
        }

        $data = $request->all();
        // Email and system role are locked to existing database record
        $data['email'] = $user->email;
        $data['role'] = $user->role;

        $validator = new \App\Validators\UserValidator();

        if (!$validator->validate($data, (int) $user->id)) {
            $session->flash('error', $validator->firstError());
            redirect('/admin/users/' . $id . '/edit');
            return;
        }

        $updateData = [
            'first_name' => trim((string) $data['first_name']),
            'last_name' => trim((string) $data['last_name']),
            'status' => $data['status'] ?? $user->status,
        ];

        if ($user->role === 'Student') {
            $studentNumber = trim((string) ($data['student_number'] ?? '')) ?: null;
            $updateData['student_number'] = $studentNumber;

            $setId = !empty($data['set_id']) ? (int) $data['set_id'] : null;
            $studentStatus = \App\Models\Student::normalizeStatus($data['student_status'] ?? null);
            $setId = \App\Models\Student::resolveSetId($studentStatus, $setId);

            if ($setId) {
                $targetSet = \App\Models\Set::find($setId);
                if ($targetSet && $targetSet->academic_term_id) {
                    $termClosureService = new \App\Services\TermClosureService();
                    if ($termClosureService->isTermClosed((int) $targetSet->academic_term_id)) {
                        $session->flash('error', 'Cannot assign student to a section belonging to a closed or ended academic term.');
                        redirect('/admin/users/' . $id . '/edit');
                        return;
                    }
                }
            }
            $yearLevel = !empty($data['year_level']) ? (int) $data['year_level'] : 1;

            \App\Models\Student::updateOrCreate(
                ['user_id' => (int) $user->id],
                [
                    'set_id' => $setId,
                    'year_level' => $yearLevel,
                    'status' => $studentStatus,
                ]
            );
            \App\Models\StudentDetail::updateOrCreate(
                ['user_id' => (int) $user->id],
                [
                    'first_name' => trim((string) $data['first_name']),
                    'last_name' => trim((string) $data['last_name']),
                    'student_number' => $studentNumber,
                    'set_id' => $setId,
                    'year_level' => $yearLevel,
                    'status' => $studentStatus,
                ]
            );

            $this->registerForActiveTerm((int) $user->id, $studentStatus, $yearLevel, $setId, $session);
        } elseif (in_array($user->role, ['Faculty', 'Dean'], true)) {
            $deptId = !empty($data['department_id']) ? (int) $data['department_id'] : null;
            \App\Models\Faculty::updateOrCreate(
                ['user_id' => (int) $user->id],
                ['department_id' => $deptId]
            );
        }

        (new \App\Repositories\UserRepository())->update((int) $user->id, $data);

        \App\Services\ActivityLogService::record([
            'category' => \App\Services\ActivityLogService::CATEGORY_ACCOUNTS,
            'action' => 'Account Updated',
            'target_type' => 'account',
            'target_id' => (int) $user->id,
            'target_label' => trim($data['first_name'] . ' ' . $data['last_name']),
            'summary' => "Updated the {$user->role} account for {$user->email}.",
        ]);

        $session->flash('success', 'User updated successfully.');
        redirect($this->listUrl((string) $user->role));
    }

    public function toggleStatus(Request $request, Response $response, Session $session, string $id): void
    {
        $user = \App\Models\User::find((int) $id);
        if (!$user) {
            $session->flash('error', 'User account not found.');
            redirect($this->listUrl());
            return;
        }

        $currentUserId = (int) ($session->get('user')['id'] ?? 0);
        if ((int) $user->id === $currentUserId) {
            $session->flash('error', 'You cannot deactivate your own active account.');
            redirect($this->listUrl());
            return;
        }

        if ($user->role === 'Admin' && $user->status === 'active') {
            $session->flash('error', 'Administrator accounts cannot be deactivated to prevent system lockout.');
            redirect($this->listUrl());
            return;
        }

        $newStatus = ($user->status === 'inactive') ? 'active' : 'inactive';
        $user->status = $newStatus;
        $user->deactivated_at = ($newStatus === 'inactive') ? date('Y-m-d H:i:s') : null;
        $user->save();

        $actionText = ($newStatus === 'inactive') ? 'deactivated' : 'activated';
        $fullName = trim($user->first_name . ' ' . $user->last_name);
        $this->logAccountStatus($user, $actionText);
        $session->flash('success', "User account for '{$fullName}' has been {$actionText} successfully.");
        redirect($this->listUrl());
    }

    public function deactivate(Request $request, Response $response, Session $session, string $id): void
    {
        $user = \App\Models\User::find((int) $id);
        if (!$user) {
            $session->flash('error', 'User account not found.');
            redirect($this->listUrl());
            return;
        }

        $currentUserId = (int) ($session->get('user')['id'] ?? 0);
        if ((int) $user->id === $currentUserId) {
            $session->flash('error', 'You cannot deactivate your own active account.');
            redirect($this->listUrl());
            return;
        }

        if ($user->role === 'Admin') {
            $session->flash('error', 'Administrator accounts cannot be deactivated to prevent system lockout.');
            redirect($this->listUrl());
            return;
        }

        $user->status = 'inactive';
        $user->deactivated_at = date('Y-m-d H:i:s');
        $user->save();

        $fullName = trim($user->first_name . ' ' . $user->last_name);
        $this->logAccountStatus($user, 'deactivated');
        $session->flash('success', "User account for '{$fullName}' has been deactivated successfully.");
        redirect($this->listUrl());
    }

    public function activate(Request $request, Response $response, Session $session, string $id): void
    {
        $user = \App\Models\User::find((int) $id);
        if (!$user) {
            $session->flash('error', 'User account not found.');
            redirect($this->listUrl());
            return;
        }

        $user->status = 'active';
        $user->deactivated_at = null;
        $user->save();

        $fullName = trim($user->first_name . ' ' . $user->last_name);
        $this->logAccountStatus($user, 'activated');
        $session->flash('success', "User account for '{$fullName}' has been activated successfully.");
        redirect($this->listUrl());
    }

    public function downloadTemplate(Request $request, Response $response): void
    {
        $headers = [
            'Student Number',
            'First Name',
            'Last Name',
            'Email',
            'Enrollment Status',
            'Year Level',
            'Class Section',
        ];

        $sampleRows = [
            ['2026-0001', 'Juan', 'Dela Cruz', 'juan.delacruz@gwc.edu.ph', 'Regular', '1', 'BSIT-1A'],
            ['2026-0002', 'Maria', 'Santos', 'maria.santos@gwc.edu.ph', 'Irregular', '2', ''],
            ['', 'Pedro', 'Reyes', 'pedro.reyes@gwc.edu.ph', 'Regular', '1', 'BSIT-1A'],
        ];

        $output = fopen('php://temp', 'r+');
        // Add UTF-8 BOM for Microsoft Excel compatibility
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, $headers);
        foreach ($sampleRows as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="GWC_Student_Registration_Template.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $csv;
        exit;
    }

    public function import(Request $request, Response $response, Session $session): void
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isJson = str_contains($contentType, 'application/json');

        $rows = [];
        if ($isJson) {
            $input = json_decode((string) file_get_contents('php://input'), true);
            $rows = $input['students'] ?? [];
        } elseif (!empty($_FILES['excel_file']['tmp_name'])) {
            $filePath = $_FILES['excel_file']['tmp_name'];
            if (($handle = fopen($filePath, 'r')) !== false) {
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }
                $header = fgetcsv($handle);
                while (($data = fgetcsv($handle)) !== false) {
                    if (empty(array_filter($data))) continue;
                    $rows[] = [
                        'student_number' => trim((string) ($data[0] ?? '')),
                        'first_name' => trim((string) ($data[1] ?? '')),
                        'last_name' => trim((string) ($data[2] ?? '')),
                        'email' => trim((string) ($data[3] ?? '')),
                        'student_status' => trim((string) ($data[4] ?? 'Regular')),
                        'year_level' => trim((string) ($data[5] ?? '1')),
                        'section' => trim((string) ($data[6] ?? '')),
                    ];
                }
                fclose($handle);
            }
        }

        if (empty($rows)) {
            if ($isJson) {
                $response->statusCode(422)->json([
                    'success' => false,
                    'message' => 'No student records received for import.',
                ]);
            } else {
                $session->flash('error', 'No valid student records found in uploaded file.');
                redirect($this->listUrl());
            }
            return;
        }

        $academicTerm = \App\Models\AcademicTerm::getActive();
        $termId = $academicTerm ? (int) $academicTerm['id'] : null;
        $sets = $termId ? \App\Models\Set::getActiveByTerm($termId) : [];

        $setsByCode = [];
        foreach ($sets as $set) {
            $code = strtoupper(trim((string) $set['name']));
            $year = (int) ($set['year_level'] ?? 1);
            $setsByCode[$code . '_' . $year] = (int) $set['id'];
            $setsByCode[$code] = (int) $set['id'];
        }

        $createdCount = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 1;
            $firstName = trim((string) ($row['first_name'] ?? ''));
            $lastName = trim((string) ($row['last_name'] ?? ''));
            $email = trim((string) ($row['email'] ?? ''));
            $studentNumber = trim((string) ($row['student_number'] ?? '')) ?: null;
            $status = \App\Models\Student::normalizeStatus($row['student_status'] ?? null);
            $yearLevel = (int) ($row['year_level'] ?? 1);
            if ($yearLevel < 1 || $yearLevel > 4) {
                $yearLevel = 1;
            }

            if (empty($firstName) || empty($lastName)) {
                $errors[] = [
                    'row' => $rowNum,
                    'name' => "Row {$rowNum}",
                    'message' => 'First name and last name are required.',
                ];
                continue;
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = [
                    'row' => $rowNum,
                    'name' => "{$firstName} {$lastName}",
                    'message' => "Invalid email address '{$email}'.",
                ];
                continue;
            }

            if (\App\Models\User::where('email', $email)->exists()) {
                $errors[] = [
                    'row' => $rowNum,
                    'name' => "{$firstName} {$lastName}",
                    'message' => "Email '{$email}' is already registered.",
                ];
                continue;
            }

            if ($studentNumber && \App\Models\User::where('student_number', $studentNumber)->exists()) {
                $errors[] = [
                    'row' => $rowNum,
                    'name' => "{$firstName} {$lastName}",
                    'message' => "Student ID '{$studentNumber}' is already registered.",
                ];
                continue;
            }

            $setId = null;
            if (\App\Models\Student::requiresSet($status)) {
                $section = strtoupper(trim((string) ($row['section'] ?? $row['set_name'] ?? $row['set_id'] ?? '')));
                if (!empty($section)) {
                    if (is_numeric($section) && \App\Models\Set::find((int) $section)) {
                        $setId = (int) $section;
                    } elseif (isset($setsByCode[$section . '_' . $yearLevel])) {
                        $setId = $setsByCode[$section . '_' . $yearLevel];
                    } elseif (isset($setsByCode[$section])) {
                        $setId = $setsByCode[$section];
                    }
                }

                if (!$setId && !empty($sets)) {
                    foreach ($sets as $s) {
                        if ((int) $s['year_level'] === $yearLevel) {
                            $setId = (int) $s['id'];
                            break;
                        }
                    }
                }

                if (!$setId) {
                    $errors[] = [
                        'row' => $rowNum,
                        'name' => "{$firstName} {$lastName}",
                        'message' => "No active class set found for Year {$yearLevel}" . ($section ? " section '{$section}'" : "") . ".",
                    ];
                    continue;
                }
            }

            try {
                $plainPassword = \App\Models\User::generateRandomPassword();
                $user = \App\Models\User::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'student_number' => $studentNumber,
                    'password' => password_hash($plainPassword, PASSWORD_BCRYPT),
                    'role' => 'Student',
                    'set_id' => $setId,
                    'year_level' => $yearLevel,
                    'status' => 'active',
                    'student_status' => $status,
                    'force_password_change' => 1,
                ]);

                if ($user && $user->id) {
                    \App\Models\Student::updateOrCreate(
                        ['user_id' => (int) $user->id],
                        [
                            'set_id' => $setId,
                            'year_level' => $yearLevel,
                            'status' => $status,
                        ]
                    );

                    \App\Models\StudentDetail::updateOrCreate(
                        ['user_id' => (int) $user->id],
                        [
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                            'student_number' => $studentNumber,
                            'set_id' => $setId,
                            'year_level' => $yearLevel,
                            'status' => $status,
                        ]
                    );

                    $this->registerForActiveTerm((int) $user->id, $status, $yearLevel, $setId, $session);

                    (new \App\Services\NotificationService())->sendStudentCredentials($user->toArray(), $plainPassword);
                    $createdCount++;
                }
            } catch (\Throwable $e) {
                $errors[] = [
                    'row' => $rowNum,
                    'name' => "{$firstName} {$lastName}",
                    'message' => "Registration failed: " . $e->getMessage(),
                ];
            }
        }

        if ($createdCount > 0) {
            \App\Services\ActivityLogService::record([
                'category' => \App\Services\ActivityLogService::CATEGORY_ACCOUNTS,
                'action' => 'Students Imported',
                'target_type' => 'import',
                'summary' => "Imported {$createdCount} of " . count($rows) . ' student accounts from a spreadsheet.' . (!empty($errors) ? ' ' . count($errors) . ' rows were skipped.' : ''),
            ]);
        }

        if ($isJson) {
            $response->json([
                'success' => $createdCount > 0,
                'total' => count($rows),
                'created' => $createdCount,
                'failed' => count($errors),
                'errors' => $errors,
                'message' => "Successfully registered {$createdCount} of " . count($rows) . " student accounts.",
            ]);
            return;
        }

        if ($createdCount > 0) {
            $msg = "Batch registration complete: {$createdCount} student accounts registered successfully.";
            if (!empty($errors)) {
                $msg .= " " . count($errors) . " records had errors and were skipped: " . implode('; ', array_column($errors, 'message'));
            }
            $session->flash('success', $msg);
        } else {
            $session->flash('error', "Import failed: " . implode('; ', array_column($errors, 'message')));
        }
        redirect($this->listUrl());
    }

    /** Registers the student for the active term; a broken school rule is flashed, not fatal. */
    private function registerForActiveTerm(int $userId, string $status, int $yearLevel, ?int $setId, Session $session): void
    {
        $term = \App\Models\AcademicTerm::getActive();
        $student = \App\Models\Student::where('user_id', $userId)->first();
        if (!$term || !$student) {
            return;
        }

        try {
            (new \App\Services\StudentRegistrationService())->register(
                (int) $student->id,
                (int) $term['id'],
                $status,
                $yearLevel,
                $setId,
                (int) ($session->get('user')['id'] ?? 0) ?: null
            );
        } catch (\DomainException $e) {
            $session->flash('error', 'Student saved, but term registration failed: ' . $e->getMessage());
        }
    }

    /** Where to send the admin after an account action: staff or students, keeping the list they came from. */
    private function listUrl(string $role = ''): string
    {
        if ($role !== '') {
            return $role === 'Student' ? '/admin/students' : '/admin/staff';
        }

        $referer = parse_url((string) ($_SERVER['HTTP_REFERER'] ?? ''));
        $path = (string) ($referer['path'] ?? '');
        $base = base_path_url();
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        if ($path === '/admin/staff' || $path === '/admin/students') {
            return $path . (!empty($referer['query']) ? '?' . $referer['query'] : '');
        }
        return '/admin/students';
    }

    private function logAccountStatus(\App\Models\User $user, string $verb): void
    {
        \App\Services\ActivityLogService::record([
            'category' => \App\Services\ActivityLogService::CATEGORY_ACCOUNTS,
            'action' => 'Account ' . ucfirst($verb),
            'target_type' => 'account',
            'target_id' => (int) $user->id,
            'target_label' => trim($user->first_name . ' ' . $user->last_name),
            'summary' => ucfirst($verb) . " the {$user->role} account for {$user->email}.",
        ]);
    }
}
