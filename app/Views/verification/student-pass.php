<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Credential Verification &mdash; Golden West Colleges, Inc.</title>
    <link rel="icon" type="image/x-icon" href="<?= url('favicon.ico') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components/attendance-pass.css') ?>">
    <style>
        body {
            background-color: #f8fafc;
            color: #0f172a;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
            margin: 0;
        }
        .verification-wrapper {
            max-width: 640px;
            width: 100%;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(14, 36, 77, 0.1), 0 8px 10px -6px rgba(14, 36, 77, 0.05);
            border: 1px solid #cbd5e1;
            overflow: hidden;
        }
        .verification-banner {
            background: linear-gradient(135deg, #08162e 0%, #0e244d 70%, #1a3a6c 100%);
            color: #ffffff;
            padding: 24px 28px;
            position: relative;
        }
        .verification-banner::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #c9a84c 0%, #ecd078 50%, #c9a84c 100%);
        }
        .verified-seal-circle {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>

<div class="verification-wrapper">
    <!-- Institutional Header Lockup -->
    <div class="verification-banner">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <img src="<?= asset('images/gwc.png') ?>" alt="Golden West Colleges Logo" style="width: 48px; height: 48px; object-fit: contain;">
                <div>
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #cbd5e1;">Golden West Colleges, Inc.</div>
                    <div style="font-size: 19px; font-weight: 700; color: #ffffff; letter-spacing: -0.01em;">Academic Credential Verification</div>
                </div>
            </div>
            <span class="badge py-1.5 px-3 rounded-pill bg-light text-dark fw-bold small">
                OFFICIAL RECORD
            </span>
        </div>
    </div>

    <div class="p-4 p-md-5 text-center">
        <?php if ($isValid && !empty($passData)): ?>
            <div class="verified-seal-circle bg-success-subtle text-success border border-success-subtle shadow-sm mx-auto">
                <i class="bi bi-patch-check-fill"></i>
            </div>
            <h2 class="h4 fw-bold text-dark mb-1">Authentic Academic Pass</h2>
            <p class="text-muted small mb-4">
                This academic pass has been cryptographically validated against Golden West Colleges records.
            </p>

            <div class="card bg-white border p-3.5 text-start mb-4" style="border-radius: 8px; border-color: #e2e8f0 !important;">
                <div class="row g-2 mb-2 pb-2 border-bottom">
                    <div class="col-sm-4 text-muted small fw-semibold text-uppercase" style="font-size: 11px;">Student Name</div>
                    <div class="col-sm-8 fw-bold text-dark"><?= htmlspecialchars($passData['name']) ?></div>
                </div>
                <div class="row g-2 mb-2 pb-2 border-bottom">
                    <div class="col-sm-4 text-muted small fw-semibold text-uppercase" style="font-size: 11px;">Student ID</div>
                    <div class="col-sm-8 font-monospace fw-semibold text-primary"><?= htmlspecialchars($passData['student_number']) ?></div>
                </div>
                <div class="row g-2 mb-2 pb-2 border-bottom">
                    <div class="col-sm-4 text-muted small fw-semibold text-uppercase" style="font-size: 11px;">Course Subject</div>
                    <div class="col-sm-8 fw-semibold text-dark"><?= htmlspecialchars($passData['subject_code'] . ' &mdash; ' . $passData['subject_title']) ?></div>
                </div>
                <div class="row g-2 mb-2 pb-2 border-bottom">
                    <div class="col-sm-4 text-muted small fw-semibold text-uppercase" style="font-size: 11px;">Section / Standing</div>
                    <div class="col-sm-8 text-dark">
                        <?= htmlspecialchars($passData['set_name']) ?> &bull; <?= htmlspecialchars($passData['year_level']) ?> 
                        <span class="badge bg-light text-secondary border ms-1 font-monospace"><?= htmlspecialchars($passData['status']) ?></span>
                    </div>
                </div>
                <div class="row g-2 mb-2 pb-2 border-bottom">
                    <div class="col-sm-4 text-muted small fw-semibold text-uppercase" style="font-size: 11px;">Final Grade &amp; Result</div>
                    <div class="col-sm-8 d-flex align-items-center gap-2">
                        <?php if ($passData['final_grade'] !== null): ?>
                            <span class="fs-5 fw-bold font-monospace text-primary tabular-nums mb-0"><?= number_format($passData['final_grade'], 2) ?>%</span>
                            <span class="badge <?= $passData['final_grade'] >= 75.0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' ?> px-2.5 py-1 fw-bold">
                                <?= htmlspecialchars($passData['status_remark']) ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1">INCOMPLETE</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-sm-4 text-muted small fw-semibold text-uppercase" style="font-size: 11px;">Attendance Summary</div>
                    <div class="col-sm-8 text-secondary small">
                        <?= (int)($passData['attendance']['present_count'] ?? 0) ?> sessions present &bull; 
                        <?= (int)($passData['attendance']['total_absences'] ?? 0) ?> recorded absences
                        <?php if (!empty($passData['attendance']['has_warning'])): ?>
                            <span class="badge bg-danger text-white ms-1 py-0.5 px-2">5+ Absences Warning</span>
                        <?php else: ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle ms-1 py-0.5 px-2">Cleared</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="text-muted d-flex align-items-center justify-content-center gap-1.5" style="font-size: 11px;">
                <i class="bi bi-clock-history"></i>
                <span>Cryptographically verified on <?= date('F j, Y \a\t g:i A') ?></span>
            </div>

        <?php else: ?>
            <div class="verified-seal-circle bg-danger-subtle text-danger border border-danger-subtle shadow-sm mx-auto">
                <i class="bi bi-shield-x"></i>
            </div>
            <h2 class="h4 fw-bold text-dark mb-1">Credential Not Found</h2>
            <p class="text-muted small mb-4">
                The requested digital student pass could not be verified or the verification token is invalid.
            </p>
            <div class="alert alert-warning text-start small mb-4" role="alert">
                <i class="bi bi-exclamation-circle-fill me-1"></i>
                Please contact the Office of the Registrar or your course instructor to verify enrollment.
            </div>
        <?php endif; ?>

        <div class="mt-4 pt-3 border-top">
            <a href="<?= url('/') ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5">
                <i class="bi bi-arrow-left"></i> Return to AcadTrack Portal
            </a>
        </div>
    </div>
</div>

</body>
</html>
