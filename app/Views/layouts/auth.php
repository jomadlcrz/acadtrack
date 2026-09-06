<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Golden West Colleges, Inc. — Acadtrack">
    <title><?= htmlspecialchars($pageTitle ?? 'Log In') ?> &mdash; Golden West Colleges, Inc.</title>
    <link rel="icon" type="image/x-icon" href="<?= url('/favicon.ico') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= url('/favicon.ico') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/pages/auth.css') ?>">
</head>
<body class="auth-body">
    <main class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo-wrap">
                    <a href="<?= url('/') ?>" class="auth-logo-link" title="Return to Golden West Colleges Home">
                        <img src="<?= asset('images/gwc.png') ?>" alt="Golden West Colleges, Inc. Logo" class="auth-logo">
                    </a>
                </div>
                <h1 class="auth-title">Golden West Colleges, Inc.</h1>
                <p class="auth-subtitle">Acadtrack</p>
            </div>

            <?php if (!empty($info)): ?>
                <div class="alert alert-primary d-flex align-items-center gap-2 mb-3" role="alert" style="font-size: 0.9rem;">
                    <i class="bi bi-info-circle-fill flex-shrink-0" aria-hidden="true"></i>
                    <div><?= htmlspecialchars($info) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="auth-alert auth-alert-danger" role="alert">
                    <i class="bi bi-exclamation-circle-fill flex-shrink-0" aria-hidden="true"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="auth-alert auth-alert-success" role="alert">
                    <i class="bi bi-check-circle-fill flex-shrink-0" aria-hidden="true"></i>
                    <div><?= htmlspecialchars($success) ?></div>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </div>

        <!-- Institutional Footer -->
        <footer class="auth-footer">
            &copy; <?= date('Y') ?> Golden West Colleges, Inc. All rights reserved.
        </footer>
    </main>

    <script src="<?= asset('vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
