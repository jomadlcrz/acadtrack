<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Terms of Academic Service & Grading Policy — College of Information Technology, Golden West Colleges, Inc.">
    <title><?= htmlspecialchars($pageTitle ?? 'Terms of Academic Service') ?> &mdash; College of Information Technology</title>
    <link rel="icon" type="image/x-icon" href="<?= url('/favicon.ico') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= asset('images/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= asset('images/favicon-16x16.png') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= asset('images/apple-touch-icon.png') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/layouts/home.css') ?>">
    <style>
        .legal-wrapper {
            max-width: 920px;
            margin: 40px auto 60px auto;
            padding: 0 16px;
        }
        .legal-document {
            background-color: #ffffff;
            border: 1px solid var(--gwc-border);
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
            padding: 48px 48px;
        }
        .legal-doc-header {
            border-bottom: 2px solid var(--gwc-navy);
            padding-bottom: 24px;
            margin-bottom: 32px;
        }
        .legal-meta-badge {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--gwc-navy);
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 4px 10px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 12px;
        }
        .legal-doc-title {
            font-size: 26px;
            font-weight: 700;
            color: var(--gwc-navy-dark);
            line-height: 1.25;
            margin-bottom: 8px;
            letter-spacing: -0.015em;
        }
        .legal-doc-subtitle {
            font-size: 14px;
            color: var(--gwc-text-secondary);
            margin-bottom: 18px;
        }
        .legal-preamble {
            border-left: 3px solid var(--gwc-navy);
            background-color: var(--gwc-slate-bg);
            padding: 16px 20px;
            border-radius: 0 4px 4px 0;
            margin-bottom: 28px;
            font-size: 14px;
            line-height: 1.6;
            color: var(--gwc-text-secondary);
        }
        .legal-section {
            margin-bottom: 32px;
        }
        .legal-section-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--gwc-navy);
            margin-bottom: 12px;
            letter-spacing: -0.01em;
            display: flex;
            align-items: baseline;
            gap: 8px;
            border-bottom: 1px solid var(--gwc-border-light);
            padding-bottom: 6px;
        }
        .legal-body-text {
            font-size: 13.5px;
            color: var(--gwc-text-secondary);
            line-height: 1.65;
            margin-bottom: 12px;
        }
        .legal-list {
            margin: 0 0 16px 0;
            padding-left: 20px;
            color: var(--gwc-text-secondary);
            font-size: 13.5px;
            line-height: 1.65;
        }
        .legal-list li {
            margin-bottom: 6px;
        }
        .legal-advisory-box {
            background-color: #fffbeb;
            border-left: 3px solid #d97706;
            padding: 16px 20px;
            border-radius: 0 4px 4px 0;
            font-size: 13.5px;
            line-height: 1.6;
            color: #78350f;
            margin: 18px 0;
        }
        .legal-advisory-box strong {
            color: #92400e;
        }
        .legal-attestation {
            border-top: 1px solid var(--gwc-border-light);
            padding-top: 24px;
            margin-top: 36px;
        }
        .legal-attestation-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--gwc-text-primary);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .legal-attestation-text {
            font-size: 12.5px;
            color: var(--gwc-text-muted);
            line-height: 1.5;
        }
        @media (max-width: 768px) {
            .legal-wrapper {
                margin: 16px auto 32px auto;
                padding: 0 12px;
            }
            .legal-document {
                padding: 24px 16px;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }
            .legal-doc-title {
                font-size: 20px;
                line-height: 1.3;
            }
            .legal-preamble,
            .legal-advisory-box {
                padding: 12px 14px;
                font-size: 13px;
            }
            .legal-section-title {
                font-size: 15px;
            }
        }
    </style>
</head>
<body class="landing-body">

    <!-- Institutional Navigation Header -->
    <header class="landing-nav">
        <div class="container">
            <a href="<?= url('/') ?>" class="landing-brand">
                <img src="<?= asset('images/cite.png') ?>" alt="College of Information Technology Logo" class="landing-logo">
                <div class="landing-brand-text">
                    <span class="landing-college-name">College of Information Technology</span>
                    <span class="landing-system-tag">Acadtrack &bull; Golden West Colleges, Inc.</span>
                </div>
            </a>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="legal-wrapper">
        <article class="legal-document">
            
            <!-- Document Header -->
            <div class="legal-doc-header">
                <span class="legal-meta-badge">Academic Governance</span>
                <h1 class="legal-doc-title">Terms of Academic Service &amp; Grading Policy</h1>
                <p class="legal-doc-subtitle">Institutional platform governance, evaluation integrity, and user accountability standards</p>
            </div>

            <!-- Preamble -->
            <div class="legal-preamble">
                The <strong>Acadtrack</strong> portal is the designated academic information and grading evaluation system of the <strong>College of Information Technology (CITE)</strong> at Golden West Colleges, Inc. By accessing this platform as a student, faculty member, Dean, or administrator, you acknowledge and agree to comply with the terms and institutional policies detailed herein.
            </div>

            <!-- Section 1: Institutional Governance -->
            <section class="legal-section">
                <h2 class="legal-section-title">1. Institutional Governance and Academic Authority</h2>
                <p class="legal-body-text">
                    Acadtrack provides automated grade computation, curriculum prerequisite evaluation, and academic record tracking for academic programs administered by CITE. The Dean of the College and authorized academic administrators retain supervisory authority over all grade approvals, section enrollments, and academic standing determinations.
                </p>
            </section>

            <!-- Section 2: Account Security -->
            <section class="legal-section">
                <h2 class="legal-section-title">2. User Accounts and Authentication Responsibility</h2>
                <p class="legal-body-text">
                    All user accounts are issued for authorized individual academic duties:
                </p>
                <ul class="legal-list">
                    <li><strong>Credential Confidentiality:</strong> Users are strictly prohibited from sharing login emails and passwords with peers or third parties.</li>
                    <li><strong>Mandatory First-Login Password Change:</strong> Any temporary credentials generated by administrators must be replaced with a secure password upon initial sign-in.</li>
                    <li><strong>Account Responsibility:</strong> All entries, grade submissions, and administrative actions originating from an authenticated session are the sole responsibility of the registered account holder.</li>
                </ul>
            </section>

            <!-- Section 3: Advisory Status of Online Grades -->
            <section class="legal-section">
                <h2 class="legal-section-title">3. Advisory Status of Online Grades</h2>
                <div class="legal-advisory-box">
                    <strong>Official Academic Records Policy:</strong> All scores, grading period evaluations, and student pass slips generated via the Acadtrack web portal are for <strong>internal academic advisement, semester progress monitoring, and student consultation purposes</strong>. The only legally binding and official academic record of Golden West Colleges, Inc. remains the signed and sealed <strong>Official Transcript of Records (OTR)</strong> and <strong>Certificate of Grades</strong> issued directly by the <strong>Office of the College Registrar</strong>.
                </div>
                <p class="legal-body-text">
                    Portal evaluations do not supersede official Registrar ledgers or institutional graduation clearances.
                </p>
            </section>

            <!-- Section 4: Grading System & Transmutation -->
            <section class="legal-section">
                <h2 class="legal-section-title">4. Grading Policy and Transmutation Standards</h2>
                <p class="legal-body-text">
                    In accordance with the CITE Academic Handbook and program syllabi:
                </p>
                <ul class="legal-list">
                    <li><strong>Periodic Assessment Breakdown:</strong> Semester evaluations are partitioned into Prelim (20%), Midterm (20%), Semi-Final (20%), and Final (40%) grading periods, unless configured differently for specific laboratory or capstone courses.</li>
                    <li><strong>Formula Standards:</strong> Instructors apply department-approved Zero-based or Transmuted 50-based grade calculation models to ensure consistent, fair, and mathematically accurate scores.</li>
                    <li><strong>Dean Verification Workflow:</strong> Submitted grade sheets undergo formal inspection by the College Dean before marks are approved and published to students.</li>
                </ul>
            </section>

            <!-- Section 5: Anti-Tampering & Ledgering -->
            <section class="legal-section">
                <h2 class="legal-section-title">5. Academic Integrity and Anti-Tampering Ledger</h2>
                <p class="legal-body-text">
                    Any unauthorized attempt to falsify, manipulate, or tamper with student records, assessment scores, or evaluation matrices is considered gross academic dishonesty and constitutes grounds for disciplinary sanctions under institutional bylaws and Philippine laws. To guarantee record authenticity, post-submission grade modifications are permanently recorded in an immutable audit ledger containing the author, previous score, revised score, timestamp, and justification.
                </p>
            </section>

            <!-- Section 6: Term Closure & Record Immutability -->
            <section class="legal-section">
                <h2 class="legal-section-title">6. Academic Term Closure and Record Immutability</h2>
                <p class="legal-body-text">
                    Upon official closure of an academic term by the Administrator or Dean's Office, all grading records, class rosters, and subject enrollments for that term become <strong>immutable</strong>. Reopening a closed term is strictly restricted and generates a persistent audit log event.
                </p>
            </section>

            <!-- Section 7: Policy Amendments -->
            <section class="legal-section">
                <h2 class="legal-section-title">7. Amendments to Academic Terms</h2>
                <p class="legal-body-text">
                    The College of Information Technology reserves the right to amend these Terms of Academic Service in response to institutional updates, CHED memoranda, or curriculum revisions. Continued use of Acadtrack constitutes affirmative acceptance of the prevailing policies.
                </p>
            </section>

            <!-- Institutional Attestation -->
            <div class="legal-attestation">
                <div class="legal-attestation-title">College of Information Technology (CITE) &mdash; Acadtrack</div>
                <div class="legal-attestation-text">
                    Golden West Colleges, Inc. &bull; San Jose Drive, Alaminos, Pangasinan
                </div>
            </div>

        </article>
    </main>

    <!-- Institutional Footer -->
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
</body>
</html>
