<?php
$pageTitle = 'Log In';
ob_start();
?>

<form method="POST" action="/login" class="auth-form" id="loginForm" novalidate>
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <div class="input-group">
            <span class="input-group-text" id="email-addon">
                <i class="bi bi-envelope" aria-hidden="true"></i>
            </span>
            <input 
                type="email" 
                id="email" 
                name="email" 
                class="form-control" 
                placeholder="name@gwc.edu" 
                required 
                autofocus 
                autocomplete="email"
                aria-describedby="email-addon"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            >
        </div>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text" id="password-addon">
                <i class="bi bi-lock" aria-hidden="true"></i>
            </span>
            <input 
                type="password" 
                id="password" 
                name="password" 
                class="form-control border-end-0" 
                placeholder="Enter your password" 
                required 
                autocomplete="current-password"
                aria-describedby="password-addon"
            >
            <button 
                type="button" 
                class="btn btn-toggle-pwd border-start-0" 
                id="togglePasswordBtn" 
                title="Show or hide password"
                aria-label="Toggle password visibility"
            >
                <i class="bi bi-eye" id="toggleIcon" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <button type="submit" class="btn-auth-submit" id="submitBtn">Log in</button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');

    if (toggleBtn && passwordInput && toggleIcon) {
        toggleBtn.addEventListener('click', function() {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            toggleIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
            passwordInput.focus();
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/auth.php';
?>
