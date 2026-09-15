<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Models\User;
use App\Models\PasswordReset;
use App\Services\PasswordResetService;
use App\Services\EmailService;
use App\Controllers\AuthController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class PasswordResetWorkflowTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();
        new \App\Core\Database(
            env('DB_HOST'),
            env('DB_DATABASE'),
            env('DB_USERNAME'),
            (string) env('DB_PASSWORD', '')
        );

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
    }

    public function testCreateAndValidateResetToken(): void
    {
        $email = 'reset_test_' . time() . '@gwc.edu';
        $service = new PasswordResetService();

        $token = $service->createResetToken($email);

        $this->assertNotEmpty($token);
        $this->assertSame(64, strlen($token));

        $record = $service->validateToken($token);
        $this->assertNotNull($record);
        $this->assertSame($email, $record->email);
        $this->assertNull($record->used_at);

        // Non-existent token should return null
        $invalid = $service->validateToken('non_existent_token_123456');
        $this->assertNull($invalid);
    }

    public function testTokenExpirationAndInvalidation(): void
    {
        $email = 'expire_test_' . time() . '@gwc.edu';
        $service = new PasswordResetService();

        $token1 = $service->createResetToken($email);

        // Creating a second token for the same email invalidates the previous one
        $token2 = $service->createResetToken($email);

        $this->assertNull($service->validateToken($token1), 'Old token must be invalidated when a new one is requested');
        $this->assertNotNull($service->validateToken($token2), 'Latest token must be valid');

        // Test expired token validation
        PasswordReset::where('token', $token2)->update([
            'expires_at' => date('Y-m-d H:i:s', time() - 3600),
        ]);

        $this->assertNull($service->validateToken($token2), 'Expired token must return null');
    }

    public function testResetPasswordWorkflowEndToEnd(): void
    {
        $unique = time() . '_' . rand(1000, 9999);
        $email = "test_user_{$unique}@gwc.edu";
        $initialPassword = 'OldPassword123!';
        $newPassword = 'NewResetPassword2026!';

        // Create test user
        $user = User::create([
            'email' => $email,
            'password' => password_hash($initialPassword, PASSWORD_BCRYPT),
            'status' => 'active',
            'is_temp_password' => 1,
        ]);

        $this->assertNotNull($user->id);

        $service = new PasswordResetService();
        $token = $service->createResetToken($email);

        // Execute reset
        $success = $service->resetPassword($token, $newPassword);
        $this->assertTrue($success, 'Password reset must succeed for valid token and user');

        // Check updated password
        $userFresh = User::find($user->id);
        $this->assertTrue(password_verify($newPassword, $userFresh->password), 'New password must verify against bcrypt hash');
        $this->assertFalse(password_verify($initialPassword, $userFresh->password), 'Old password must no longer work');
        $this->assertFalse((bool) $userFresh->is_temp_password, 'is_temp_password flag must be cleared');

        // Token cannot be reused
        $this->assertNull($service->validateToken($token), 'Used token must not be valid for reuse');

        // Clean up
        $userFresh->delete();
        PasswordReset::where('email', $email)->delete();
    }

    public function testForgotPasswordViewRendering(): void
    {
        $view = new \App\Core\View();
        $output = $view->render('auth.forgot-password', [
            'error' => null,
            'success' => null,
            'info' => null,
        ]);

        $this->assertStringContainsString('Reset your password', $output);
        $this->assertStringContainsString('Email address', $output);
        $this->assertStringContainsString('/forgot-password', $output);
    }

    public function testResetPasswordViewRenderingWithValidToken(): void
    {
        $email = 'view_test_' . time() . '@gwc.edu';
        $service = new PasswordResetService();
        $token = $service->createResetToken($email);

        $view = new \App\Core\View();
        $output = $view->render('auth.reset-password', [
            'token' => $token,
            'error' => null,
            'success' => null,
            'info' => null,
        ]);

        $this->assertStringContainsString('Set new password', $output);
        $this->assertStringContainsString('Confirm new password', $output);
        $this->assertStringContainsString($token, $output);

        PasswordReset::where('email', $email)->delete();
    }
}
