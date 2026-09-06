<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Golden West Colleges, Inc. — Official Academic Grading &amp; Curriculum Evaluation System">
    <title>Academic Grading &amp; Evaluation Platform &mdash; Golden West Colleges, Inc.</title>
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
                    <span class="landing-system-tag">Academic Grading &amp; Evaluation System</span>
                </div>
            </a>

            <nav aria-label="Main Navigation">
                <ul class="landing-nav-links">
                    <li><a href="#roles" class="landing-nav-link">Portals &amp; Roles</a></li>
                    <li><a href="#grading-standards" class="landing-nav-link">Grading Standards</a></li>
                    <li><a href="#lifecycle" class="landing-nav-link">Workflow Lifecycle</a></li>
                    <li>
                        <a href="/login" class="btn-nav-login">
                            <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                            <span>Log In</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- 2. Institutional Gateway Hero -->
    <section class="landing-hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="hero-pill">
                        <i class="bi bi-shield-check" aria-hidden="true"></i>
                        <span>Official Academic Management Gateway</span>
                    </div>
                    <h1 class="hero-title">Academic Grading &amp; Curriculum Evaluation Platform</h1>
                    <p class="hero-lead">
                        A centralized, role-governed academic management platform engineered for Golden West Colleges, Inc. 
                        Streamlines course assignments, student rosters, period-based grading sheets, Dean audit reviews, and curriculum evaluation metrics.
                    </p>
                    <div class="hero-actions">
                        <a href="/login" class="btn-hero-primary">
                            <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                            <span>Access Portal (Log In)</span>
                        </a>
                        <a href="#roles" class="btn-hero-outline">
                            <i class="bi bi-person-lines-fill" aria-hidden="true"></i>
                            <span>Explore Stakeholder Roles</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. KPI & Institutional Metric Strip -->
    <section class="landing-metrics">
        <div class="container">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="metric-item">
                        <div class="metric-icon" aria-hidden="true">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <div class="metric-value">4 Roles</div>
                            <div class="metric-label">Dean, Faculty, Student, Admin</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="metric-item">
                        <div class="metric-icon" aria-hidden="true">
                            <i class="bi bi-calculator-fill"></i>
                        </div>
                        <div>
                            <div class="metric-value">4 Periods</div>
                            <div class="metric-label">Prelim, Midterm, Semi, Final</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="metric-item">
                        <div class="metric-icon" aria-hidden="true">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <div>
                            <div class="metric-value">5 States</div>
                            <div class="metric-label">Draft to Certified Finalized</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="metric-item">
                        <div class="metric-icon" aria-hidden="true">
                            <i class="bi bi-award-fill"></i>
                        </div>
                        <div>
                            <div class="metric-value">1.00 &ndash; 5.00</div>
                            <div class="metric-label">GWC Official Grade Scale</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Stakeholder Portals (Role Grid) -->
    <section id="roles" class="landing-section">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Role-Governed Architecture</span>
                <h2 class="section-title">Dedicated Portals for Every Academic Stakeholder</h2>
                <p class="section-lead">
                    Every user accesses a role-tailored environment designed for their exact academic and administrative responsibilities.
                </p>
            </div>

            <div class="row g-4">
                <!-- Dean Portal -->
                <div class="col-md-6 col-lg-3">
                    <div class="role-card">
                        <div>
                            <div class="role-card-header">
                                <div class="role-icon-box" aria-hidden="true">
                                    <i class="bi bi-mortarboard-fill"></i>
                                </div>
                                <div>
                                    <h3 class="role-title">College Dean</h3>
                                    <span class="badge bg-warning text-dark role-badge">Academic Audit</span>
                                </div>
                            </div>
                            <p class="role-description">
                                Oversees curriculum subjects, assigns qualified faculty to course sections, audits grade distributions, and certifies submissions.
                            </p>
                            <ul class="role-features">
                                <li><i class="bi bi-check2"></i> Assign faculty to subjects</li>
                                <li><i class="bi bi-check2"></i> Review grade distributions</li>
                                <li><i class="bi bi-check2"></i> Approve or return sheets with remarks</li>
                            </ul>
                        </div>
                        <a href="/login" class="btn-role-action">
                            <span>Dean Portal</span>
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>

                <!-- Faculty Portal -->
                <div class="col-md-6 col-lg-3">
                    <div class="role-card">
                        <div>
                            <div class="role-card-header">
                                <div class="role-icon-box" aria-hidden="true">
                                    <i class="bi bi-person-video3"></i>
                                </div>
                                <div>
                                    <h3 class="role-title">Faculty Member</h3>
                                    <span class="badge bg-success text-white role-badge">Grade Entry</span>
                                </div>
                            </div>
                            <p class="role-description">
                                Manages enrolled student class rosters, encodes period scores, autosaves working drafts, and submits final sheets for Dean review.
                            </p>
                            <ul class="role-features">
                                <li><i class="bi bi-check2"></i> High-density grade encoding sheet</li>
                                <li><i class="bi bi-check2"></i> Real-time equivalent calculation</li>
                                <li><i class="bi bi-check2"></i> Safe draft save &amp; submission locks</li>
                            </ul>
                        </div>
                        <a href="/login" class="btn-role-action">
                            <span>Faculty Portal</span>
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>

                <!-- Student Portal -->
                <div class="col-md-6 col-lg-3">
                    <div class="role-card">
                        <div>
                            <div class="role-card-header">
                                <div class="role-icon-box" aria-hidden="true">
                                    <i class="bi bi-backpack-fill"></i>
                                </div>
                                <div>
                                    <h3 class="role-title">Student</h3>
                                    <span class="badge bg-primary text-white role-badge">Academic Records</span>
                                </div>
                            </div>
                            <p class="role-description">
                                Views certified semester grades, tracks overall Grade Point Average (GPA), and monitors academic evaluation standings.
                            </p>
                            <ul class="role-features">
                                <li><i class="bi bi-check2"></i> Period-by-period score transparency</li>
                                <li><i class="bi bi-check2"></i> Cumulative weighted GPA tracking</li>
                                <li><i class="bi bi-check2"></i> Official performance remarks</li>
                            </ul>
                        </div>
                        <a href="/login" class="btn-role-action">
                            <span>Student Portal</span>
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>

                <!-- Administrator Portal -->
                <div class="col-md-6 col-lg-3">
                    <div class="role-card">
                        <div>
                            <div class="role-card-header">
                                <div class="role-icon-box" aria-hidden="true">
                                    <i class="bi bi-sliders"></i>
                                </div>
                                <div>
                                    <h3 class="role-title">Administrator</h3>
                                    <span class="badge bg-dark text-white role-badge">Governance</span>
                                </div>
                            </div>
                            <p class="role-description">
                                Manages academic term activations, provisions user credentials, configures grading scales, and ensures database integrity.
                            </p>
                            <ul class="role-features">
                                <li><i class="bi bi-check2"></i> Academic term &amp; year activation</li>
                                <li><i class="bi bi-check2"></i> User account &amp; role administration</li>
                                <li><i class="bi bi-check2"></i> Grading baseline configuration</li>
                            </ul>
                        </div>
                        <a href="/login" class="btn-role-action">
                            <span>Admin Portal</span>
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. Academic Grading Standards & Scale -->
    <section id="grading-standards" class="landing-section bg-white border-top border-bottom">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">GWC Evaluative Framework</span>
                <h2 class="section-title">Official Grading Scale &amp; Period Weights</h2>
                <p class="section-lead">
                    Structured in full alignment with Golden West Colleges, Inc. academic policies.
                </p>
            </div>

            <!-- Period Weights -->
            <div class="row g-3 mb-5">
                <div class="col-6 col-md-3">
                    <div class="weight-card">
                        <div class="weight-period">Prelim Period</div>
                        <div class="weight-percent">20%</div>
                        <p class="weight-desc">Preliminary assessments &amp; exams</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="weight-card">
                        <div class="weight-period">Midterm Period</div>
                        <div class="weight-percent">20%</div>
                        <p class="weight-desc">Mid-semester assessments &amp; exams</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="weight-card">
                        <div class="weight-period">Semi-Final Period</div>
                        <div class="weight-percent">20%</div>
                        <p class="weight-desc">Pre-final assessments &amp; coursework</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="weight-card">
                        <div class="weight-period">Final Period</div>
                        <div class="weight-percent">40%</div>
                        <p class="weight-desc">Comprehensive final term evaluation</p>
                    </div>
                </div>
            </div>

            <!-- Evaluation Scale Table -->
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="table-scale-wrap">
                        <table class="table-scale table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 28%;">Percentage Range</th>
                                    <th style="width: 22%;">Equivalent Grade</th>
                                    <th style="width: 25%;">Academic Performance</th>
                                    <th style="width: 25%;">Official Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>90.00% &ndash; 100.00%</strong></td>
                                    <td><code>1.00 &ndash; 1.25</code></td>
                                    <td><span class="text-success fw-bold">Excellent</span></td>
                                    <td><span class="badge badge-approved">Passed (Honor)</span></td>
                                </tr>
                                <tr>
                                    <td><strong>80.00% &ndash; 89.99%</strong></td>
                                    <td><code>1.50 &ndash; 1.75</code></td>
                                    <td><span class="text-success fw-semibold">Very Good</span></td>
                                    <td><span class="badge badge-approved">Passed</span></td>
                                </tr>
                                <tr>
                                    <td><strong>70.00% &ndash; 79.99%</strong></td>
                                    <td><code>2.00 &ndash; 2.25</code></td>
                                    <td><span class="text-primary fw-semibold">Good</span></td>
                                    <td><span class="badge badge-approved">Passed</span></td>
                                </tr>
                                <tr>
                                    <td><strong>60.00% &ndash; 69.99%</strong></td>
                                    <td><code>2.50 &ndash; 2.75</code></td>
                                    <td><span class="text-secondary fw-semibold">Satisfactory</span></td>
                                    <td><span class="badge badge-submitted">Passed</span></td>
                                </tr>
                                <tr>
                                    <td><strong>50.00% &ndash; 59.99%</strong></td>
                                    <td><code>3.00</code></td>
                                    <td><span class="text-warning fw-semibold">Needs Improvement</span></td>
                                    <td><span class="badge badge-under-review">Conditional Pass</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Below 50.00%</strong></td>
                                    <td><code>5.00</code></td>
                                    <td><span class="text-danger fw-bold">Failing</span></td>
                                    <td><span class="badge badge-returned">Failed</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. Grading Sheet Lifecycle Workflow -->
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
                            Faculty member encodes student scores. Drafts can be saved repeatedly without locking the sheet.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 col-lg">
                    <div class="workflow-step-card">
                        <div class="workflow-step-num">2</div>
                        <h4 class="workflow-step-title">SUBMITTED</h4>
                        <p class="workflow-step-desc">
                            Faculty submits completed sheet for Dean review. Input fields lock to prevent accidental modification.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 col-lg">
                    <div class="workflow-step-card">
                        <div class="workflow-step-num">3</div>
                        <h4 class="workflow-step-title">UNDER REVIEW</h4>
                        <p class="workflow-step-desc">
                            Dean inspects the grade distribution, historical averages, and ensures evaluative fairness.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 col-lg">
                    <div class="workflow-step-card">
                        <div class="workflow-step-num">4</div>
                        <h4 class="workflow-step-title">APPROVED / RETURNED</h4>
                        <p class="workflow-step-desc">
                            Dean approves sheet to release grades, or returns it with mandatory feedback remarks for instructor revision.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 col-lg">
                    <div class="workflow-step-card">
                        <div class="workflow-step-num">5</div>
                        <h4 class="workflow-step-title">FINALIZED</h4>
                        <p class="workflow-step-desc">
                            Registrar locks the term. Approved grades become immutable historical records in the GWC academic database.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. Institutional Footer -->
    <footer class="landing-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="footer-brand">
                        <img src="/assets/images/gwc.png" alt="GWC Logo" class="footer-logo">
                        <h3 class="footer-title">Golden West Colleges, Inc.</h3>
                    </div>
                    <p class="footer-desc">
                        Academic Grading &amp; Evaluation Platform &mdash; Providing dependable, transparent, and accurate grade computation and curriculum tracking for the academic community of Golden West Colleges, Inc.
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
                        <li><a href="#grading-standards"><i class="bi bi-chevron-right me-1"></i> Grading Period Weights</a></li>
                        <li><a href="#grading-standards"><i class="bi bi-chevron-right me-1"></i> Evaluation Scale</a></li>
                        <li><a href="#lifecycle"><i class="bi bi-chevron-right me-1"></i> Lifecycle State Machine</a></li>
                        <li><a href="/login"><i class="bi bi-chevron-right me-1"></i> Sign In to Portal</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <div>
                    &copy; <?= date('Y') ?> Golden West Colleges, Inc. All rights reserved.
                </div>
                <div>
                    Academic Management System &bull; Version 1.0 (Vanilla PHP 8.2 MVC)
                </div>
            </div>
        </div>
    </footer>

    <script src="/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>
