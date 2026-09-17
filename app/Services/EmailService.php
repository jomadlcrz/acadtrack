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
        $this->mailer->Host = (string) env('MAIL_HOST');
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = trim((string) env('MAIL_USERNAME'));
        $this->mailer->Password = str_replace(' ', '', (string) env('MAIL_PASSWORD'));
        
        $encryption = strtolower((string) env('MAIL_ENCRYPTION', 'tls'));
        $port = (int) env('MAIL_PORT', 587);

        if ($encryption === 'ssl' || $port === 465) {
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $this->mailer->Port = $port ?: 465;
        } else {
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = $port ?: 587;
        }

        $this->mailer->Timeout = 15;
        $this->mailer->CharSet = 'UTF-8';

        $fromAddress = (string) (env('MAIL_FROM_ADDRESS') ?: env('MAIL_USERNAME'));
        $fromName = (string) env('MAIL_FROM_NAME', 'GWC Acadtrack');

        if (!empty($fromAddress) && filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
            $this->mailer->setFrom($fromAddress, $fromName);
        }
    }

    public function send(string $to, string $subject, string $htmlBody): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
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
