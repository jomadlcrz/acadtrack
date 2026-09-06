<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Grade Sheet - <?= htmlspecialchars($sheet['subject_code']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8fafc;
            color: #1e293b;
            font-size: 12px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .print-container {
            max-width: 960px;
            margin: 20px auto;
            background: #ffffff;
            padding: 35px 45px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 4px;
        }
        .institution-header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .institution-title {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
        }
        .institution-sub {
            font-size: 12px;
            color: #475569;
            margin: 2px 0 0 0;
        }
        .report-title {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 8px;
            color: #1e3a8a;
        }
        .table-print {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .table-print th, .table-print td {
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
        }
        .table-print th {
            background-color: #f1f5f9 !important;
            font-weight: 600;
            color: #334155;
            text-transform: uppercase;
            font-size: 10px;
        }
        .signature-block {
            margin-top: 45px;
            page-break-inside: avoid;
        }
        .signature-line {
            border-top: 1px solid #0f172a;
            margin-top: 40px;
            padding-top: 4px;
            font-weight: 600;
            font-size: 11px;
        }
        @media print {
            body {
                background: #ffffff !important;
                font-size: 10.5pt;
            }
            .print-container {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                margin: 15mm;
                size: portrait;
            }
        }
    </style>
</head>
<body>

<div class="no-print bg-white border-bottom py-2 sticky-top shadow-sm">
    <div class="container-fluid max-w-960 d-flex justify-content-between align-items-center" style="max-width: 960px;">
        <span class="text-muted small">
            <i class="bi bi-printer me-1"></i> Official Document Preview: <strong><?= htmlspecialchars($sheet['subject_code']) ?></strong>
        </span>
        <div class="d-flex gap-2">
            <button type="button" onclick="window.close()" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-x-lg"></i> Close
            </button>
            <button type="button" onclick="window.print()" class="btn btn-sm btn-primary">
                <i class="bi bi-printer-fill me-1"></i> Print document
            </button>
        </div>
    </div>
</div>

<div class="print-container">
    <!-- Header -->
    <div class="institution-header">
        <h1 class="institution-title">Golden West Colleges, Inc.</h1>
        <p class="institution-sub">San Fernando City, La Union, Philippines</p>
        <p class="institution-sub">Office of the Academic Dean &bull; Office of the Registrar</p>
        <div class="report-title">Official Grading Sheet &amp; Class Roster</div>
    </div>

    <!-- Metadata Section -->
    <div class="row g-2 mb-3 small">
        <div class="col-7">
            <table class="w-100">
                <tr>
                    <td style="width: 130px;" class="text-muted">Course / Subject:</td>
                    <td class="fw-semibold font-monospace"><?= htmlspecialchars($sheet['subject_code']) ?> &mdash; <?= htmlspecialchars($sheet['subject_name']) ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Nature / Type:</td>
                    <td><?= htmlspecialchars($sheet['subject_nature'] ?? 'Lecture') ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Instructor:</td>
                    <td class="fw-semibold"><?= htmlspecialchars($sheet['faculty_first_name'] . ' ' . $sheet['faculty_last_name']) ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Year Level:</td>
                    <td><?= htmlspecialchars((string)$sheet['year_level']) ?><?= match((int)$sheet['year_level']) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' } ?> Year</td>
                </tr>
            </table>
        </div>
        <div class="col-5">
            <table class="w-100">
                <tr>
                    <td style="width: 120px;" class="text-muted">Academic Term:</td>
                    <td><?= htmlspecialchars($sheet['academic_year_name'] ?? '2024-2025') ?> &bull; <?= (string)($sheet['semester'] ?? '1') === '2' ? '2nd Semester' : '1st Semester' ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Grading Method:</td>
                    <td><?= ($setting['grading_method'] ?? 'zero_based') === 'fifty_based' ? '50-Based' : 'Zero-Based' ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Grading Weights:</td>
                    <td>P: <?= (int)$weights['prelim'] ?>% | M: <?= (int)$weights['midterm'] ?>% | SF: <?= (int)$weights['semi_final'] ?>% | F: <?= (int)$weights['final'] ?>%</td>
                </tr>
                <tr>
                    <td class="text-muted">Sheet Status:</td>
                    <td class="fw-semibold"><?= htmlspecialchars($sheet['status']) ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Grades Table -->
    <table class="table-print mb-4">
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">#</th>
                <th style="width: 110px;">Student ID</th>
                <th>Student Full Name</th>
                <th style="width: 75px;" class="text-center">Status</th>
                <?php foreach ($periods as $period): ?>
                    <th style="width: 70px;" class="text-end"><?= htmlspecialchars($period['name']) ?></th>
                <?php endforeach; ?>
                <th style="width: 80px;" class="text-end">Final rating</th>
                <th style="width: 75px;" class="text-center">Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($students)): ?>
                <tr>
                    <td colspan="<?= 6 + count($periods) ?>" class="text-center py-3 text-muted">No student records enrolled.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($students as $idx => $student): ?>
                <?php
                    $studentId = (int) $student['id'];
                    $studentPeriodGrades = $gradeMatrix[$studentId] ?? [];
                    
                    // Compute final grade
                    $weightedSum = 0;
                    $totalWeightUsed = 0;
                    $hasAnyGrade = false;

                    foreach ($periods as $period) {
                        $pId = (int) $period['id'];
                        if (isset($studentPeriodGrades[$pId])) {
                            $hasAnyGrade = true;
                            $pName = strtolower($period['name']);
                            $w = 20.0;
                            if (str_contains($pName, 'prelim')) {
                                $w = $weights['prelim'];
                            } elseif (str_contains($pName, 'midterm')) {
                                $w = $weights['midterm'];
                            } elseif (str_contains($pName, 'semi')) {
                                $w = $weights['semi_final'];
                            } elseif (str_contains($pName, 'final')) {
                                $w = $weights['final'];
                            }
                            $weightedSum += $studentPeriodGrades[$pId] * $w;
                            $totalWeightUsed += $w;
                        }
                    }

                    $finalRating = ($totalWeightUsed > 0) ? round($weightedSum / $totalWeightUsed, 2) : 0.0;
                    $isPassed = $hasAnyGrade && ($finalRating >= 75.0);
                ?>
                <tr>
                    <td class="text-center"><?= $idx + 1 ?></td>
                    <td class="font-monospace fw-semibold"><?= htmlspecialchars($student['student_number'] ?? 'N/A') ?></td>
                    <td class="fw-semibold"><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($student['status'] ?? 'Regular') ?></td>
                    <?php foreach ($periods as $period): ?>
                        <?php $pGrade = $studentPeriodGrades[(int)$period['id']] ?? null; ?>
                        <td class="text-end font-monospace">
                            <?= $pGrade !== null ? number_format((float)$pGrade, 2) : '—' ?>
                        </td>
                    <?php endforeach; ?>
                    <td class="text-end font-monospace fw-bold">
                        <?= $hasAnyGrade ? number_format($finalRating, 2) : '—' ?>
                    </td>
                    <td class="text-center fw-semibold <?= $hasAnyGrade ? ($isPassed ? 'text-success' : 'text-danger') : 'text-muted' ?>">
                        <?= $hasAnyGrade ? ($isPassed ? 'PASSED' : 'FAILED') : 'INC' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if (!empty($sheet['remarks'])): ?>
        <div class="mb-3 p-2 bg-light border rounded small">
            <strong>Administrative Remarks:</strong> <?= htmlspecialchars($sheet['remarks']) ?>
        </div>
    <?php endif; ?>

    <!-- Signature Block -->
    <div class="signature-block">
        <div class="row text-center">
            <div class="col-4">
                <div class="signature-line">
                    <?= htmlspecialchars($sheet['faculty_first_name'] . ' ' . $sheet['faculty_last_name']) ?>
                </div>
                <span class="text-muted small">Faculty Instructor</span>
            </div>
            <div class="col-4">
                <div class="signature-line">
                    <?= !empty($sheet['approver_first_name']) ? htmlspecialchars($sheet['approver_first_name'] . ' ' . $sheet['approver_last_name']) : 'Dean Office Signatory' ?>
                </div>
                <span class="text-muted small">College Dean</span>
            </div>
            <div class="col-4">
                <div class="signature-line">
                    Office of the Registrar
                </div>
                <span class="text-muted small">Confirmed &amp; Recorded</span>
            </div>
        </div>
    </div>
</div>

</body>
</html>
