<?php
$pageTitle = 'Forgot Password';
$subtitle = 'Reset your password';
ob_start();
?>

<form method="POST" action="<?= url('/forgot-password') ?>" class="auth-form" novalidate>
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" id="email" name="email" class="form-control" required placeholder="name@gwc.edu">
        </div>
        <div class="form-text">We'll send password recovery instructions to this address.</div>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2 d-inline-flex align-items-center justify-content-center gap-2">
        <i class="bi bi-send"></i> Send reset link
    </button>
    
    <div class="text-center mt-3">
        <a href="<?= url('/login') ?>" class="text-decoration-none small text-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Back to sign in
        </a>
    </div>
</form>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/auth.php';
?>
