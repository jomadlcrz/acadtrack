<?php

declare(strict_types=1);

require_once 'C:/xampp/htdocs/acadtrack/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable('C:/xampp/htdocs/acadtrack');
$dotenv->safeLoad();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "========================================\n";
echo "   Acadtrack - Gmail SMTP Diagnostics   \n";
echo "========================================\n\n";

$host = (string) env('MAIL_HOST');
$port = (int) env('MAIL_PORT', 587);
$username = trim((string) env('MAIL_USERNAME'));
$password = str_replace(' ', '', (string) env('MAIL_PASSWORD'));
$encryption = strtolower((string) env('MAIL_ENCRYPTION', 'tls'));
$fromAddress = (string) (env('MAIL_FROM_ADDRESS') ?: $username);
$fromName = (string) env('MAIL_FROM_NAME', 'GWC Acadtrack');

echo "Configuration:\n";
echo " - Host:        {$host}\n";
echo " - Port:        {$port}\n";
echo " - Encryption:  {$encryption}\n";
echo " - Username:    " . ($username ? $username : "[NOT SET - Check .env]") . "\n";
echo " - Password:    " . ($password ? str_repeat('*', strlen($password)) . " (" . strlen($password) . " chars)" : "[NOT SET - Check .env]") . "\n";
echo " - From:        {$fromName} <{$fromAddress}>\n\n";

if (empty($username) || empty($password)) {
    echo "❌ ERROR: MAIL_USERNAME or MAIL_PASSWORD is empty in .env!\n";
    echo "Please edit your C:\\xampp\\htdocs\\acadtrack\\.env file and set:\n";
    echo "MAIL_USERNAME=your_email@gmail.com\n";
    echo "MAIL_PASSWORD=your_16_character_google_app_password\n";
    exit(1);
}

$recipient = $argv[1] ?? $username;

echo "Attempting to send test email to: {$recipient} ...\n\n";

$mailer = new PHPMailer(true);

try {
    // Enable verbose debug output
    $mailer->SMTPDebug = SMTP::DEBUG_CONNECTION;
    $mailer->Debugoutput = function ($str, $level) {
        echo "   [SMTP] " . trim($str) . "\n";
    };

    $mailer->isSMTP();
    $mailer->Host = $host;
    $mailer->SMTPAuth = true;
    $mailer->Username = $username;
    $mailer->Password = $password;

    if ($encryption === 'ssl' || $port === 465) {
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mailer->Port = $port ?: 465;
    } else {
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Port = $port ?: 587;
    }

    $mailer->Timeout = 15;
    $mailer->CharSet = 'UTF-8';

    $mailer->setFrom($fromAddress ?: $username, $fromName);
    $mailer->addAddress($recipient);

    $mailer->isHTML(true);
    $mailer->Subject = 'Acadtrack - Gmail SMTP Test Verification';
    $htmlBody = \App\Services\EmailTemplateBuilder::smtpDiagnostic($recipient, date('Y-m-d H:i:s'));
    $mailer->Body = $htmlBody;
    $mailer->AltBody = "Acadtrack SMTP Test Successful! Your Google App Password is authenticated and operational. Sent at: " . date('Y-m-d H:i:s');

    $mailer->send();

    echo "\n✅ SUCCESS: Test email was sent successfully to {$recipient}!\n";
    echo "Your Google App Password is properly configured.\n";
} catch (Exception $e) {
    echo "\n❌ FAILED to send email.\n";
    echo "Mailer Error: " . $mailer->ErrorInfo . "\n\n";
    echo "Troubleshooting Tips for Google App Passwords:\n";
    echo " 1. Make sure 2-Step Verification is turned ON for your Google Account.\n";
    echo " 2. Generate a 16-character App Password at: https://myaccount.google.com/apppasswords\n";
    echo " 3. Remove any spaces when pasting the 16-character password into .env.\n";
    echo " 4. Ensure your firewall or antivirus allows outbound connections on port 587.\n";
    exit(1);
}
