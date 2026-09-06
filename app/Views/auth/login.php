<?php
$pageTitle = 'Login';
$subtitle = 'Sign in to your account';
ob_start();
?>

<form method="POST" action="/login" class="auth-form">
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autofocus placeholder="Enter your email">
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required placeholder="Enter your password">
    </div>

    <button type="submit" class="btn btn-primary btn-block">Sign In</button>
</form>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/auth.php';
?>
