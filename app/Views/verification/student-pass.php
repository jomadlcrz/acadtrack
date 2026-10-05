<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Academic Credential Verification — College of Information Technology, Golden West Colleges, Inc.">
    <title>Academic Credential Verification &mdash; College of Information Technology</title>
    <link rel="icon" type="image/x-icon" href="<?= url('/favicon.ico') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= asset('images/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= asset('images/favicon-16x16.png') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= asset('images/apple-touch-icon.png') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/layouts/home.css') ?>">
    <style>
        .verification-wrapper {
            max-width: 740px;
            margin: 40px auto 60px auto;
            padding: 0 16px;
        }
        .verification-document {
            background-color: #ffffff;
            border: 1px solid var(--gwc-border);
            border-radius: 6px;
            box-shadow: none;
            padding: 40px 40px;
        }
        @media (max-width: 576px) {
            .verification-wrapper {
                margin: 20px auto 40px auto;
            }
            .verification-document {
                padding: 24px 20px;
            }
        }
        .verification-header {
            border-bottom: 2px solid var(--gwc-navy);
            padding-bottom: 20px;
            margin-bottom: 28px;
        }
        .verification-meta-badge {
            font-size: 11px;
            font-weight: 600;
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
        .verification-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--gwc-navy-dark);
            line-height: 1.25;
            margin-bottom: 6px;
            letter-spacing: -0.015em;
        }
        .verification-subtitle {
            font-size: 14px;
            color: var(--gwc-text-secondary);
            margin-bottom: 0;
        }
        .verification-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid var(--gwc-border);
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 24px;
            background-color: #ffffff;
        }
        .verification-table tr:not(:last-child) th,
        .verification-table tr:not(:last-child) td {
            border-bottom: 1px solid var(--gwc-border-light);
        }
        .verification-table th {
            width: 32%;
            background-color: #f8fafc;
            color: var(--gwc-text-muted);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 16px;
            vertical-align: middle;
        }
        .verification-table td {
            color: var(--gwc-text-primary);
            font-size: 13px;
            padding: 12px 16px;
            vertical-align: middle;
        }
        .verification-note {
            font-size: 13px;
            color: var(--gwc-text-secondary);
            line-height: 1.6;
            margin-bottom: 24px;
            padding: 14px 18px;
            background-color: #f8fafc;
            border: 1px solid var(--gwc-border);
            border-radius: 6px;
        }
        .verification-action-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: gap-2;
            gap: 12px;
            padding-top: 20px;
            border-top: 1px solid var(--gwc-border);
        }
        .btn-portal-back {
            display: inline-flex;
            align-items: center;
            font-size: 13px;
            font-weight: 500;
            color: var(--gwc-text-secondary);
            background-color: #ffffff;
            border: 1px solid var(--gwc-border);
            border-radius: 6px;
            padding: 6px 14px;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
        }
        .btn-portal-back:hover {
            color: var(--gwc-navy-dark);
            background-color: #f8fafc;
            border-color: #94a3b8;
            text-decoration: none;
        }
    </style>
</head>
<body class="landing-body">

    <!-- Institutional Navigation Header (matches Home Page & Legal) -->
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

    <!-- Main Verification Document -->
    <main class="verification-wrapper">
        <article class="verification-document">
            <!-- Document Header -->
            <div class="verification-header">
                <span class="verification-meta-badge">Academic Verification</span>
                <h1 class="verification-title">Student Pass Verification</h1>
                <p class="verification-subtitle">Digital scholastic pass and enrollment status verification record</p>
            </div>

            <?php if ($isValid && !empty($passData)): ?>
                <!-- Valid Pass Record Table -->
                <div class="table-responsive mb-4">
                    <table class="verification-table">
                        <tbody>
                            <tr>
                                <th>Student Name</th>
                                <td class="fw-semibold text-dark"><?= htmlspecialchars($passData['name']) ?></td>
                            </tr>
                            <tr>
                                <th>Student ID</th>
                                <td class="font-monospace fw-semibold text-dark"><?= htmlspecialchars($passData['student_number']) ?></td>
                            </tr>
                            <tr>
                                <th>Course Subject</th>
                                <td class="text-dark"><?= htmlspecialchars($passData['subject_code']) ?> &mdash; <?= htmlspecialchars($passData['subject_title']) ?></td>
                            </tr>
                            <tr>
                                <th>Section &bull; Year Level</th>
                                <td class="text-dark">
                                    <?= htmlspecialchars($passData['set_name']) ?> &bull; <?= htmlspecialchars($passData['year_level']) ?> (<?= htmlspecialchars($passData['status']) ?>)
                                </td>
                            </tr>
                            <tr>
                                <th>Final Grade &amp; Result</th>
                                <td class="text-dark">
                                    <?php if ($passData['final_grade'] !== null): ?>
                                        <span class="font-monospace fw-semibold tabular-nums"><?= number_format($passData['final_grade'], 2) ?>%</span>
                                        &bull; <span><?= htmlspecialchars($passData['status_remark']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Incomplete</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Attendance Summary</th>
                                <td class="text-dark">
                                    <?= (int)($passData['attendance']['present_count'] ?? 0) ?> sessions present &bull; 
                                    <?= (int)($passData['attendance']['total_absences'] ?? 0) ?> recorded absences
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="verification-note">
                    This digital pass has been authenticated against the active scholastic records of the College of Information Technology (CITE), Golden West Colleges, Inc.
                </div>

            <?php else: ?>
                <!-- Unverified / Not Enrolled Table -->
                <div class="table-responsive mb-4">
                    <table class="verification-table">
                        <tbody>
                            <tr>
                                <th>Student Identifier</th>
                                <td class="font-monospace fw-semibold text-dark"><?= htmlspecialchars($idParam ?: 'Not Specified') ?></td>
                            </tr>
                            <?php if (!empty($studentName)): ?>
                            <tr>
                                <th>Student Name</th>
                                <td class="text-dark"><?= htmlspecialchars($studentName) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Course Subject</th>
                                <td class="text-dark">
                                    <?php if ($subject): ?>
                                        <?= htmlspecialchars($subject->subject_code ?: $subject->code) ?> &mdash; <?= htmlspecialchars($subject->descriptive_title ?: $subject->name) ?>
                                    <?php else: ?>
                                        Course Offering #<?= (int) ($subjectId ?? 0) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Academic Term</th>
                                <td class="text-dark">
                                    <?= htmlspecialchars(!empty($termName) ? $termName : ($academicTerm->name ?? ('Academic Term #' . (int) ($termId ?? 0)))) ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Verification Status</th>
                                <td class="text-danger fw-semibold">
                                    Unverified &mdash; Not Enrolled
                                </td>
                            </tr>
                            <tr>
                                <th>Audit Finding</th>
                                <td class="text-secondary" style="font-size: 13px; line-height: 1.5;">
                                    No active enrollment or official scholastic pass is on file for this student in the specified course offering and academic term.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="verification-note">
                    Official digital passes are issued and validated exclusively for students with authenticated course enrollments and recorded evaluations. If this requires administrative review, please coordinate with the College of Information Technology (CITE) Dean's Office or the College Registrar.
                </div>
            <?php endif; ?>

            <!-- Document Actions -->
            <div class="verification-action-bar">
                <a href="<?= url('/') ?>" class="btn-portal-back">
                    Return to AcadTrack Portal
                </a>
                <span class="text-muted small">
                    AcadTrack Verification System &bull; CITE
                </span>
            </div>
        </article>
    </main>

    <!-- Institutional Footer (matches Home Page & Legal) -->
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
