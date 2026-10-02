<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="College of Information Technology — Acadtrack">
    <title>Acadtrack &mdash; College of Information Technology</title>
    <link rel="icon" type="image/x-icon" href="<?= url('/favicon.ico') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= asset('images/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= asset('images/favicon-16x16.png') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= asset('images/apple-touch-icon.png') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/layouts/home.css') ?>">
</head>
<body class="landing-body">

    <!-- 1. Institutional Navigation Header -->
    <header class="landing-nav">
        <div class="container">
            <a href="<?= url('/') ?>" class="landing-brand">
                <img src="<?= asset('images/cite.png') ?>" alt="College of Information Technology Logo" class="landing-logo">
                <div class="landing-brand-text">
                    <span class="landing-college-name">College of Information Technology</span>
                    <span class="landing-system-tag">Acadtrack &bull; GWC</span>
                </div>
            </a>
        </div>
    </header>

    <!-- 2. Institutional Gateway Hero -->
    <section class="landing-hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="hero-title">Academic Grading &amp; Curriculum Evaluation Platform</h1>
                    <p class="hero-lead">
                        A centralized, role-governed academic management platform engineered for the College of Information Technology (CITE). 
                        Streamlines course assignments, student rosters, period-based grading sheets, Dean audit reviews, and curriculum evaluation metrics.
                    </p>
                    <div class="hero-actions">
                        <a href="<?= url('/login') ?>" class="btn-hero-primary">
                            <span>Sign In to Portal</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. Grading Sheet Lifecycle Workflow -->
    <section id="lifecycle" class="landing-section">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Quality Assurance</span>
                <h2 class="section-title">Grading Sheet Lifecycle &amp; Security State Machine</h2>
                <p class="section-lead">
                    Grade submissions follow a strict five-stage operational workflow to ensure evaluative accuracy and audit compliance.
                </p>
            </div>

            <div class="row g-3">
                <div class="col-md-4 col-lg">
                    <div class="workflow-step-card">
                        <div class="workflow-step-num">1</div>
                        <h4 class="workflow-step-title">DRAFT</h4>
                        <p class="workflow-step-desc">
                            Faculty selects semester, sets Zero/50-based grading, manages rosters, and encodes draft marks.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 col-lg">
                    <div class="workflow-step-card">
                        <div class="workflow-step-num">2</div>
                        <h4 class="workflow-step-title">SUBMITTED</h4>
                        <p class="workflow-step-desc">
                            Faculty verifies computed grade equivalents and submits the sheet. Score inputs lock immediately.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 col-lg">
                    <div class="workflow-step-card">
                        <div class="workflow-step-num">3</div>
                        <h4 class="workflow-step-title">UNDER REVIEW</h4>
                        <p class="workflow-step-desc">
                            Dean and Admin inspect grade distributions, with permissions to edit or return with remarks.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 col-lg">
                    <div class="workflow-step-card">
                        <div class="workflow-step-num">4</div>
                        <h4 class="workflow-step-title">APPROVED &amp; NOTIFIED</h4>
                        <p class="workflow-step-desc">
                            Dean or Admin approves and prints sheets. Automated emails alert students of grade availability.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 col-lg">
                    <div class="workflow-step-card">
                        <div class="workflow-step-num">5</div>
                        <h4 class="workflow-step-title">FINALIZED</h4>
                        <p class="workflow-step-desc">
                            Grades lock into permanent academic records. Students sign in to view their Whole Evaluation.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Institutional Footer -->
    <footer class="landing-footer">
        <div class="container">
            <div class="footer-brand">
                <img src="<?= asset('images/cite.png') ?>" alt="CITE Logo" class="footer-logo">
                <h3 class="footer-title">College of Information Technology</h3>
            </div>
            <p class="footer-desc">
                Acadtrack &mdash; Providing dependable, transparent, and accurate grade computation and curriculum tracking for the College of Information Technology.
            </p>

            <div class="footer-bottom">
                <div>
                    <div class="mb-1">
                        <a href="<?= url('/privacy') ?>" class="text-white text-decoration-none me-3 opacity-75 hover-opacity-100">Privacy Notice</a>
                        <span class="text-white-50">&bull;</span>
                        <a href="<?= url('/terms') ?>" class="text-white text-decoration-none ms-3 opacity-75 hover-opacity-100">Terms of Academic Service</a>
                    </div>
                    <div>
                        &copy; <?= date('Y') ?> College of Information Technology &mdash; Golden West Colleges, Inc. All rights reserved.
                    </div>
                </div>
                <div>
                    San Jose Drive, Alaminos, Pangasinan
                </div>
            </div>
        </div>
    </footer>

    <script src="<?= asset('vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
