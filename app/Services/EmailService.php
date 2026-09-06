<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private PHPMailer $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }

    private function configure(): void
    {
        $this->mailer->isSMTP();
        $this->mailer->Host = $_ENV['MAIL_HOST'] ?? 'smtp.example.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $_ENV['MAIL_USERNAME'] ?? '';
        $this->mailer->Password = $_ENV['MAIL_PASSWORD'] ?? '';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = (int) ($_ENV['MAIL_PORT'] ?? 587);
        $this->mailer->CharSet = 'UTF-8';

        $this->mailer->setFrom(
            $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@example.com',
            $_ENV['MAIL_FROM_NAME'] ?? 'GWC Grading System'
        );
    }

    public function send(string $to, string $subject, string $htmlBody): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $htmlBody;
            $this->mailer->AltBody = strip_tags($htmlBody);
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Email failed: " . $e->getMessage());
            return false;
        }
    }
}
