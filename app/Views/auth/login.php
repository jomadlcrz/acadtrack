<?php
$pageTitle = 'Sign In';
$authHeading = 'Sign in';
$authSubheading = 'to continue to your Acadtrack portal';
ob_start();
?>

<form method="POST" action="<?= url('/login') ?>" class="auth-form" id="loginForm" novalidate>
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

    <!-- Password Field -->
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="password" class="form-label mb-0">Password</label>
            <a href="<?= url('/forgot-password') ?>" class="auth-link-subtle" tabindex="5">
                Forgot password?
            </a>
        </div>
        <div class="input-group">
            <input 
                type="password" 
                id="password" 
                name="password" 
                class="form-control border-end-0" 
                placeholder="Enter your password" 
                required 
                autocomplete="current-password"
            >
            <button 
                type="button" 
                class="btn btn-toggle-pwd border-start-0" 
                id="togglePasswordBtn" 
                title="Show or hide password"
                aria-label="Toggle password visibility"
                tabindex="-1"
            >
                <i class="bi bi-eye text-muted" id="toggleIcon" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <!-- Submit Button -->
    <button type="submit" class="btn-auth-submit mt-4" id="submitBtn">
        <span>Sign in</span>
    </button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    const loginForm = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');

    if (toggleBtn && passwordInput && toggleIcon) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            toggleIcon.className = isPassword ? 'bi bi-eye-slash text-primary' : 'bi bi-eye text-muted';
            passwordInput.focus();
        });
    }

    if (loginForm && submitBtn) {
        loginForm.addEventListener('submit', function() {
            if (loginForm.checkValidity()) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Signing in...';
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/auth.php';
?>
