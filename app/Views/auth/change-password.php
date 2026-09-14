<?php
$pageTitle = 'Set New Password';
ob_start();
?>

<div class="auth-prompt">
    <h2 class="auth-prompt-title">Set new password</h2>
    <p class="auth-prompt-desc">Create a new permanent password to secure your account before continuing.</p>
</div>

<form method="POST" action="<?= url('/change-password') ?>" class="auth-form" id="changePasswordForm" novalidate>
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="new_password" class="form-label">New password</label>
        <div class="input-group">
            <input 
                type="password" 
                id="new_password" 
                name="new_password" 
                class="form-control border-end-0" 
                placeholder="Enter new password" 
                required 
                minlength="8"
                autofocus
                autocomplete="new-password"
            >
            <button 
                type="button" 
                class="btn btn-toggle-pwd border-start-0" 
                data-target="new_password" 
                title="Show or hide password"
                aria-label="Toggle password visibility"
            >
                <i class="bi bi-eye" aria-hidden="true"></i>
            </button>
        </div>
        <div class="form-text text-muted" style="font-size: 12px; margin-top: 4px;">
            Must be at least 8 characters long and different from your temporary password.
        </div>
    </div>

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
            >
                <i class="bi bi-eye" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <button type="submit" class="btn-auth-submit" id="submitBtn">
        Update password
    </button>
</form>

<div class="auth-cancel-wrap">
    <form method="POST" action="<?= url('/logout') ?>" class="d-inline">
        <?= csrf_field() ?>
        <button type="submit" class="auth-cancel-btn">
            Cancel and sign out
        </button>
    </form>
</div>

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
