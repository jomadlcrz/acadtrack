<?php

declare(strict_types=1);

namespace App\Services;

class NotificationService
{
    public function sendGradeSubmitted(array $gradingSheet, array $faculty): void
    {
        // Implementation: send notification to Dean about submitted grades
        // Can be expanded to use email, in-app notifications, etc.
    }

    public function sendGradeApproved(array $gradingSheet, array $faculty): void
    {
        // Implementation: send notification to Faculty about approved grades
    }

    public function sendGradeReturned(array $gradingSheet, string $reason): void
    {
        // Implementation: send notification to Faculty about returned grades
    }
}
