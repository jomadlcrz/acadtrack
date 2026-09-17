<?php

declare(strict_types=1);

namespace App\Services;

class EmailTemplateBuilder
{
    public const LOGO_URL = 'https://lh3.googleusercontent.com/d/1qzReRd1ijh6e9oeqw8A7xyCP0RX1ZKws';

    /**
     * Resolve the base application URL for links within emails.
     */
    public static function getAppUrl(string $path = ''): string
    {
        $appUrl = (string) env('APP_URL', '');
        if (empty($appUrl)) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $basePath = function_exists('base_path_url') ? base_path_url() : '';
            $appUrl = $scheme . '://' . $host . $basePath;
        }

        $cleanPath = '/' . ltrim($path, '/');
        return rtrim($appUrl, '/') . ($path !== '' ? $cleanPath : '');
    }

    /**
     * Ensure Google usercontent image URLs are delivered at an optimal crisp retina resolution (=s400)
     * rather than having the email client downsample the uncompressed 2.4MB (1921x1920) raw source.
     */
    public static function getCrispLogoUrl(string $url): string
    {
        if (str_contains($url, 'googleusercontent.com') && !str_contains($url, '=')) {
            return $url . '=s400';
        }
        return $url;
    }

    /**
     * Master responsive email wrapper.
     */
    public static function wrap(
        string $headerTitle,
        string $headerSubtitle,
        string $bodyHtml,
        string $preheader = ''
    ): string {
        $safeTitle = htmlspecialchars($headerTitle, ENT_QUOTES, 'UTF-8');
        $safeSubtitle = htmlspecialchars($headerSubtitle, ENT_QUOTES, 'UTF-8');
        $safePreheader = htmlspecialchars($preheader ?: $headerTitle, ENT_QUOTES, 'UTF-8');
        $logoUrl = self::getCrispLogoUrl(self::LOGO_URL);

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$safeTitle}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        body { margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
        table { border-collapse: collapse; }
        img { border: 0; outline: none; text-decoration: none; }
        @media only screen and (max-width: 600px) {
            .email-container { width: 100% !important; margin: 0 !important; }
            .header-cell { padding: 24px 20px !important; }
            .content-cell { padding: 28px 20px !important; }
            .footer-cell { padding: 20px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b;">
    <!-- Hidden Preheader -->
    <div style="display: none; font-size: 1px; color: #f1f5f9; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all;">
        {$safePreheader}
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f1f5f9; width: 100%; margin: 0; padding: 32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" class="email-container" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 580px; background-color: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                    <!-- Navy & Gold Top Accent Stripe -->
                    <tr>
                        <td style="background-color: #0f2744; height: 5px; font-size: 1px; line-height: 1px;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="background-color: #d97706; height: 3px; font-size: 1px; line-height: 1px;">&nbsp;</td>
                    </tr>

                    <!-- Brand Header (Clean White with class-scheduling brand lockup) -->
                    <tr>
                        <td class="header-cell" style="background-color: #ffffff; padding: 26px 32px 22px 32px; text-align: left; border-bottom: 1px solid #e2e8f0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="vertical-align: middle; width: 56px; padding-right: 14px;">
                                        <img src="{$logoUrl}" alt="GWC Logo" width="52" height="52" style="display: block; width: 52px; height: 52px; border: 0; outline: none; -ms-interpolation-mode: bicubic; image-rendering: -webkit-optimize-contrast; image-rendering: crisp-edges;" />
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div style="font-size: 14px; text-transform: uppercase; letter-spacing: 0.06em; color: #0f2744; font-weight: 800; line-height: 1.2; margin-bottom: 3px;">
                                            Golden West Colleges, Inc.
                                        </div>
                                        <div style="font-size: 13px; color: #64748b; font-weight: 500; line-height: 1.2;">
                                            Acadtrack &mdash; Academic Portal
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            <div style="border-top: 1px solid #f1f5f9; margin-top: 18px; padding-top: 16px;">
                                <h1 style="margin: 0; font-size: 20px; font-weight: 700; color: #0f172a; line-height: 1.3;">
                                    {$safeTitle}
                                </h1>
                                <div style="margin-top: 4px; font-size: 13px; color: #64748b; line-height: 1.4;">
                                    {$safeSubtitle}
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td class="content-cell" style="padding: 36px 32px 28px 32px; line-height: 1.6; font-size: 14.5px; color: #334155;">
                            {$bodyHtml}
                        </td>
                    </tr>

                    <!-- Institutional Footer -->
                    <tr>
                        <td class="footer-cell" style="background-color: #f8fafc; padding: 22px 32px; font-size: 12px; color: #64748b; text-align: center; border-top: 1px solid #e2e8f0; line-height: 1.6;">
                            <div style="font-weight: 600; color: #475569; margin-bottom: 4px;">
                                Golden West Colleges, Inc.
                            </div>
                            <div style="color: #64748b; margin-bottom: 8px;">
                                San Jose Drive, Alaminos City, Pangasinan &bull; Philippines
                            </div>
                            <div style="font-size: 11.5px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 8px; margin-top: 8px;">
                                &copy; 2026 Acadtrack &mdash; Academic Grading &amp; Records Management System<br>
                                <span style="color: #94a3b8;">This is an automated academic communication. Please do not reply directly to this email.</span>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    /**
     * Template: Password Reset Request
     */
    public static function passwordReset(string $email, string $resetUrl): string
    {
        $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $safeUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');

        $body = <<<HTML
            <div style="font-size: 16px; font-weight: 600; color: #0f172a; margin-bottom: 16px;">
                Hello,
            </div>
            <p style="margin: 0 0 16px 0;">
                We received a request to reset the password for your Acadtrack account registered under <strong>{$safeEmail}</strong>.
            </p>
            <p style="margin: 0 0 28px 0;">
                To choose a new password and regain access to your account, click the button below:
            </p>

            <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 24px 0 28px 0; width: 100%;">
                <tr>
                    <td align="center">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="center" style="border-radius: 6px; background-color: #1e40af;">
                                    <a href="{$safeUrl}" target="_blank" rel="noopener" style="display: inline-block; padding: 12px 32px; font-size: 14px; font-weight: 600; color: #ffffff !important; text-decoration: none; border-radius: 6px;">
                                        Reset Password
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div style="font-size: 13px; color: #475569; background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 14px 16px; margin: 24px 0;">
                <strong style="color: #1e3a8a;">Important Notice:</strong> This password reset link is valid for <strong>15 minutes</strong> from when it was requested and can only be used once.
            </div>

            <p style="margin: 0 0 24px 0; color: #64748b; font-size: 13.5px;">
                If you did not request a password reset, you can safely disregard this email. Your account credentials remain secure.
            </p>

            <div style="font-size: 12px; color: #64748b; word-break: break-all; padding-top: 18px; border-top: 1px solid #e2e8f0;">
                If the button above does not work, copy and paste the following URL into your web browser:<br>
                <a href="{$safeUrl}" style="color: #1e40af; text-decoration: underline;">{$safeUrl}</a>
            </div>
HTML;

        return self::wrap(
            'Acadtrack Password Recovery',
            'Account Security Verification',
            $body,
            'Reset your Acadtrack password — link expires in 15 minutes.'
        );
    }

    /**
     * Template: Student Credentials Provisioning
     */
    public static function studentCredentials(
        string $name,
        string $email,
        string $plainPassword,
        string $loginUrl
    ): string {
        $safeName = htmlspecialchars($name ?: 'Student', ENT_QUOTES, 'UTF-8');
        $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $safePlainPassword = htmlspecialchars($plainPassword, ENT_QUOTES, 'UTF-8');
        $safeLoginUrl = htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8');

        $body = <<<HTML
            <div style="font-size: 16px; font-weight: 600; color: #0f172a; margin-bottom: 16px;">
                Dear {$safeName},
            </div>
            <p style="margin: 0 0 16px 0;">
                Welcome to <strong>Golden West Colleges, Inc.</strong> Your official student account on the <strong>GWC Acadtrack</strong> academic management platform has been provisioned.
            </p>
            <p style="margin: 0 0 20px 0;">
                Use the following credentials to access your student portal:
            </p>

            <!-- Credentials Card -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; margin: 20px 0 24px 0;">
                <tr>
                    <td style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0;">
                        <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 600; display: block; margin-bottom: 3px;">Login Email</span>
                        <span style="font-size: 14.5px; font-weight: 600; color: #0f172a; font-family: monospace;">{$safeEmail}</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 16px 20px;">
                        <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 600; display: block; margin-bottom: 3px;">Temporary Password</span>
                        <span style="display: inline-block; font-size: 15px; font-weight: 700; color: #1e40af; font-family: monospace; background-color: #e0e7ff; padding: 3px 10px; border-radius: 4px; letter-spacing: 0.04em;">{$safePlainPassword}</span>
                    </td>
                </tr>
            </table>

            <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 24px 0; width: 100%;">
                <tr>
                    <td align="center">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="center" style="border-radius: 6px; background-color: #1e40af;">
                                    <a href="{$safeLoginUrl}" target="_blank" rel="noopener" style="display: inline-block; padding: 12px 32px; font-size: 14px; font-weight: 600; color: #ffffff !important; text-decoration: none; border-radius: 6px;">
                                        Sign In to Acadtrack
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div style="font-size: 13px; color: #854d0e; background-color: #fefce8; border: 1px solid #fef08a; border-radius: 6px; padding: 14px 16px; margin: 24px 0;">
                <strong>Security Notice:</strong> For your protection, you will be required to change this temporary password and create a personal, secure password upon your initial sign-in.
            </div>

            <p style="margin: 0 0 20px 0; color: #64748b; font-size: 13px;">
                Through Acadtrack, you can track course enrollments, access verified period grades, and monitor your degree curriculum progress.
            </p>

            <div style="font-size: 12px; color: #64748b; word-break: break-all; padding-top: 18px; border-top: 1px solid #e2e8f0;">
                Direct Portal URL:<br>
                <a href="{$safeLoginUrl}" style="color: #1e40af; text-decoration: underline;">{$safeLoginUrl}</a>
            </div>
HTML;

        return self::wrap(
            'Your Acadtrack Student Credentials',
            'Official Account Provisioning',
            $body,
            'Your GWC Acadtrack student account credentials are ready.'
        );
    }

    /**
     * Template: Official Grades Published Advisory
     */
    public static function gradesPublished(
        string $name,
        string $subjectCode,
        string $subjectTitle,
        string $periodName,
        string $portalUrl
    ): string {
        $safeName = htmlspecialchars($name ?: 'Student', ENT_QUOTES, 'UTF-8');
        $safeCode = htmlspecialchars($subjectCode, ENT_QUOTES, 'UTF-8');
        $safeTitle = htmlspecialchars($subjectTitle, ENT_QUOTES, 'UTF-8');
        $safePeriod = htmlspecialchars($periodName, ENT_QUOTES, 'UTF-8');
        $safePortalUrl = htmlspecialchars($portalUrl, ENT_QUOTES, 'UTF-8');

        $body = <<<HTML
            <div style="font-size: 16px; font-weight: 600; color: #0f172a; margin-bottom: 16px;">
                Dear {$safeName},
            </div>
            <p style="margin: 0 0 16px 0;">
                Official academic grades for your enrolled subject have been verified, approved by academic department administration, and released to your student profile.
            </p>

            <!-- Course Details Card -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; margin: 20px 0 24px 0;">
                <tr>
                    <td style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0; width: 130px; font-size: 12px; text-transform: uppercase; color: #64748b; font-weight: 600;">
                        Course Code
                    </td>
                    <td style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0; font-size: 14.5px; font-weight: 700; color: #0f172a; font-family: monospace;">
                        {$safeCode}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0; font-size: 12px; text-transform: uppercase; color: #64748b; font-weight: 600;">
                        Descriptive Title
                    </td>
                    <td style="padding: 14px 20px; border-bottom: 1px solid #e2e8f0; font-size: 14px; font-weight: 500; color: #1e293b;">
                        {$safeTitle}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 14px 20px; font-size: 12px; text-transform: uppercase; color: #64748b; font-weight: 600;">
                        Grading Period
                    </td>
                    <td style="padding: 14px 20px;">
                        <span style="display: inline-block; background-color: #dbeafe; color: #1e40af; font-size: 12.5px; font-weight: 700; padding: 3px 10px; border-radius: 4px;">
                            {$safePeriod}
                        </span>
                    </td>
                </tr>
            </table>

            <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 24px 0; width: 100%;">
                <tr>
                    <td align="center">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="center" style="border-radius: 6px; background-color: #1e40af;">
                                    <a href="{$safePortalUrl}" target="_blank" rel="noopener" style="display: inline-block; padding: 12px 32px; font-size: 14px; font-weight: 600; color: #ffffff !important; text-decoration: none; border-radius: 6px;">
                                        View Academic Grades
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div style="font-size: 13px; color: #475569; background-color: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px 16px; margin: 24px 0;">
                <strong>Academic Record Note:</strong> Please sign in to your student portal to inspect detailed breakdown marks, raw performance scores, and cumulative evaluation metrics.
            </div>

            <div style="font-size: 12px; color: #64748b; word-break: break-all; padding-top: 18px; border-top: 1px solid #e2e8f0;">
                Portal Access:<br>
                <a href="{$safePortalUrl}" style="color: #1e40af; text-decoration: underline;">{$safePortalUrl}</a>
            </div>
HTML;

        return self::wrap(
            'Official Grades Published',
            'Academic Performance Advisory',
            $body,
            "Grades for {$safeCode} ({$safePeriod}) are now published in your student portal."
        );
    }

    /**
     * Template: SMTP Diagnostics Verification
     */
    public static function smtpDiagnostic(string $recipient, string $timestamp): string
    {
        $safeRecipient = htmlspecialchars($recipient, ENT_QUOTES, 'UTF-8');
        $safeTimestamp = htmlspecialchars($timestamp, ENT_QUOTES, 'UTF-8');

        $body = <<<HTML
            <div style="font-size: 16px; font-weight: 600; color: #0f172a; margin-bottom: 16px;">
                SMTP Diagnostics Successful
            </div>
            <p style="margin: 0 0 16px 0;">
                Your Google App Password and PHPMailer SMTP service configuration for the <strong>GWC Acadtrack</strong> portal are operational and verified.
            </p>

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; margin: 20px 0;">
                <tr>
                    <td style="padding: 12px 18px; border-bottom: 1px solid #e2e8f0; font-size: 12px; text-transform: uppercase; color: #64748b; font-weight: 600; width: 140px;">
                        Recipient
                    </td>
                    <td style="padding: 12px 18px; border-bottom: 1px solid #e2e8f0; font-size: 13.5px; font-weight: 600; color: #0f172a; font-family: monospace;">
                        {$safeRecipient}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 18px; font-size: 12px; text-transform: uppercase; color: #64748b; font-weight: 600;">
                        Timestamp
                    </td>
                    <td style="padding: 12px 18px; font-size: 13.5px; color: #334155;">
                        {$safeTimestamp}
                    </td>
                </tr>
            </table>

            <div style="font-size: 13px; color: #166534; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 14px 16px; margin: 20px 0;">
                <strong>Status: Operational.</strong> Transactional notifications (credential notices, grade advisories, and password resets) will be dispatched reliably through this configuration.
            </div>
HTML;

        return self::wrap(
            'Acadtrack SMTP Verification',
            'System Diagnostics & Delivery Test',
            $body,
            'Acadtrack SMTP test email delivery confirmation.'
        );
    }
}
