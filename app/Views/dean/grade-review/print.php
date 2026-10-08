<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Official Grade Sheet — <?= htmlspecialchars($sheet['subject_code']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            background: #fff;
            font-size: 8.5pt;
            line-height: 1.25;
        }

        /* Preview chrome */
        .no-print-bar {
            background: #0f172a;
            color: #f8fafc;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .no-print-bar .doc-tag {
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        .no-print-bar .btn-group {
            display: flex;
            gap: 8px;
        }
        .btn-preview {
            border: 1px solid #475569;
            background: transparent;
            color: #f8fafc;
            padding: 6px 14px;
            font-size: 12px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
            transition: all 0.15s ease;
        }
        .btn-preview:hover {
            background: #1e293b;
            color: #fff;
        }
        .btn-preview.btn-primary-preview {
            background: #1e3a5f;
            border-color: #1e3a5f;
            color: #fff;
        }
        .btn-preview.btn-primary-preview:hover {
            background: #152942;
        }

        @media screen {
            body {
                background: #f1f5f9;
                padding-bottom: 30px;
            }
            .print-sheet {
                max-width: 8.5in;
                margin: 24px auto;
                background: #fff;
                padding: 0.4in;
                box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
                border: 1px solid #cbd5e1;
            }
        }

        @page {
            size: portrait;
            margin: 0.35in;
        }

        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
            }
            .no-print-bar {
                display: none !important;
            }
            .print-sheet {
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                width: 100% !important;
            }
        }

        /* Institutional Letterhead */
        .fl-header {
            position: relative;
            min-height: 0.75in;
            text-align: center;
            margin-bottom: 0.35rem;
            line-height: 1.15;
            padding: 0 0.85in;
            border-bottom: 2px solid #1e3a5f;
            padding-bottom: 0.35rem;
        }
        .fl-header .fl-text {
            text-align: center;
        }
        .fl-header strong {
            display: block;
            font-size: 15px;
            font-weight: 700;
            color: #000;
            letter-spacing: 0.05em;
        }
        .fl-header span {
            display: block;
            font-size: 10px;
            color: #222;
        }
        .fl-logo {
            position: absolute;
            top: 0.02in;
            width: 0.65in;
            height: 0.65in;
            object-fit: contain;
        }
        .fl-logo-left { left: 0.05in; }
        .fl-logo-right { right: 0.05in; }

        /* Document Title */
        .fl-heading {
            text-align: center;
            padding: 0.25rem 0;
            font-size: 13.5px;
            font-weight: 700;
            letter-spacing: 0.1em;
            color: #1e3a5f;
            margin-bottom: 0.4rem;
            text-transform: uppercase;
        }

        /* 4-Column Metadata Grid */
        .fl-info {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 0.45rem;
        }
        .fl-info td {
            border: 1px solid #444;
            padding: 0.18rem 0.35rem;
            font-size: 8.5pt;
            vertical-align: middle;
        }
        .fl-info td.lbl {
            font-weight: 700;
            width: 18%;
            background: #f5f5f5;
            color: #222;
            text-transform: uppercase;
            font-size: 7.5pt;
            letter-spacing: 0.02em;
        }
        .fl-info td.oval {
            width: 32%;
            color: #000;
        }

        /* Main Data Table */
        .fl-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 0.5rem;
        }
        .fl-table th, .fl-table td {
            border: 1px solid #444;
            padding: 0.18rem 0.25rem;
            font-size: 8.5pt;
            line-height: 1.2;
            vertical-align: middle;
            overflow-wrap: break-word;
            word-break: break-word;
        }
        .fl-table th {
            background: #f5f5f5;
            font-weight: 700;
            text-align: center;
            font-size: 8.5pt;
            color: #000;
        }
        .fl-table .center { text-align: center; }
        .fl-table .right { text-align: right; }
        .fl-table .bold { font-weight: 700; }
        .fl-table .font-mono { font-family: monospace, Courier, monospace; }

        .remarks-box {
            border: 1px solid #444;
            padding: 0.3rem 0.4rem;
            margin-bottom: 0.5rem;
            font-size: 8pt;
            background: #fafafa;
        }

        /* Institutional Sign-off Block */
        .fl-signoff {
            break-inside: avoid;
            page-break-inside: avoid;
            margin-top: 1.8rem;
        }
        .fl-signoff-row {
            display: flex;
            justify-content: space-between;
            gap: 0.4in;
        }
        .fl-signoff-party {
            flex: 1;
            max-width: 2.6in;
            text-align: center;
        }
        .fl-signoff-label {
            text-align: left;
            font-size: 9px;
            color: #444;
            margin-bottom: 1.5rem;
        }
        .fl-signoff-line {
            display: block;
            width: 100%;
            border-bottom: 1px solid #000;
            margin-bottom: 0.25rem;
        }
        .fl-signoff-name {
            font-weight: bold;
            font-size: 9.5pt;
            line-height: 1.2;
            color: #000;
        }
        .fl-signoff-role {
            font-size: 8.5pt;
            color: #333;
            line-height: 1.2;
        }
    </style>
</head>
<body>

<div class="no-print-bar">
    <div class="doc-tag">
        Official Document Preview: <strong><?= htmlspecialchars($sheet['subject_code']) ?></strong> (<?= htmlspecialchars($sheet['period_name'] ?? 'Grading Sheet') ?>)
    </div>
    <div class="btn-group">
        <button type="button" onclick="window.close()" class="btn-preview">Close</button>
        <button type="button" onclick="window.print()" class="btn-preview btn-primary-preview">Print document</button>
    </div>
</div>

<div class="print-sheet">
    <!-- Header mirroring class-scheduling-frontend -->
    <header class="fl-header">
        <img class="fl-logo fl-logo-left" src="<?= asset('assets/images/gwc.png') ?>" alt="Golden West Colleges Logo">
        <img class="fl-logo fl-logo-right" src="<?= asset('assets/images/cite.png') ?>" alt="College of Information Technology Logo">
        <div class="fl-text">
            <strong>GOLDEN WEST COLLEGES, INC.</strong>
            <span>San Jose Drive, Alaminos City, Pangasinan * Tel. No. (075) 552-7382</span>
            <span>Email Address: goldenwest.colleges@yahoo.com.ph</span>
        </div>
    </header>

    <div class="fl-heading">OFFICIAL GRADING SHEET &amp; CLASS ROSTER</div>

    <!-- Metadata Table mirroring class-scheduling-frontend -->
    <table class="fl-info">
        <tr>
            <td class="lbl">COURSE CODE</td>
            <td class="oval bold"><?= htmlspecialchars($sheet['subject_code']) ?></td>
            <td class="lbl">ACADEMIC TERM</td>
            <td class="oval"><?= htmlspecialchars($sheet['academic_year_name'] ?? '2024-2025') ?> &bull; <?= (string)($sheet['semester'] ?? '1') === '2' ? '2nd Semester' : '1st Semester' ?></td>
        </tr>
        <tr>
            <td class="lbl">DESCRIPTIVE TITLE</td>
            <td class="oval"><?= htmlspecialchars($sheet['subject_name']) ?></td>
            <td class="lbl">YEAR LEVEL</td>
            <td class="oval"><?= htmlspecialchars((string)$sheet['year_level']) ?><?= match((int)$sheet['year_level']) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' } ?> Year</td>
        </tr>
        <tr>
            <td class="lbl">INSTRUCTOR</td>
            <td class="oval bold"><?= htmlspecialchars($sheet['faculty_first_name'] . ' ' . $sheet['faculty_last_name']) ?></td>
            <td class="lbl">GRADING PERIOD</td>
            <td class="oval bold"><?= htmlspecialchars($sheet['period_name'] ?? 'All Periods') ?></td>
        </tr>
        <tr>
            <td class="lbl">COURSE NATURE</td>
            <td class="oval"><?= htmlspecialchars($sheet['subject_nature'] ?? 'Lecture') ?></td>
            <td class="lbl">SHEET STATUS</td>
            <td class="oval bold"><?= htmlspecialchars($sheet['status']) ?></td>
        </tr>
    </table>

    <!-- Main Data Table mirroring class-scheduling-frontend -->
    <table class="fl-table">
        <thead>
            <tr>
                <th style="width: 28px;" class="center">#</th>
                <th style="width: 105px;" class="center">STUDENT ID</th>
                <th>STUDENT FULL NAME</th>
                <th style="width: 75px;" class="center">STATUS</th>
                <?php foreach ($periods as $period): ?>
                    <th style="width: 70px;" class="center"><?= htmlspecialchars(strtoupper($period['name'])) ?></th>
                <?php endforeach; ?>
                <th style="width: 80px;" class="center">FINAL RATING</th>
                <th style="width: 75px;" class="center">REMARKS</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($students)): ?>
                <tr>
                    <td colspan="<?= 6 + count($periods) ?>" class="center" style="padding: 1rem; color: #666;">No student records enrolled.</td>
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
                    <td class="center"><?= $idx + 1 ?></td>
                    <td class="center font-mono bold"><?= !empty($student['student_number']) ? htmlspecialchars($student['student_number']) : 'No ID' ?></td>
                    <td class="bold"><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) ?></td>
                    <td class="center"><?= htmlspecialchars($student['status'] ?? 'Regular') ?></td>
                    <?php foreach ($periods as $period): ?>
                        <?php $pGrade = $studentPeriodGrades[(int)$period['id']] ?? null; ?>
                        <td class="center font-mono">
                            <?= $pGrade !== null ? number_format((float)$pGrade, 2) : '—' ?>
                        </td>
                    <?php endforeach; ?>
                    <td class="center font-mono bold">
                        <?= $hasAnyGrade ? number_format($finalRating, 2) : '—' ?>
                    </td>
                    <td class="center bold" style="color: <?= $hasAnyGrade ? ($isPassed ? '#166534' : '#991b1b') : '#666' ?>;">
                        <?= $hasAnyGrade ? ($isPassed ? 'PASSED' : 'FAILED') : 'INC' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if (!empty($sheet['remarks'])): ?>
        <div class="remarks-box">
            <strong>Administrative Remarks:</strong> <?= htmlspecialchars($sheet['remarks']) ?>
        </div>
    <?php endif; ?>

    <!-- Signature Block mirroring class-scheduling-frontend -->
    <footer class="fl-signoff">
        <div class="fl-signoff-row">
            <div class="fl-signoff-party">
                <div class="fl-signoff-label">Prepared by:</div>
                <span class="fl-signoff-line"></span>
                <div class="fl-signoff-name"><?= htmlspecialchars($sheet['faculty_first_name'] . ' ' . $sheet['faculty_last_name']) ?></div>
                <div class="fl-signoff-role">Faculty Instructor</div>
            </div>
            <div class="fl-signoff-party">
                <div class="fl-signoff-label">Approved by:</div>
                <span class="fl-signoff-line"></span>
                <div class="fl-signoff-name"><?= !empty($sheet['approver_first_name']) ? htmlspecialchars($sheet['approver_first_name'] . ' ' . $sheet['approver_last_name']) : 'College Dean' ?></div>
                <div class="fl-signoff-role">College Dean</div>
            </div>
            <div class="fl-signoff-party">
                <div class="fl-signoff-label">Noted / Recorded by:</div>
                <span class="fl-signoff-line"></span>
                <div class="fl-signoff-name">Office of the Registrar</div>
                <div class="fl-signoff-role">College Registrar</div>
            </div>
        </div>
    </footer>
</div>

<script>
window.addEventListener("load", function () {
    var imgs = Array.prototype.slice.call(document.images);
    Promise.all(
        imgs.map(function (img) {
            if (img.complete) return Promise.resolve();
            return new Promise(function (resolve) {
                img.addEventListener("load", resolve, { once: true });
                img.addEventListener("error", resolve, { once: true });
            });
        })
    ).then(function () {
        setTimeout(function () { window.print(); }, 200);
    });
});
</script>
</body>
</html>
