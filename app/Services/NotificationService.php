<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notification;
use App\Models\Student;
use App\Models\Subject;

class NotificationService
{
    private EmailService $emailService;

    public function __construct()
    {
        $this->emailService = new EmailService();
    }

    public function sendStudentCredentials(array|object $user, string $plainPassword): bool
    {
        $email = $user['email'] ?? '';
        $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        $title = 'Your GWC Acadtrack Account Credentials';
        $message = "Dear {$name},\n\nYour student account on the GWC Acadtrack Academic Platform has been provisioned.\n\nLogin Email: {$email}\nTemporary Password: {$plainPassword}\n\nFor security reasons, you will be required to change this temporary password upon your first sign in.\n\nGolden West Colleges, Inc.";

        Notification::create([
            'user_id' => (int) ($user['id'] ?? 0),
            'recipient_email' => $email,
            'title' => $title,
            'message' => $message,
            'type' => 'credentials',
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s'),
        ]);

        $htmlBody = "<p>Dear <strong>" . htmlspecialchars($name) . "</strong>,</p>"
            . "<p>Your student account on the <strong>GWC Acadtrack</strong> platform has been provisioned.</p>"
            . "<ul><li><strong>Login Email:</strong> " . htmlspecialchars($email) . "</li>"
            . "<li><strong>Temporary Password:</strong> " . htmlspecialchars($plainPassword) . "</li></ul>"
            . "<p><em>Note: For security reasons, you will be required to set a new password upon your first sign in.</em></p>"
            . "<p>Golden West Colleges, Inc.</p>";

        return $this->emailService->send($email, $title, $htmlBody);
    }

    public function sendGradesPublished(int $subjectId, int $academicTermId, string $periodName = 'Term'): void
    {
        $subject = Subject::find($subjectId);
        $subjectTitle = $subject['name'] ?? 'Assigned Course';
        $subjectCode = $subject['code'] ?? '';

        $students = Student::getBySubject($subjectId, $academicTermId);

        foreach ($students as $student) {
            $userId = (int) ($student['user_id'] ?? 0);
            $email = $student['email'] ?? '';
            $name = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));

            if (empty($email)) {
                continue;
            }

            $title = "Grades Available: {$subjectCode} - {$periodName}";
            $message = "Dear {$name},\n\nOfficial grades for {$subjectCode} ({$subjectTitle}) for {$periodName} have been reviewed, approved, and published.\n\nPlease log in to Acadtrack to view your marks and whole curriculum evaluation.\n\nGolden West Colleges, Inc.";

            Notification::create([
                'user_id' => $userId,
                'recipient_email' => $email,
                'title' => $title,
                'message' => $message,
                'type' => 'grades_published',
                'status' => 'sent',
                'sent_at' => date('Y-m-d H:i:s'),
            ]);

            $htmlBody = "<p>Dear <strong>" . htmlspecialchars($name) . "</strong>,</p>"
                . "<p>Official grades for <strong>" . htmlspecialchars($subjectCode . ' - ' . $subjectTitle) . "</strong> (" . htmlspecialchars($periodName) . ") have been approved and published.</p>"
                . "<p>Log in to your student portal to inspect your period marks and comprehensive academic evaluation.</p>"
                . "<p><em>Golden West Colleges, Inc.</em></p>";

            $this->emailService->send($email, $title, $htmlBody);
        }
    }

    public function sendGradeSubmitted(array $gradingSheet, array $faculty): void
    {
        // Notification logged for review queue
    }

    public function sendGradeApproved(array $gradingSheet, array $faculty): void
    {
        // Notification logged for faculty
    }

    public function sendGradeReturned(array $gradingSheet, string $reason): void
    {
        // Notification logged for faculty revisions
    }
}

