<?php
$isResetSuccess = !empty($isSuccess);
$pageTitle = $isResetSuccess ? 'Password Reset' : 'Reset Password';
$authHeading = $isResetSuccess ? 'Password updated' : 'Set new password';
$authSubheading = $isResetSuccess ? 'Your account password has been successfully updated.' : 'Choose a strong password with at least 8 characters.';
ob_start();
?>

<?php if ($isResetSuccess): ?>
    <div class="pt-2">
        <a href="<?= url('/login') ?>" class="btn-auth-submit text-decoration-none" id="signInBtn">
            <span>Sign in</span>
        </a>
    </div>
<?php else: ?>
    <form method="POST" action="<?= url('/reset-password') ?>" class="auth-form" id="resetPasswordForm" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= htmlspecialchars((string) ($token ?? '')) ?>">

        <!-- New Password Field -->
        <div class="mb-3">
            <label for="password" class="form-label">New password</label>
            <div class="input-group">
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control border-end-0" 
                    placeholder="Enter new password" 
                    required 
                    minlength="8"
                    autocomplete="new-password"
                >
                <button 
                    type="button" 
                    class="btn btn-toggle-pwd border-start-0" 
                    data-target="password" 
                    title="Show or hide password"
                    aria-label="Toggle password visibility"
                    tabindex="-1"
                >
                    <i class="bi bi-eye text-muted" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <!-- Confirm Password Field -->
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm new password</label>
            <div class="input-group">
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    class="form-control border-end-0" 
                    placeholder="Confirm new password" 
                    required 
                    minlength="8"
                    autocomplete="new-password"
                >
                <button 
                    type="button" 
                    class="btn btn-toggle-pwd border-start-0" 
                    data-target="confirm_password" 
                    title="Show or hide password"
                    aria-label="Toggle password visibility"
                    tabindex="-1"
                >
                    <i class="bi bi-eye text-muted" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-auth-submit mt-4" id="submitBtn">
            <span>Reset password</span>
        </button>
        
        <!-- Return to Sign In -->
        <div class="text-center mt-3 pt-2">
            <a href="<?= url('/login') ?>" class="auth-link-subtle d-inline-flex align-items-center gap-1.5">
                <i class="bi bi-arrow-left"></i>
                <span>Back to sign in</span>
            </a>
        </div>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.btn-toggle-pwd').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');
                if (input && icon) {
                    const isPassword = input.getAttribute('type') === 'password';
                    input.setAttribute('type', isPassword ? 'text' : 'password');
                    icon.className = isPassword ? 'bi bi-eye-slash text-primary' : 'bi bi-eye text-muted';
                    input.focus();
                }
            });
        });

        const resetForm = document.getElementById('resetPasswordForm');
        const submitBtn = document.getElementById('submitBtn');

        if (resetForm && submitBtn) {
            resetForm.addEventListener('submit', function() {
                if (resetForm.checkValidity()) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Updating password...';
                }
            });
        }
    });
    </script>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/auth.php';
?>
