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
        $expiresAt = date('Y-m-d H:i:s', time() + 900); // 15 minutes validity

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
        return EmailTemplateBuilder::passwordReset($email, $resetUrl);
    }
}
