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
    <meta name="description" content="College of Information Technology — Acadtrack">
    <title><?= htmlspecialchars($pageTitle ?? 'Sign In') ?> &mdash; College of Information Technology</title>
    <link rel="icon" type="image/x-icon" href="<?= url('/favicon.ico') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= asset('images/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= asset('images/favicon-16x16.png') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= asset('images/apple-touch-icon.png') ?>">
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
        <aside class="auth-panel-left d-none d-lg-flex" style="background-image: linear-gradient(160deg, rgba(8, 22, 46, 0.88) 0%, rgba(14, 36, 77, 0.82) 100%), url('<?= asset('images/gwc_bg.png') ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;">
            <div class="auth-left-content">
                <div class="auth-brand-lockup">
                    <a href="<?= url('/') ?>" class="auth-brand-link" title="College of Information Technology">
                        <img src="<?= asset('images/cite.png') ?>" alt="College of Information Technology Seal" class="auth-brand-emblem">
                    </a>
                    <div class="auth-brand-text">
                        <span class="auth-brand-inst">College of Information Technology</span>
                        <span class="auth-brand-sys">Acadtrack</span>
                    </div>
                </div>

                <div class="auth-hero-statement">
                    <h1 class="auth-hero-title">CITE Academic Evaluation &amp; Grade Records</h1>
                    <p class="auth-hero-desc">
                        Official academic grading and student curriculum evaluation portal for the College of Information Technology.
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
                        <a href="<?= url('/') ?>" class="auth-mobile-logo-link" title="College of Information Technology">
                            <img src="<?= asset('images/cite.png') ?>" alt="CITE Logo" class="auth-mobile-logo">
                        </a>
                        <div>
                            <span class="auth-mobile-inst">College of Information Technology</span>
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
                <div class="mb-1">
                    <a href="<?= url('/privacy') ?>" class="text-decoration-none text-muted me-2" style="font-size: 0.8rem;">Privacy Notice</a>
                    <span class="text-muted opacity-50">&bull;</span>
                    <a href="<?= url('/terms') ?>" class="text-decoration-none text-muted ms-2" style="font-size: 0.8rem;">Terms of Use</a>
                </div>
                <div>
                    &copy; <?= date('Y') ?> College of Information Technology &mdash; Golden West Colleges, Inc. All rights reserved.
                </div>
            </footer>
        </main>
    </div>

    <script src="<?= asset('vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
