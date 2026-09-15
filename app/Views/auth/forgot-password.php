<?php
$pageTitle = 'Forgot Password';
$authHeading = 'Reset your password';
$authSubheading = 'Enter your email address and we\'ll send you a link to reset your password.';
ob_start();
?>

<form method="POST" action="<?= url('/forgot-password') ?>" class="auth-form" id="forgotPasswordForm" novalidate>
    <?= csrf_field() ?>

    <!-- Email Field -->
    <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input 
            type="email" 
            id="email" 
            name="email" 
            class="form-control" 
            placeholder="Enter your email address" 
            required 
            autocomplete="email"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
        >
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn-auth-submit mt-4" id="submitBtn">
        <span>Send reset link</span>
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
    const forgotForm = document.getElementById('forgotPasswordForm');
    const submitBtn = document.getElementById('submitBtn');

    if (forgotForm && submitBtn) {
        forgotForm.addEventListener('submit', function() {
            if (forgotForm.checkValidity()) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Sending link...';
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/auth.php';
?>
