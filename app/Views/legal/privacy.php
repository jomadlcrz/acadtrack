<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Data Privacy Notice — College of Information Technology, Golden West Colleges, Inc.">
    <title><?= htmlspecialchars($pageTitle ?? 'Data Privacy Notice') ?> &mdash; College of Information Technology</title>
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
        .legal-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 12px;
            padding: 14px 18px;
            background-color: var(--gwc-slate-bg);
            border: 1px solid var(--gwc-border-light);
            border-radius: 6px;
            font-size: 12.5px;
        }
        .legal-meta-item-label {
            font-weight: 600;
            color: var(--gwc-text-muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 2px;
        }
        .legal-meta-item-value {
            color: var(--gwc-text-primary);
            font-weight: 500;
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
        .legal-card-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin: 16px 0;
        }
        .legal-subcard {
            background-color: var(--gwc-slate-bg);
            border: 1px solid var(--gwc-border-light);
            border-radius: 6px;
            padding: 16px;
        }
        .legal-subcard-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--gwc-navy);
            margin-bottom: 6px;
        }
        .legal-subcard-text {
            font-size: 12.5px;
            color: var(--gwc-text-secondary);
            line-height: 1.5;
            margin: 0;
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
            .legal-meta-grid {
                grid-template-columns: 1fr;
                padding: 12px 14px;
                gap: 8px;
            }
            .legal-card-grid {
                grid-template-columns: 1fr;
            }
            .legal-preamble {
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
                    <span class="landing-system-tag">Acadtrack &bull; GWC</span>
                </div>
            </a>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="legal-wrapper">
        <article class="legal-document">
            
            <!-- Document Header -->
            <div class="legal-doc-header">
                <span class="legal-meta-badge">Statutory Compliance Notice</span>
                <h1 class="legal-doc-title">Institutional Data Privacy Notice</h1>
                <p class="legal-doc-subtitle">Under Republic Act No. 10173 (Data Privacy Act of 2012 of the Philippines)</p>

                <div class="legal-meta-grid">
                    <div>
                        <div class="legal-meta-item-label">Governing Academic Unit</div>
                        <div class="legal-meta-item-value">College of Information Technology (CITE)</div>
                    </div>
                    <div>
                        <div class="legal-meta-item-label">Statutory Reference</div>
                        <div class="legal-meta-item-value">Republic Act No. 10173 (IRR / NPC)</div>
                    </div>
                    <div>
                        <div class="legal-meta-item-label">Effective Term</div>
                        <div class="legal-meta-item-value">Academic Year 2026&ndash;2027</div>
                    </div>
                </div>
            </div>

            <!-- Statutory Commitment Preamble -->
            <div class="legal-preamble">
                The <strong>College of Information Technology (CITE)</strong> at Golden West Colleges, Inc. is committed to upholding the privacy rights of all students, faculty, and administrative personnel in strict adherence to the <strong>Data Privacy Act of 2012 (Republic Act No. 10173)</strong>, its Implementing Rules and Regulations, and relevant directives of the National Privacy Commission (NPC).
            </div>

            <!-- Section 1: Scope -->
            <section class="legal-section">
                <h2 class="legal-section-title">1. Scope and Applicability</h2>
                <p class="legal-body-text">
                    This Data Privacy Notice governs all personal data, academic performance metrics, student enrollment cohorts, instructor grade sheets, and administrative audit trails processed through the <strong>Acadtrack</strong> platform for curricula administered under CITE, primarily the Bachelor of Science in Information Technology (BSIT) program.
                </p>
            </section>

            <!-- Section 2: Categories of Data Collected -->
            <section class="legal-section">
                <h2 class="legal-section-title">2. Categories of Information Collected</h2>
                <p class="legal-body-text">
                    In fulfillment of academic evaluation and curriculum administration mandates, the platform systematically processes:
                </p>
                <ul class="legal-list">
                    <li><strong>Student Identification Data:</strong> Official institutional student number, full name, year level, academic cohort / section, and institutional contact details.</li>
                    <li><strong>Academic Assessment Marks:</strong> Component scores, coursework, laboratory exercises, periodic examinations (Prelim, Midterm, Semi-Final, Final), transmuted grades, and General Weighted Average (GWA).</li>
                    <li><strong>Faculty and Staff Records:</strong> Instructor full name, employee number, department assignment, and teaching load designations.</li>
                    <li><strong>Technical Audit Logs:</strong> System authentication timestamps, IP addresses recorded during sensitive grade submissions or modifications, and session telemetry.</li>
                </ul>
            </section>

            <!-- Section 3: Purpose of Processing -->
            <section class="legal-section">
                <h2 class="legal-section-title">3. Purpose of Processing</h2>
                <p class="legal-body-text">
                    Student and academic information collected through Acadtrack is utilized strictly for lawful institutional purposes:
                </p>
                <ul class="legal-list">
                    <li>Computing period-based marks according to CITE grading standards (zero-based or 50-based formulas).</li>
                    <li>Generating certified grading sheets for Dean review, administrative verification, and Registrar archiving.</li>
                    <li>Providing students with authorized real-time visibility into their academic progress and whole curriculum evaluations.</li>
                    <li>Facilitating student verification passes, prerequisite validations, and Dean's Honor Roll / academic distinction rankings.</li>
                </ul>
            </section>

            <!-- Section 4: Role-Based Access Controls -->
            <section class="legal-section">
                <h2 class="legal-section-title">4. Role-Based Access Controls (RBAC) and Safeguards</h2>
                <p class="legal-body-text">
                    To preserve confidentiality and maintain strict academic integrity, Acadtrack enforces structural role-based boundaries:
                </p>
                <div class="legal-card-grid">
                    <div class="legal-subcard">
                        <div class="legal-subcard-title">Student Portal Boundary</div>
                        <p class="legal-subcard-text">Students may only access their individual grades, evaluations, and pass slips. Cross-student or peer record inspection is strictly restricted.</p>
                    </div>
                    <div class="legal-subcard">
                        <div class="legal-subcard-title">Faculty Scope Boundary</div>
                        <p class="legal-subcard-text">Instructors are restricted to encoding scores only for classes and subjects officially assigned to them by the College Dean.</p>
                    </div>
                </div>
            </section>

            <!-- Section 5: Data Retention & Audit Trails -->
            <section class="legal-section">
                <h2 class="legal-section-title">5. Data Retention and Immutable Audit Trail</h2>
                <p class="legal-body-text">
                    Official student grades form part of the student's permanent academic record and are preserved in accordance with Commission on Higher Education (CHED) policies and institutional archiving standards. Grade alterations after initial submission are recorded in an append-only audit trail logging the exact timestamp, previous mark, revised mark, and author identity.
                </p>
            </section>

            <!-- Section 6: Rights of Data Subjects -->
            <section class="legal-section">
                <h2 class="legal-section-title">6. Rights of Data Subjects</h2>
                <p class="legal-body-text">
                    Under Section 16 of Republic Act No. 10173, students and academic personnel are entitled to:
                </p>
                <ul class="legal-list">
                    <li><strong>Right to be Informed:</strong> To know how academic data is collected, computed, transmuted, and reported.</li>
                    <li><strong>Right to Access:</strong> To view recorded periodic grades and historical curriculum evaluations through authenticated portal accounts.</li>
                    <li><strong>Right to Rectification:</strong> To request correction of erroneous grade entries during official grade consultation periods prior to academic term closure.</li>
                </ul>
            </section>

            <!-- Institutional Attestation -->
            <div class="legal-attestation">
                <div class="legal-attestation-title">Office of the Dean &mdash; College of Information Technology (CITE)</div>
                <div class="legal-attestation-text">
                    Golden West Colleges, Inc. &bull; San Jose Drive, Alaminos, Pangasinan<br>
                    Official Inquiries: <a href="mailto:goldenwestcollege94@gmail.com" class="text-decoration-none">goldenwestcollege94@gmail.com</a>
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
