<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PasswordReset;
use App\Models\User;
use App\Models\Notification;
use Exception;

class PasswordResetService
{
    private EmailService $emailService;

    public function __construct(?EmailService $emailService = null)
    {
        $this->emailService = $emailService ?? new EmailService();
    }

    /**
     * Generate and store a secure password reset token for the given email.
     */
    public function createResetToken(string $email): string
    {
        // Invalidate any existing unused reset tokens for this email
        PasswordReset::where('email', $email)
            ->whereNull('used_at')
            ->update(['used_at' => date('Y-m-d H:i:s')]);

        $token = bin2hex(random_bytes(32));
        $now = date('Y-m-d H:i:s');
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 60 minutes validity

        PasswordReset::create([
            'email' => $email,
            'token' => $token,
            'created_at' => $now,
            'expires_at' => $expiresAt,
            'used_at' => null,
        ]);

        return $token;
    }

    /**
     * Build the absolute URL for the password reset action.
     */
    public function getResetUrl(string $token): string
    {
        $appUrl = (string) env('APP_URL', '');
        if (empty($appUrl)) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $appUrl = $scheme . '://' . $host . (function_exists('base_path_url') ? base_path_url() : '');
        }

        return rtrim($appUrl, '/') . '/reset-password?token=' . urlencode($token);
    }

    /**
     * Send password reset instructions to user via email.
     */
    public function sendResetEmail(string $email, string $token): bool
    {
        $resetUrl = $this->getResetUrl($token);
        $subject = 'Reset Your Password — Acadtrack';

        $htmlBody = $this->buildEmailTemplate($email, $resetUrl);

        return $this->emailService->send($email, $subject, $htmlBody);
    }

    /**
     * Validate whether a reset token is valid and unexpired.
     */
    public function validateToken(string $token): ?PasswordReset
    {
        if (empty($token)) {
            return null;
        }

        /** @var PasswordReset|null $record */
        $record = PasswordReset::where('token', $token)
            ->whereNull('used_at')
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        return $record;
    }

    /**
     * Complete the password reset with a valid token and new password.
     */
    public function resetPassword(string $token, string $newPassword): bool
    {
        $resetRecord = $this->validateToken($token);
        if (!$resetRecord) {
            return false;
        }

        $user = User::where('email', $resetRecord->email)->first();
        if (!$user) {
            return false;
        }

        $user->password = password_hash($newPassword, PASSWORD_BCRYPT);
        $user->force_password_change = false;
        $user->is_temp_password = false;
        $user->save();

        // Mark token as consumed
        $resetRecord->used_at = date('Y-m-d H:i:s');
        $resetRecord->save();

        // Optionally record a security notification
        try {
            Notification::create([
                'user_id' => (int) $user->id,
                'recipient_email' => $user->email,
                'title' => 'Password Reset Successful',
                'message' => "Your password for the GWC Acadtrack portal was successfully updated on " . date('M d, Y h:i A') . ".",
                'type' => 'general',
                'status' => 'sent',
                'sent_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (Exception $e) {
            error_log('Notification creation skipped: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Construct institutional HTML email for password recovery.
     */
    private function buildEmailTemplate(string $email, string $resetUrl): string
    {
        $safeUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');
        $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Your Acadtrack Password</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; color: #1e293b; }
        .wrapper { width: 100%; max-width: 580px; margin: 30px auto; background-color: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; }
        .header { background-color: #0f2744; padding: 28px 32px; text-align: left; }
        .header-brand { font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #93c5fd; font-weight: 600; margin-bottom: 4px; }
        .header-title { font-size: 20px; color: #ffffff; font-weight: 700; margin: 0; }
        .content { padding: 36px 32px; line-height: 1.6; font-size: 14.5px; color: #334155; }
        .greeting { font-size: 16px; font-weight: 600; color: #0f172a; margin-bottom: 16px; }
        .btn-box { text-align: center; margin: 32px 0; }
        .btn { display: inline-block; background-color: #1e40af; color: #ffffff !important; text-decoration: none; padding: 12px 28px; font-size: 14px; font-weight: 600; border-radius: 6px; }
        .notice { font-size: 13px; color: #64748b; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px 16px; margin: 24px 0; }
        .fallback { font-size: 12.5px; color: #64748b; word-break: break-all; margin-top: 24px; padding-top: 20px; border-top: 1px solid #e2e8f0; }
        .footer { background-color: #f8fafc; padding: 20px 32px; font-size: 12px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <div class="header-brand">Golden West Colleges, Inc.</div>
            <h1 class="header-title">Acadtrack Password Recovery</h1>
        </div>
        <div class="content">
            <div class="greeting">Hello,</div>
            <p>We received a request to reset the password for your Acadtrack account registered under <strong>{$safeEmail}</strong>.</p>
            <p>To choose a new password and regain access to your account, click the button below:</p>
            
            <div class="btn-box">
                <a href="{$safeUrl}" class="btn" target="_blank" rel="noopener">Reset Password</a>
            </div>

            <div class="notice">
                <strong>Important Notice:</strong> This password reset link is valid for <strong>60 minutes</strong> from when it was requested and can only be used once.
            </div>

            <p style="margin-bottom: 0;">If you did not request a password reset, you can safely disregard this email. Your account credentials remain secure.</p>

            <div class="fallback">
                If the button above does not work, copy and paste the following URL into your web browser:<br>
                <a href="{$safeUrl}" style="color: #1e40af;">{$safeUrl}</a>
            </div>
        </div>
        <div class="footer">
            Golden West Colleges, Inc. &bull; San Jose Drive, Alaminos, Pangasinan<br>
            &copy; 2026 Acadtrack &mdash; Academic Grading &amp; Records Management Portal
        </div>
    </div>
</body>
</html>
HTML;
    }
}
