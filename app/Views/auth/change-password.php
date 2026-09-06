<?php
$pageTitle = 'Set New Password';
ob_start();
?>

<div class="mb-4 text-center">
    <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-circle mb-2" style="width: 48px; height: 48px;">
        <i class="bi bi-shield-lock text-primary" style="font-size: 1.5rem;"></i>
    </div>
    <h2 class="h5 fw-bold text-dark mb-1">Set Your New Password</h2>
    <p class="text-muted small mb-0">For your account security, you must replace your temporary password with a new password before proceeding.</p>
</div>

<form method="POST" action="<?= url('/change-password') ?>" class="auth-form" id="changePasswordForm" novalidate>
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="new_password" class="form-label fw-medium">New password <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock" aria-hidden="true"></i></span>
            <input 
                type="password" 
                id="new_password" 
                name="new_password" 
                class="form-control" 
                placeholder="Minimum 8 characters" 
                required 
                minlength="8"
                autofocus
            >
            <button type="button" class="btn btn-outline-secondary border-start-0 toggle-pwd-btn" data-target="new_password" title="Show/hide password">
                <i class="bi bi-eye"></i>
            </button>
        </div>
        <div class="form-text">Must be at least 8 characters long and different from your temporary password.</div>
    </div>

    <div class="mb-4">
        <label for="confirm_password" class="form-label fw-medium">Confirm new password <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-shield-check" aria-hidden="true"></i></span>
            <input 
                type="password" 
                id="confirm_password" 
                name="confirm_password" 
                class="form-control" 
                placeholder="Re-enter your new password" 
                required 
                minlength="8"
            >
            <button type="button" class="btn btn-outline-secondary border-start-0 toggle-pwd-btn" data-target="confirm_password" title="Show/hide password">
                <i class="bi bi-eye"></i>
            </button>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-3 shadow-sm">
        Update Password & Continue <i class="bi bi-arrow-right ms-1"></i>
    </button>
</form>

<div class="text-center pt-2 border-top">
    <form method="POST" action="<?= url('/logout') ?>" class="d-inline">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-link text-decoration-none text-muted small p-0">
            <i class="bi bi-box-arrow-left me-1"></i> Cancel and sign out
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-pwd-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            if (input && icon) {
                const isPassword = input.getAttribute('type') === 'password';
                input.setAttribute('type', isPassword ? 'text' : 'password');
                icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
                input.focus();
            }
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/auth.php';
?>
