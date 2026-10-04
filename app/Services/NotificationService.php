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

        $loginUrl = EmailTemplateBuilder::getAppUrl('/login');
        $htmlBody = EmailTemplateBuilder::studentCredentials($name, $email, $plainPassword, $loginUrl);

        return $this->emailService->send($email, $title, $htmlBody);
    }

    public function sendGradesPublished(int $subjectId, int $academicTermId, string $periodName = 'Term'): void
    {
        $subject = Subject::find($subjectId);
        $subjectTitle = $subject['descriptive_title'] ?? $subject['name'] ?? 'Assigned Course';
        $subjectCode = $subject['subject_code'] ?? $subject['code'] ?? '';

        $students = Student::getBySubject($subjectId, $academicTermId);
        $portalUrl = EmailTemplateBuilder::getAppUrl('/student/evaluation');

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

            $htmlBody = EmailTemplateBuilder::gradesPublished($name, $subjectCode, $subjectTitle, $periodName, $portalUrl);

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

    /**
     * Tell the instructor their grading sheet was returned and exactly what to fix.
     *
     * @param array<string, mixed> $gradingSheet a row from GradingSheet::findWithDetails()
     */
    public function sendGradeReturned(array $gradingSheet, string $reason): void
    {
        $userId = (int) ($gradingSheet['faculty_id'] ?? 0);
        $email = (string) ($gradingSheet['faculty_email'] ?? '');
        if ($userId <= 0 || $email === '') {
            return;
        }

        $name = trim(($gradingSheet['faculty_first_name'] ?? '') . ' ' . ($gradingSheet['faculty_last_name'] ?? '')) ?: 'Instructor';
        $code = (string) ($gradingSheet['subject_code'] ?? '');
        $period = (string) ($gradingSheet['period_name'] ?? '');

        $title = "Grading Sheet Returned: {$code} - {$period}";
        $message = "Dear {$name},

Your {$period} grading sheet for {$code} was returned for revision.

Reason: {$reason}

Please correct the marks and submit the sheet again.

Golden West Colleges, Inc.";

        Notification::create([
            'user_id' => $userId,
            'recipient_email' => $email,
            'title' => $title,
            'message' => $message,
            'type' => 'general',
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s'),
        ]);

        $safe = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        $body = '<div style="font-size: 16px; font-weight: 600; color: #0f172a; margin-bottom: 16px;">Dear ' . $safe($name) . ',</div>'
            . '<p style="margin: 0 0 16px 0;">Your <strong>' . $safe($period) . '</strong> grading sheet for <strong>' . $safe($code) . '</strong> was returned for revision.</p>'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; margin: 16px 0 20px 0;"><tr><td style="padding: 14px 20px; font-size: 14px; color: #0f172a;">'
            . '<div style="font-size: 12px; text-transform: uppercase; color: #92400e; font-weight: 600; margin-bottom: 6px;">What to correct</div>' . nl2br($safe($reason))
            . '</td></tr></table>'
            . '<p style="margin: 0;">Please correct the marks and submit the sheet again.</p>';

        $this->emailService->send($email, $title, EmailTemplateBuilder::wrap('Grading Sheet Returned', $code . ' - ' . $period, $body, $title));
    }
}

