<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Curriculum Evaluation — <?= htmlspecialchars($student['student_number'] ?? 'Student') ?></title>
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

        /* Top preview bar for on-screen preview */
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
            tr {
                page-break-inside: avoid !important;
            }
        }

        /* Institutional Letterhead mirroring class-scheduling-frontend */
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

        /* Institutional Sign-off Block */
        .fl-signoff {
            break-inside: avoid;
            page-break-inside: avoid;
            margin-top: 1.8rem;
        }
        .fl-signoff-row {
            display: flex;
            justify-content: space-around;
            gap: 0.6in;
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
        .fl-notice {
            margin-top: 1.5rem;
            padding-top: 0.4rem;
            border-top: 1px solid #cbd5e1;
            text-align: center;
            color: #64748b;
            font-size: 7.5pt;
        }
    </style>
</head>
<body>

<div class="no-print-bar">
    <div class="doc-tag">
        Official Document Preview: <strong><?= htmlspecialchars(($student['last_name'] ?? '') . ', ' . ($student['first_name'] ?? '')) ?></strong> (<?= htmlspecialchars($student['student_number'] ?? 'No ID') ?>)
    </div>
    <div class="btn-group">
        <button type="button" onclick="window.close()" class="btn-preview">Close</button>
        <button type="button" onclick="window.print()" class="btn-preview btn-primary-preview">Print document</button>
    </div>
</div>

<div class="print-sheet">
    <header class="fl-header">
        <img class="fl-logo fl-logo-left" src="<?= asset('assets/images/gwc.png') ?>" alt="Golden West Colleges Seal">
        <img class="fl-logo fl-logo-right" src="<?= asset('assets/images/cite.png') ?>" alt="College Seal">
        <div class="fl-text">
            <strong>GOLDEN WEST COLLEGES, INC.</strong>
            <span>San Jose Drive, Alaminos City, Pangasinan * Tel. No. (075) 552-7382</span>
            <span>Email Address: goldenwest.colleges@yahoo.com.ph</span>
        </div>
    </header>

    <div class="fl-heading">CURRICULUM EVALUATION &amp; SCHOLASTIC STANDING REPORT</div>

    <!-- Metadata Grid -->
    <table class="fl-info">
        <tr>
            <td class="lbl">STUDENT NO.</td>
            <td class="oval bold font-mono"><?= htmlspecialchars($student['student_number'] ?? '—') ?></td>
            <td class="lbl">DATE PRINTED</td>
            <td class="oval"><?= date('F d, Y') ?></td>
        </tr>
        <tr>
            <td class="lbl">STUDENT NAME</td>
            <td class="oval bold"><?= htmlspecialchars(($student['last_name'] ?? '') . ', ' . ($student['first_name'] ?? '')) ?></td>
            <td class="lbl">DEGREE PROGRAM</td>
            <td class="oval"><?= htmlspecialchars($programName ?? 'College Degree Program') ?></td>
        </tr>
        <tr>
            <td class="lbl">YEAR &amp; SECTION</td>
            <td class="oval"><?= htmlspecialchars((string)($student['year_level'] ?? 1)) ?><?= ((int)($student['year_level'] ?? 1) === 1 ? 'st' : ((int)($student['year_level'] ?? 1) === 2 ? 'nd' : ((int)($student['year_level'] ?? 1) === 3 ? 'rd' : 'th'))) ?> Year<?= !empty($student['set_name']) ? ' &bull; Set ' . htmlspecialchars($student['set_name']) : '' ?> (<?= htmlspecialchars($student['status'] ?? 'Regular') ?>)</td>
            <td class="lbl">COVERAGE / TERM</td>
            <td class="oval"><?= !empty($academicTerm['school_year']) ? 'A.Y. ' . htmlspecialchars($academicTerm['school_year']) : 'Academic Year' ?> &bull; <?= empty($selectedSemester) ? 'Whole Evaluation' : ($selectedSemester === '2' ? '2nd Semester' : '1st Semester') ?></td>
        </tr>
        <tr>
            <td class="lbl">COURSES EVALUATED</td>
            <td class="oval bold"><?= $count ?> subject(s)</td>
            <td class="lbl">SCHOLASTIC GWA</td>
            <td class="oval bold font-mono"><?= number_format($overallGwa, 2) ?> (<?= ($overallGwa >= 75.0 && $passedCount === $count) ? 'Good Standing' : 'Academic Warning' ?>)</td>
        </tr>
    </table>

    <!-- Main Data Table -->
    <table class="fl-table">
        <thead>
            <tr>
                <th style="width: 28px;" class="center">#</th>
                <th style="width: 110px;" class="center">COURSE CODE</th>
                <th>DESCRIPTIVE TITLE</th>
                <th style="width: 90px;" class="center">COMPUTED AVG</th>
                <th style="width: 95px;" class="center">STATUS</th>
                <th style="width: 120px;" class="center">REMARKS</th>
            </tr>
        </thead>
        <tbody>
            <?php $rowIndex = 0; ?>
            <?php foreach ($evaluations as $subjectCode => $eval): ?>
            <?php 
                $rowIndex++;
                $status = strtoupper($eval['status'] ?? '');
                $average = (float)($eval['average'] ?? 0);
                $isPassed = !in_array($status, ['FAILING', 'NO GRADES', 'NEEDS IMPROVEMENT']) && $average >= 75.0;
            ?>
            <tr>
                <td class="center"><?= $rowIndex ?></td>
                <td class="center font-mono bold"><?= htmlspecialchars($subjectCode) ?></td>
                <td class="bold"><?= htmlspecialchars($eval['subject_name']) ?></td>
                <td class="center font-mono bold"><?= number_format((float)$eval['average'], 2) ?></td>
                <td class="center bold" style="color: <?= $isPassed ? '#166534' : '#991b1b' ?>;">
                    <?= htmlspecialchars($eval['status']) ?>
                </td>
                <td class="center" style="font-size: 8pt; color: #333;">
                    <?= htmlspecialchars($eval['remarks'] ?? '—') ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Institutional Sign-Off Block -->
    <footer class="fl-signoff">
        <div class="fl-signoff-row">
            <div class="fl-signoff-party">
                <div class="fl-signoff-label">Prepared &amp; Evaluated by:</div>
                <span class="fl-signoff-line"></span>
                <div class="fl-signoff-name">Office of the College Registrar</div>
                <div class="fl-signoff-role">College Registrar</div>
            </div>
            <div class="fl-signoff-party">
                <div class="fl-signoff-label">Approved &amp; Certified by:</div>
                <span class="fl-signoff-line"></span>
                <div class="fl-signoff-name">Dean / Academic Head</div>
                <div class="fl-signoff-role">College Dean</div>
            </div>
        </div>
        <div class="fl-notice">
            This document is an authentic scholastic evaluation generated directly from the AcadTrack Student Information System. Any unauthorized alteration or erasure invalidates this record.
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
