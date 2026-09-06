<?php
$pageTitle = 'Forgot Password';
$subtitle = 'Reset your password';
ob_start();
?>

<form method="POST" action="/forgot-password" class="auth-form">
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required placeholder="Enter your email">
    </div>

    <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
</form>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/auth.php';
?>
