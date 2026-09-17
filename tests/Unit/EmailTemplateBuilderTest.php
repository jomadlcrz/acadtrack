<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\EmailTemplateBuilder;
use App\Services\PasswordResetService;
use App\Services\NotificationService;
use App\Services\EmailService;

class EmailTemplateBuilderTest extends TestCase
{
    private const EXPECTED_LOGO = 'https://lh3.googleusercontent.com/d/1qzReRd1ijh6e9oeqw8A7xyCP0RX1ZKws';

    public function testLogoConstantMatchesExactProvidedUrl(): void
    {
        $this->assertSame(self::EXPECTED_LOGO, EmailTemplateBuilder::LOGO_URL);
    }

    public function testPasswordResetTemplateContainsLogoAndConsistentStructure(): void
    {
        $email = 'student.test@gwc.edu.ph';
        $resetUrl = 'https://portal.gwc.edu.ph/reset-password?token=sample_token_xyz123';

        $html = EmailTemplateBuilder::passwordReset($email, $resetUrl);

        // Verify Logo
        $this->assertStringContainsString(self::EXPECTED_LOGO, $html, 'Password reset email must include official logo URL');
        $this->assertStringContainsString('=s400', $html, 'Logo must use high-DPI retina downsampling parameter');
        $this->assertStringContainsString('alt="GWC Logo"', $html);
        $this->assertStringContainsString('width="52"', $html);
        $this->assertStringContainsString('-ms-interpolation-mode: bicubic', $html);
        $this->assertStringNotContainsString('background-color: #ffffff; padding: 2px', $html, 'Logo must not have white background or padding');

        // Verify Branding & Layout Consistency
        $this->assertStringContainsString('Golden West Colleges, Inc.', $html);
        $this->assertStringContainsString('Acadtrack &mdash; Academic Portal', $html);
        $this->assertStringContainsString('Acadtrack Password Recovery', $html);
        $this->assertStringContainsString('Reset Password', $html);
        $this->assertStringContainsString('15 minutes', $html);
        $this->assertStringContainsString($email, $html);
        $this->assertStringContainsString($resetUrl, $html);

        // Verify Footer Consistency
        $this->assertStringContainsString('San Jose Drive, Alaminos City, Pangasinan', $html);
        $this->assertStringContainsString('&copy; 2026 Acadtrack', $html);
        $this->assertStringContainsString('automated academic communication', $html);
    }

    public function testStudentCredentialsTemplateContainsLogoAndCredentialsCard(): void
    {
        $name = 'Maria Santos';
        $email = 'maria.santos@gwc.edu.ph';
        $plainPass = 'TempPass@2026';
        $loginUrl = 'https://portal.gwc.edu.ph/login';

        $html = EmailTemplateBuilder::studentCredentials($name, $email, $plainPass, $loginUrl);

        // Verify Logo
        $this->assertStringContainsString(self::EXPECTED_LOGO, $html, 'Student credentials email must include official logo URL');

        // Verify Structure & Credentials Card
        $this->assertStringContainsString('Dear Maria Santos,', $html);
        $this->assertStringContainsString('Your Acadtrack Student Credentials', $html);
        $this->assertStringContainsString('Login Email', $html);
        $this->assertStringContainsString($email, $html);
        $this->assertStringContainsString('Temporary Password', $html);
        $this->assertStringContainsString($plainPass, $html);
        $this->assertStringContainsString('Sign In to Acadtrack', $html);
        $this->assertStringContainsString('Security Notice:', $html);
        $this->assertStringContainsString($loginUrl, $html);

        // Verify Footer
        $this->assertStringContainsString('&copy; 2026 Acadtrack', $html);
    }

    public function testGradesPublishedTemplateContainsLogoAndCourseCard(): void
    {
        $name = 'Juan Dela Cruz';
        $subjectCode = 'IT-311';
        $subjectTitle = 'Advanced Database Systems';
        $periodName = 'Midterm';
        $portalUrl = 'https://portal.gwc.edu.ph/student/evaluation';

        $html = EmailTemplateBuilder::gradesPublished($name, $subjectCode, $subjectTitle, $periodName, $portalUrl);

        // Verify Logo
        $this->assertStringContainsString(self::EXPECTED_LOGO, $html, 'Grades published email must include official logo URL');

        // Verify Structure & Course Info
        $this->assertStringContainsString('Dear Juan Dela Cruz,', $html);
        $this->assertStringContainsString('Official Grades Published', $html);
        $this->assertStringContainsString('Course Code', $html);
        $this->assertStringContainsString($subjectCode, $html);
        $this->assertStringContainsString($subjectTitle, $html);
        $this->assertStringContainsString($periodName, $html);
        $this->assertStringContainsString('View Academic Grades', $html);
        $this->assertStringContainsString($portalUrl, $html);

        // Verify Footer
        $this->assertStringContainsString('&copy; 2026 Acadtrack', $html);
    }

    public function testSmtpDiagnosticTemplateContainsLogoAndSystemDetails(): void
    {
        $recipient = 'admin@gwc.edu.ph';
        $timestamp = '2026-09-17 22:30:00';

        $html = EmailTemplateBuilder::smtpDiagnostic($recipient, $timestamp);

        $this->assertStringContainsString(self::EXPECTED_LOGO, $html);
        $this->assertStringContainsString('Acadtrack SMTP Verification', $html);
        $this->assertStringContainsString('SMTP Diagnostics Successful', $html);
        $this->assertStringContainsString($recipient, $html);
        $this->assertStringContainsString($timestamp, $html);
    }

    public function testXssEscapingInTemplates(): void
    {
        $xssName = '<script>alert("xss")</script>';
        $xssEmail = 'malicious<script>@evil.com';

        $html = EmailTemplateBuilder::studentCredentials($xssName, $xssEmail, 'pass123', 'https://portal.gwc.edu.ph');

        $this->assertStringNotContainsString('<script>alert("xss")</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', $html);
    }
}
