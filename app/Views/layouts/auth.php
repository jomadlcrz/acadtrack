<?php
$displayInfo = $info ?? null;
$displayError = $error ?? $_SESSION['_flash']['error'] ?? null;
$displaySuccess = $success ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Golden West Colleges, Inc. — Acadtrack">
    <title><?= htmlspecialchars($pageTitle ?? 'Sign In') ?> &mdash; Golden West Colleges, Inc.</title>
    <link rel="icon" type="image/x-icon" href="<?= url('/favicon.ico') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= url('/favicon.ico') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components/button.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components/input.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components/alert.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/layouts/auth.css') ?>">
</head>
<body class="auth-body-split">
    <div class="auth-split-wrapper">
        <!-- Left Panel: Institutional Identity -->
        <aside class="auth-panel-left d-none d-lg-flex">
            <div class="auth-left-content">
                <div class="auth-brand-lockup">
                    <a href="<?= url('/') ?>" class="auth-brand-link" title="Golden West Colleges">
                        <img src="<?= asset('images/gwc.png') ?>" alt="Golden West Colleges Seal" class="auth-brand-emblem">
                    </a>
                    <div class="auth-brand-text">
                        <span class="auth-brand-inst">Golden West Colleges, Inc.</span>
                        <span class="auth-brand-sys">Acadtrack</span>
                    </div>
                </div>

                <div class="auth-hero-statement">
                    <h1 class="auth-hero-title">School Grade Evaluation &amp; Academic Records</h1>
                    <p class="auth-hero-desc">
                        Official academic grading and student records portal for Golden West Colleges.
                    </p>
                </div>

                <div class="auth-left-footer">
                    <span class="auth-location">
                        <i class="bi bi-geo-alt me-1"></i> San Jose Drive, Alaminos, Pangasinan
                    </span>
                </div>
            </div>
        </aside>

        <!-- Right Panel: Clean Authentication Form -->
        <main class="auth-panel-right">
            <div class="auth-form-container">
                <div class="auth-form-card">
                    <!-- Mobile Institutional Brand (Visible only on < 992px) -->
                    <div class="auth-mobile-header-wrap d-flex d-lg-none align-items-center gap-2.5 mb-4 pb-3 border-bottom">
                        <a href="<?= url('/') ?>" class="auth-mobile-logo-link" title="Golden West Colleges">
                            <img src="<?= asset('images/gwc.png') ?>" alt="GWC Logo" class="auth-mobile-logo">
                        </a>
                        <div>
                            <span class="auth-mobile-inst">Golden West Colleges, Inc.</span>
                            <span class="auth-mobile-sys">Acadtrack</span>
                        </div>
                    </div>

                    <!-- Header Title -->
                    <div class="auth-form-header mb-4">
                        <h2 class="auth-form-title"><?= htmlspecialchars($authHeading ?? $pageTitle ?? 'Sign in') ?></h2>
                        <p class="auth-form-subtitle text-muted mb-0">
                            <?= htmlspecialchars($authSubheading ?? 'to continue to Acadtrack') ?>
                        </p>
                    </div>

                    <!-- Alerts -->
                    <?php if (!empty($displayInfo)): ?>
                        <div class="alert alert-info mb-3" role="alert">
                            <?= htmlspecialchars((string)$displayInfo) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($displayError)): ?>
                        <div class="alert alert-danger mb-3" role="alert">
                            <?= htmlspecialchars((string)$displayError) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($displaySuccess)): ?>
                        <div class="alert alert-success mb-3" role="alert">
                            <?= htmlspecialchars((string)$displaySuccess) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Form Content -->
                    <div class="auth-content-body">
                        <?= $content ?>
                    </div>
                </div>
            </div>

            <!-- Clean Footer -->
            <footer class="auth-right-footer text-center text-muted small">
                &copy; <?= date('Y') ?> Golden West Colleges, Inc. All rights reserved.
            </footer>
        </main>
    </div>

    <script src="<?= asset('vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
