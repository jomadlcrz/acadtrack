<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Golden West Colleges, Inc. — Acadtrack">
    <title>Acadtrack &mdash; Golden West Colleges, Inc.</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="stylesheet" href="/assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/pages/home.css">
</head>
<body class="landing-body">

    <!-- 1. Institutional Navigation Header -->
    <header class="landing-nav">
        <div class="container">
            <a href="/" class="landing-brand">
                <img src="/assets/images/gwc.png" alt="Golden West Colleges Logo" class="landing-logo">
                <div class="landing-brand-text">
                    <span class="landing-college-name">Golden West Colleges, Inc.</span>
                    <span class="landing-system-tag">Acadtrack</span>
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
                        A centralized, role-governed academic management platform engineered for Golden West Colleges, Inc. 
                        Streamlines course assignments, student rosters, period-based grading sheets, Dean audit reviews, and curriculum evaluation metrics.
                    </p>
                    <div class="hero-actions">
                        <a href="/login" class="btn-hero-primary">
                            <span>Log In to Portal</span>
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
                            Grades lock into permanent academic records. Students log in to view their Whole Evaluation.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Institutional Footer -->
    <footer class="landing-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="footer-brand">
                        <img src="/assets/images/gwc.png" alt="GWC Logo" class="footer-logo">
                        <h3 class="footer-title">Golden West Colleges, Inc.</h3>
                    </div>
                    <p class="footer-desc">
                        Acadtrack &mdash; Providing dependable, transparent, and accurate grade computation and curriculum tracking for the academic community of Golden West Colleges, Inc.
                    </p>
                </div>
                <div class="col-6 col-lg-3 offset-lg-1">
                    <h4 class="footer-heading">Portals</h4>
                    <ul class="footer-links">
                        <li><a href="/login"><i class="bi bi-chevron-right me-1"></i> Dean Review Portal</a></li>
                        <li><a href="/login"><i class="bi bi-chevron-right me-1"></i> Faculty Grading Portal</a></li>
                        <li><a href="/login"><i class="bi bi-chevron-right me-1"></i> Student Grade Inquiry</a></li>
                        <li><a href="/login"><i class="bi bi-chevron-right me-1"></i> System Administration</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-3">
                    <h4 class="footer-heading">Academic System</h4>
                    <ul class="footer-links">
                        <li><a href="#lifecycle"><i class="bi bi-chevron-right me-1"></i> Workflow Lifecycle</a></li>
                        <li><a href="/login"><i class="bi bi-chevron-right me-1"></i> Log In to Portal</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <div>
                    &copy; <?= date('Y') ?> Golden West Colleges, Inc. All rights reserved.
                </div>
            </div>
        </div>
    </footer>

    <script src="/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>
