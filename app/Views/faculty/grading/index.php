<?php
$pageTitle = 'Grade Encoding Sheet';
$subtitle = 'Encode and review student performance marks for the designated academic grading period.';
$headerActions = '<a href="' . url('/faculty/subjects') . '" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"><i class="bi bi-arrow-left"></i> Back to subjects</a>';
ob_start();
$isLocked = in_array($gradingSheet['status'] ?? '', ['SUBMITTED', 'APPROVED']);
?>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="card-body py-3 px-4">
        <form method="GET" action="<?= url('/faculty/grading') ?>" class="row g-3 align-items-center">
            <input type="hidden" name="period_id" value="<?= htmlspecialchars((string)$periodId) ?>">

            <div class="col-md-4 col-lg-3">
                <label for="semester" class="form-label mb-1 fw-semibold small text-muted">Semester:</label>
                <select id="semester" name="semester" class="form-select" onchange="this.form.submit()">
                    <option value="1" <?= ($selectedSemester ?? '1') === '1' ? 'selected' : '' ?>>1st Semester</option>
                    <option value="2" <?= ($selectedSemester ?? '1') === '2' ? 'selected' : '' ?>>2nd Semester</option>
                </select>
            </div>

            <div class="col-md-5 col-lg-6">
                <label for="subject_id" class="form-label mb-1 fw-semibold small text-muted">Subject:</label>
                <select id="subject_id" name="subject_id" class="form-select" onchange="this.form.submit()">
                    <?php if (empty($assignedSubjects)): ?>
                        <option value="">No subjects assigned</option>
                    <?php else: ?>
                        <?php foreach ($assignedSubjects as $subj): ?>
                            <option value="<?= $subj['id'] ?>" <?= $subj['id'] == $subjectId ? 'selected' : '' ?>>
                                <?= htmlspecialchars($subj['code'] . ' - ' . $subj['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-md-3 text-md-end pt-md-3">
                <?php if ($gradingSheet): ?>
                    <?php $sheetStatus = grading_sheet_status((string) $gradingSheet['status']); ?>
                    <span class="badge <?= $sheetStatus['class'] ?> fs-6 py-2 px-3">
                        <i class="bi <?= $sheetStatus['icon'] ?> me-1"></i> <?= htmlspecialchars($sheetStatus['label']) ?>
                    </span>
                <?php else: ?>
                    <span class="badge bg-secondary-subtle text-secondary border fs-6 py-2 px-3">
                        <i class="bi bi-dash-circle me-1"></i> Unsaved
                    </span>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Period Tabs (Excel Workbook Style matching IntelliGrade) -->
<div class="tab-bar period-tab-bar mb-4" role="tablist" aria-label="Grading periods">
    <?php foreach ($periods as $period): ?>
        <?php
        $isActive = ((int) $period['id'] === (int) $periodId);
        $periodUrl = url('/faculty/grading?semester=' . ($selectedSemester ?? '1') . '&subject_id=' . $subjectId . '&period_id=' . $period['id']);
        $periodIcon = match(strtolower(trim($period['name']))) {
            'prelim', 'prelims' => 'bi-flag',
            'midterm', 'midterms' => 'bi-flag-fill',
            'semi-final', 'semifinal', 'semi-finals', 'semifinals' => 'bi-award',
            'final', 'finals' => 'bi-trophy',
            default => 'bi-calendar-check'
        };
        ?>
        <a href="<?= $periodUrl ?>" 
           class="tab-btn period-tab-btn <?= $isActive ? 'active' : '' ?>"
           role="tab"
           aria-selected="<?= $isActive ? 'true' : 'false' ?>"
           title="Switch to <?= htmlspecialchars($period['name']) ?> grading sheet">
            <i class="bi <?= $periodIcon ?>" aria-hidden="true"></i>
            <span><?= htmlspecialchars($period['name']) ?></span>
        </a>
    <?php endforeach; ?>
</div>

<?php if ($gradingSheet && in_array($gradingSheet['status'], ['SUBMITTED', 'UNDER_REVIEW'], true)): ?>
    <div class="alert alert-info d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-info-circle-fill fs-5"></i>
        <div>
            <strong class="fw-semibold"><?= $gradingSheet['status'] === 'UNDER_REVIEW' ? 'The Dean is reviewing this sheet.' : 'Grading sheet submitted.' ?></strong> Editing is locked until the Dean approves it or returns it to you.
        </div>
    </div>
<?php elseif ($gradingSheet && $gradingSheet['status'] === 'RETURNED'): ?>
    <div class="alert alert-warning d-flex align-items-start gap-2 mb-4" role="alert">
        <i class="bi bi-arrow-return-left fs-5"></i>
        <div>
            <strong class="fw-semibold">The Dean returned this sheet for correction.</strong>
            <?php if (!empty($gradingSheet['remarks'])): ?>
                <div class="mt-1">Reason: &ldquo;<?= htmlspecialchars($gradingSheet['remarks']) ?>&rdquo;</div>
            <?php endif; ?>
            <div class="mt-1">Fix the marks below, then submit the sheet again.</div>
        </div>
    </div>
<?php elseif ($gradingSheet && $gradingSheet['status'] === 'APPROVED'): ?>
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-check-circle-fill fs-5"></i>
        <div>
            <strong class="fw-semibold">Approved by the Dean.</strong> Waiting for final confirmation. Students will see the marks once the sheet is finalized.
        </div>
    </div>
<?php elseif ($gradingSheet && $gradingSheet['status'] === 'FINALIZED'): ?>
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-lock-fill fs-5"></i>
        <div>
            <strong class="fw-semibold">Finalized.</strong> These marks are official, locked, and visible to your students.
        </div>
    </div>
<?php endif; ?>

<?php
$activePeriodName = 'Grading Period';
foreach ($periods as $p) {
    if ((int) $p['id'] === (int) $periodId) {
        $activePeriodName = $p['name'];
        break;
    }
}
$activeSubjectNature = $currentSubject['nature'] ?? 'Lecture';
$activeGradingMethod = $gradingSetting['grading_method'] ?? ($currentSubject['grading_method'] ?? 'zero_based');
$activePrelimWeight = (float) ($gradingSetting['prelim_weight'] ?? ($currentSubject['prelim_weight'] ?? 20.00));
$activeMidtermWeight = (float) ($gradingSetting['midterm_weight'] ?? ($currentSubject['midterm_weight'] ?? 20.00));
$activeSemiFinalWeight = (float) ($gradingSetting['semi_final_weight'] ?? ($currentSubject['semi_final_weight'] ?? 20.00));
$activeFinalWeight = (float) ($gradingSetting['final_weight'] ?? ($currentSubject['final_weight'] ?? 40.00));
?>

<?php if (empty($students)): ?>
    <?php
    $icon = 'bi-people';
    $iconColor = 'blue';
    $title = 'No enrolled students found';
    $message = 'There are currently no students registered for this course set.';
    $card = true;
    include __DIR__ . '/../../components/empty-state.php';
    ?>
<?php else: ?>
    <div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
        <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2" style="position: relative; z-index: 1030;">
            <div class="d-flex align-items-center gap-2">
                <h3 class="h6 mb-0 fw-semibold text-dark">Student Grade Roster</h3>
                <span class="text-muted small">(<?= count($students) ?> enrolled students)</span>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" 
                        class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1.5"
                        data-bs-toggle="modal" 
                        data-bs-target="#settingsModal"
                        title="Configure grading method and period weights for this subject">
                    <i class="bi bi-gear"></i> Settings
                </button>

                <!-- Excel Import Trigger -->
                <button type="button" 
                        class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1.5"
                        onclick="document.getElementById('excelScoreImport').click()"
                        title="Import student scores from Excel (.xlsx) or CSV"
                        <?= $isLocked ? 'disabled' : '' ?>>
                    <i class="bi bi-file-earmark-arrow-up"></i> Import Excel
                </button>
                <input type="file" id="excelScoreImport" accept=".xlsx,.xls,.csv" style="display:none" onchange="handleExcelImport(event)">

                <!-- Export Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-inline-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                        <i class="bi bi-download"></i> Export
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border" style="font-size: 12px; z-index: 1050;">
                        <li>
                            <button class="dropdown-item d-flex align-items-center gap-2 py-2" type="button" onclick="exportRosterToExcel('xlsx')">
                                <i class="bi bi-file-earmark-excel text-success"></i> Export Excel (.xlsx)
                            </button>
                        </li>
                        <li>
                            <button class="dropdown-item d-flex align-items-center gap-2 py-2" type="button" onclick="exportRosterToExcel('csv')">
                                <i class="bi bi-file-earmark-text text-primary"></i> Export CSV (.csv)
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <form id="saveGradesForm" method="POST" action="<?= url('/faculty/grading/save') ?>" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="subject_id" value="<?= htmlspecialchars((string)$subjectId) ?>">
            <input type="hidden" name="grading_period_id" value="<?= htmlspecialchars((string)$periodId) ?>">
            <input type="hidden" name="academic_term_id" value="<?= htmlspecialchars((string)($academicTerm['id'] ?? 1)) ?>">
            <input type="hidden" name="semester" value="<?= htmlspecialchars((string)($selectedSemester ?? '1')) ?>">

            <div class="table-responsive" style="max-height: 65vh;">
                <table class="table table-hover align-middle mb-0" id="rosterTable">
                    <thead class="bg-white sticky-top">
                        <tr>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="width: 160px; font-size: 11px;">Student ID</th>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Student name</th>
                            <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-end" style="width: 230px; font-size: 11px;">Period score (0.00 – 100.00)</th>
                            <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 140px; font-size: 11px;">Entry status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($students as $student): ?>
                        <?php $score = $grades[$student['id']] ?? ''; ?>
                        <tr id="student_row_<?= $student['id'] ?>"
                            data-id="<?= $student['id'] ?>"
                            data-student-number="<?= htmlspecialchars($student['student_number'] ?? '') ?>"
                            data-student-name="<?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>"
                            data-year-level="<?= htmlspecialchars((string)($student['year_level'] ?? 1)) ?>"
                            data-status="<?= htmlspecialchars($student['status'] ?? 'Regular') ?>">
                            <td class="px-3 fw-semibold font-monospace small text-dark">
                                <?= !empty($student['student_number']) ? htmlspecialchars($student['student_number']) : 'No ID' ?>
                            </td>
                            <td class="px-3 fw-semibold text-dark">
                                <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                            </td>
                            <td class="px-3 text-end">
                                <div class="d-inline-flex align-items-center gap-1 justify-content-end w-100">
                                    <input type="number" 
                                           id="grade_input_<?= $student['id'] ?>"
                                           name="grades[<?= $student['id'] ?>]" 
                                           value="<?= htmlspecialchars((string)$score) ?>"
                                           min="0" max="100" step="0.01"
                                           class="form-control form-control-sm grade-input text-end font-monospace"
                                           style="max-width: 105px;"
                                           placeholder="—"
                                           oninput="updateRowStatus(<?= $student['id'] ?>)"
                                           <?= $isLocked ? 'disabled' : '' ?>>
                                    <button type="button" 
                                            class="btn btn-sm btn-light border px-2 py-1 text-muted"
                                            title="View authenticated grade pass slip"
                                            onclick="openStudentPassCard(<?= $student['id'] ?>)">
                                        <i class="bi bi-file-earmark-person" style="font-size: 13px;"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="px-3 text-center" id="status_cell_<?= $student['id'] ?>">
                                <?php if ($score !== '' && $score !== null): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check2"></i> Encoded
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                        Pending
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-light py-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted d-flex align-items-center gap-1">
                    <i class="bi bi-shield-check text-primary"></i> Scores are saved as working draft until officially submitted.
                </small>
                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2" <?= $isLocked ? 'disabled' : '' ?>>
                    <i class="bi bi-save"></i> Save draft
                </button>
            </div>
        </form>
    </div>

    <?php if ($gradingSheet && in_array($gradingSheet['status'], ['DRAFT', 'RETURNED'])): ?>
        <div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
            <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="h6 fw-semibold text-dark mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-send-check text-primary"></i> Submit Sheet for Dean Approval
                    </h4>
                    <p class="text-muted small mb-0">
                        Once submitted, score encoding will be locked and routed directly to the Dean Review Queue.
                    </p>
                </div>
                <form method="POST" action="<?= url('/faculty/grading/submit') ?>" class="m-0" onsubmit="return confirm('Are you sure you want to submit this grading sheet for review? Once submitted, student score fields will be locked.');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="grading_sheet_id" value="<?= htmlspecialchars((string)$gradingSheet['id']) ?>">
                    <button type="submit" class="btn btn-warning d-inline-flex align-items-center gap-2 fw-semibold">
                        <i class="bi bi-send"></i> Submit for review
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<style>
.manage-section {
    background: #f8fafc;
    border-radius: 6px;
    padding: 10px 14px;
    margin-bottom: 10px;
    border: 1px solid #e2e8f0;
}
.manage-section.section-accent {
    border-color: #c9a84c;
}
.manage-section .section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 6px;
}
.manage-section .section-header h4 {
    font-size: 0.8rem;
    font-weight: 700;
    color: #0a2e4a;
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 0;
}
.setting-row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    padding: 3px 0;
}
.setting-row label {
    font-weight: 600;
    font-size: 0.75rem;
    min-width: 100px;
    color: #475569;
    margin-bottom: 0;
}
.setting-row input, .setting-row select {
    padding: 3px 8px;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    font-size: 0.75rem;
    background: #ffffff;
    color: #0f172a;
    outline: none;
}
.setting-row input:focus, .setting-row select:focus {
    border-color: #0a2e4a;
    box-shadow: 0 0 0 2px rgba(10, 46, 74, 0.1);
}
.setting-hint {
    font-size: 0.7rem;
    color: #64748b;
    margin-top: 2px;
    padding-left: 108px;
}
.weight-table-wrap {
    overflow-x: auto;
}
.weight-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.72rem;
    min-width: 280px;
}
.weight-table th {
    background: #0a2e4a;
    color: #ffffff;
    padding: 5px 8px;
    text-align: center;
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    font-weight: 600;
    border: 1px solid #0a2e4a;
}
.weight-table td {
    padding: 4px 6px;
    border: 1px solid #e2e8f0;
    text-align: center;
    background: #ffffff;
    vertical-align: middle;
}
.weight-table td .period-label {
    font-weight: 600;
    font-size: 0.7rem;
    color: #334155;
    white-space: nowrap;
}
.weight-table td input[type="number"],
.weight-table td input.comp-score-input {
    width: 60px;
    padding: 2px 4px;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    text-align: center;
    font-size: 0.72rem;
    font-weight: 600;
    background: #ffffff;
    color: #0f172a;
    font-family: monospace;
}
.weight-table td input[type="number"]:focus,
.weight-table td input.comp-score-input:focus {
    border-color: #0a2e4a;
    outline: none;
    box-shadow: 0 0 0 2px rgba(10, 46, 74, 0.1);
}
.weight-table .sum-row td {
    font-weight: 700;
    font-size: 0.7rem;
    background: #f8fafc;
}
.weight-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 6px;
    flex-wrap: wrap;
    gap: 6px;
}
</style>

<!-- IntelliGrade Settings Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 720px;">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="modal-title" id="settingsModalLabel">
                            <i class="bi bi-gear me-1 text-primary"></i> Settings
                        </h5>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 11px;">
                            <?= htmlspecialchars($activePeriodName) ?>
                        </span>
                    </div>
                    <small class="text-muted">
                        <?= htmlspecialchars($currentSubject['subject_code'] ?? 'Course') ?> &mdash; <?= htmlspecialchars($currentSubject['descriptive_title'] ?? '') ?>
                    </small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-3">
                <form method="POST" action="<?= url('/faculty/grading/settings') ?>" id="subjectSettingsForm" class="mb-0">
                    <?= csrf_field() ?>
                    <input type="hidden" name="subject_id" value="<?= htmlspecialchars((string)$subjectId) ?>">
                    <input type="hidden" name="period_id" value="<?= htmlspecialchars((string)$periodId) ?>">
                    <input type="hidden" name="academic_term_id" value="<?= htmlspecialchars((string)($academicTerm['id'] ?? 1)) ?>">
                    <input type="hidden" name="semester" value="<?= htmlspecialchars((string)($selectedSemester ?? '1')) ?>">

                    <!-- Section 1: General (Grading Method) -->
                    <div class="manage-section">
                        <div class="section-header">
                            <h4><i class="bi bi-sliders text-primary"></i> General</h4>
                        </div>
                        <div class="setting-row">
                            <label>Passing Grade:</label>
                            <input type="number" id="passingGradeInput" value="75" min="0" max="100" step="0.5" style="width: 60px;" readonly disabled>
                            <span class="badge bg-secondary-subtle text-secondary border" style="font-size: 10px;">College Standard</span>
                        </div>
                        <div class="setting-row">
                            <label>Decimals:</label>
                            <select id="decimalPlacesInput" style="width: 60px;" disabled>
                                <option value="2" selected>2</option>
                            </select>
                            <span class="badge bg-secondary-subtle text-secondary border" style="font-size: 10px;">Fixed</span>
                        </div>
                        <div class="setting-row">
                            <label>Grading Method:</label>
                            <select id="gradingMethodSelect" name="grading_method" style="width: 170px;" onchange="onGradingMethodChanged()">
                                <option value="zero_based" <?= ($activeGradingMethod === 'zero_based') ? 'selected' : '' ?>>Zero Based (0-100)</option>
                                <option value="fifty_based" <?= ($activeGradingMethod === 'fifty_based') ? 'selected' : '' ?>>50 Based (score/2+50)</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary py-0 px-2" style="font-size: 0.72rem; height: 26px;">Save</button>
                        </div>
                        <div class="setting-hint">
                            <i class="bi bi-info-circle me-1"></i> Only applies to EXAM scores, not computed totals
                        </div>
                    </div>

                    <!-- Section 2: Period Weights -->
                    <div class="manage-section section-accent">
                        <div class="section-header">
                            <h4><i class="bi bi-layers text-primary"></i> Period Weights</h4>
                        </div>
                        <p style="font-size: 0.68rem; color: #64748b; margin-bottom: 6px;">
                            Per period & subject type. Must sum to 100%.
                        </p>
                        <div class="weight-table-wrap">
                            <table class="weight-table" id="weightTable">
                                <thead>
                                    <tr>
                                        <th style="min-width: 70px;">Scope</th>
                                        <th>Prelim (%)</th>
                                        <th>Midterm (%)</th>
                                        <th>Semi-Final (%)</th>
                                        <th>Final (%)</th>
                                        <th style="min-width: 60px;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <span class="period-label fw-bold"><?= htmlspecialchars($currentSubject['subject_code'] ?? 'Subject') ?></span>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" max="100" id="prelimWeightInput" name="prelim_weight" value="<?= number_format($activePrelimWeight, 2, '.', '') ?>" oninput="validatePeriodWeightSum()">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" max="100" id="midtermWeightInput" name="midterm_weight" value="<?= number_format($activeMidtermWeight, 2, '.', '') ?>" oninput="validatePeriodWeightSum()">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" max="100" id="semiFinalWeightInput" name="semi_final_weight" value="<?= number_format($activeSemiFinalWeight, 2, '.', '') ?>" oninput="validatePeriodWeightSum()">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" max="100" id="finalWeightInput" name="final_weight" value="<?= number_format($activeFinalWeight, 2, '.', '') ?>" oninput="validatePeriodWeightSum()">
                                        </td>
                                        <td id="periodTotalCell" class="fw-bold font-monospace" style="font-size: 0.75rem; color: #16a34a;">
                                            100.00%
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="sum-row">
                                        <td><span style="font-weight: 600; color: #64748b;">Validation</span></td>
                                        <td colspan="5" style="text-align: center;">
                                            <span id="weightValidationMsg" style="font-size: 0.68rem; color: #16a34a;">
                                                <i class="bi bi-check-circle-fill me-1"></i> Period weights sum to exactly 100.00%
                                            </span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="weight-actions">
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="resetPeriodWeightsDefault()" style="font-size: 0.72rem; height: 26px;">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-sm btn-success py-0 px-2" id="btnSaveSubjectSettings" style="font-size: 0.72rem; height: 26px;">
                                <i class="bi bi-save me-1"></i> Save Period Weights
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Section 3: Syllabus Components -->
                <div class="manage-section mb-0">
                    <div class="section-header">
                        <h4><i class="bi bi-book text-primary"></i> Syllabus Components</h4>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <label class="fw-semibold text-secondary mb-0" style="font-size: 0.72rem;">Subject Type:</label>
                            <select id="calcSubjectType" class="form-select form-select-sm py-1" style="width: auto; min-width: 170px; font-size: 0.72rem;" onchange="switchSubjectTemplate()">
                                <option value="major_with_lab" <?= in_array($activeSubjectNature, ['Laboratory', 'Combined']) ? 'selected' : '' ?>>
                                    Major with Lab
                                </option>
                                <option value="major_without_lab" <?= ($activeSubjectNature === 'Lecture') ? 'selected' : '' ?>>
                                    Major without Lab
                                </option>
                                <option value="research">
                                    Research / Thesis
                                </option>
                            </select>
                        </div>
                        <span class="badge bg-light text-secondary border font-monospace" style="font-size: 10px;">
                            Components sum to 100%
                        </span>
                    </div>

                    <div class="weight-table-wrap mb-2">
                        <table class="weight-table">
                            <thead>
                                <tr>
                                    <th style="text-align: left; padding-left: 10px;">Component</th>
                                    <th style="width: 90px;">Weight (%)</th>
                                    <th style="width: 110px;">Score (0–100)</th>
                                    <th style="width: 90px;">Weighted</th>
                                </tr>
                            </thead>
                            <tbody id="calcComponentBody">
                                <!-- Populated dynamically via JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Live Summary Banner -->
                    <div class="d-flex justify-content-between align-items-center p-2 rounded border bg-white">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-secondary fw-semibold" style="font-size: 0.72rem;">Computed Period Rating:</span>
                            <span class="fw-bold font-monospace text-primary" id="calcComputedGrade" style="font-size: 0.9rem;">0.00</span>
                            <span class="text-muted" style="font-size: 0.72rem;">/ 100.00</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span id="calcStatusBadge" class="badge bg-secondary-subtle text-secondary border px-2 py-1" style="font-size: 10px;">
                                Awaiting scores
                            </span>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" onclick="resetCalculatorInputs()" style="font-size: 10px; height: 22px;" title="Clear calculator inputs">
                                <i class="bi bi-arrow-counterclockwise"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const INTELLI_TEMPLATES = {
    'major_with_lab': {
        label: 'Major with Lab',
        components: [
            { key: 'exam', label: 'Periodical Examination', weight: 0.40, isExam: true },
            { key: 'labOutput', label: 'Laboratory Outputs / Reports', weight: 0.30, isExam: false },
            { key: 'quizzes', label: 'Quizzes / Written Assessments', weight: 0.10, isExam: false },
            { key: 'labPerformance', label: 'Laboratory Practical Performance', weight: 0.10, isExam: false },
            { key: 'participation', label: 'Classroom Participation & Recitation', weight: 0.10, isExam: false }
        ]
    },
    'major_without_lab': {
        label: 'Major without Lab',
        components: [
            { key: 'exam', label: 'Periodical Examination', weight: 0.40, isExam: true },
            { key: 'majorProjects', label: 'Major Term Projects / Outputs', weight: 0.20, isExam: false },
            { key: 'quizzes', label: 'Quizzes / Topic Tests', weight: 0.15, isExam: false },
            { key: 'participation', label: 'Class Attendance & Participation', weight: 0.15, isExam: false },
            { key: 'assignments', label: 'Assignments & Case Studies', weight: 0.10, isExam: false }
        ]
    },
    'research': {
        label: 'Research / Thesis',
        components: [
            { key: 'researchImplementation', label: 'Research Prototype / Implementation', weight: 0.30, isExam: false },
            { key: 'finalPaper', label: 'Manuscript / Final Paper Deliverable', weight: 0.30, isExam: false },
            { key: 'oralDefense', label: 'Oral Defense Panel Evaluation', weight: 0.20, isExam: true },
            { key: 'proposalDefense', label: 'Proposal Defense Benchmark', weight: 0.10, isExam: false },
            { key: 'adviserEval', label: 'Adviser Consultation & Evaluation', weight: 0.10, isExam: false }
        ]
    }
};

function validatePeriodWeightSum() {
    const p = parseFloat(document.getElementById('prelimWeightInput')?.value) || 0;
    const m = parseFloat(document.getElementById('midtermWeightInput')?.value) || 0;
    const s = parseFloat(document.getElementById('semiFinalWeightInput')?.value) || 0;
    const f = parseFloat(document.getElementById('finalWeightInput')?.value) || 0;
    const total = Math.round((p + m + s + f) * 100) / 100;

    const totalCell = document.getElementById('periodTotalCell');
    const msg = document.getElementById('weightValidationMsg');
    const btn = document.getElementById('btnSaveSubjectSettings');

    if (totalCell) {
        totalCell.textContent = `${total.toFixed(2)}%`;
        if (Math.abs(total - 100) < 0.01) {
            totalCell.style.color = '#16a34a';
            if (msg) {
                msg.style.color = '#16a34a';
                msg.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Period weights sum to exactly 100.00%';
            }
            if (btn) btn.disabled = false;
        } else {
            totalCell.style.color = '#dc2626';
            if (msg) {
                msg.style.color = '#dc2626';
                msg.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> Period weights must equal 100.00% (currently ${total.toFixed(2)}%).`;
            }
            if (btn) btn.disabled = true;
        }
    }
}

function resetPeriodWeightsDefault() {
    const p = document.getElementById('prelimWeightInput');
    const m = document.getElementById('midtermWeightInput');
    const s = document.getElementById('semiFinalWeightInput');
    const f = document.getElementById('finalWeightInput');
    if (p) p.value = '20.00';
    if (m) m.value = '20.00';
    if (s) s.value = '20.00';
    if (f) f.value = '40.00';
    validatePeriodWeightSum();
}

function onGradingMethodChanged() {
    recalculateComponents();
}

function switchSubjectTemplate() {
    const typeSelect = document.getElementById('calcSubjectType');
    const selected = typeSelect ? typeSelect.value : 'major_with_lab';
    const tmpl = INTELLI_TEMPLATES[selected] || INTELLI_TEMPLATES['major_with_lab'];
    const tbody = document.getElementById('calcComponentBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    tmpl.components.forEach(comp => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="text-align: left; padding-left: 10px;">
                <span class="fw-semibold text-dark" style="font-size: 0.72rem;">${comp.label}</span>
                ${comp.isExam ? '<span class="badge bg-light text-muted border ms-1" style="font-size: 9px;">Transmuted</span>' : ''}
            </td>
            <td class="font-monospace fw-semibold" style="font-size: 0.72rem;">
                ${(comp.weight * 100).toFixed(0)}%
            </td>
            <td>
                <input type="number" 
                       min="0" max="100" step="0.5" 
                       data-key="${comp.key}" 
                       data-weight="${comp.weight}" 
                       data-is-exam="${comp.isExam ? '1' : '0'}"
                       class="comp-score-input" 
                       placeholder="0.00" 
                       oninput="recalculateComponents()"
                       style="width: 60px;">
            </td>
            <td class="font-monospace fw-semibold text-primary" id="contrib_${comp.key}" style="font-size: 0.72rem;">
                0.00%
            </td>
        `;
        tbody.appendChild(tr);
    });

    recalculateComponents();
}

function recalculateComponents() {
    const methodSelect = document.getElementById('gradingMethodSelect');
    const isFiftyBased = methodSelect ? (methodSelect.value === 'fifty_based') : false;

    const inputs = document.querySelectorAll('.comp-score-input');
    let totalScore = 0;
    let hasAnyData = false;

    inputs.forEach(input => {
        const raw = parseFloat(input.value);
        const weight = parseFloat(input.dataset.weight) || 0;
        const isExam = input.dataset.isExam === '1';
        const contribEl = document.getElementById('contrib_' + input.dataset.key);

        if (!isNaN(raw) && raw >= 0) {
            hasAnyData = true;
            let effective = raw;
            if (isExam && isFiftyBased) {
                effective = (raw / 2) + 50;
            }
            const contrib = effective * weight;
            totalScore += contrib;
            if (contribEl) contribEl.textContent = contrib.toFixed(2) + '%';
        } else {
            if (contribEl) contribEl.textContent = '0.00%';
        }
    });

    const gradeEl = document.getElementById('calcComputedGrade');
    const badgeEl = document.getElementById('calcStatusBadge');

    const rounded = Math.round(totalScore * 100) / 100;
    if (gradeEl) gradeEl.textContent = rounded.toFixed(2);

    if (!hasAnyData) {
        if (badgeEl) {
            badgeEl.className = 'badge bg-secondary-subtle text-secondary border px-2 py-1';
            badgeEl.textContent = 'Awaiting scores';
        }
    } else if (rounded >= 75.0) {
        if (badgeEl) {
            badgeEl.className = 'badge bg-success-subtle text-success border border-success-subtle px-2 py-1';
            badgeEl.textContent = 'PASSED (≥ 75.00%)';
        }
    } else {
        if (badgeEl) {
            badgeEl.className = 'badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1';
            badgeEl.textContent = 'FAILED (< 75.00%)';
        }
    }
}

function resetCalculatorInputs() {
    document.querySelectorAll('.comp-score-input').forEach(inp => {
        inp.value = '';
    });
    recalculateComponents();
}

function updateRowStatus(studentId) {
    const input = document.getElementById('grade_input_' + studentId);
    const statusCell = document.getElementById('status_cell_' + studentId);
    if (!input || !statusCell) return;

    const val = input.value.trim();
    if (val !== '' && !isNaN(parseFloat(val))) {
        statusCell.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check2"></i> Encoded</span>';
    } else {
        statusCell.innerHTML = '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Pending</span>';
    }
}

// =========================================================================
// Pass Card & Excel Integration (IntelliGrade Model)
// =========================================================================
function openStudentPassCard(studentId) {
    const row = document.getElementById('student_row_' + studentId);
    if (!row) return;

    const input = document.getElementById('grade_input_' + studentId);
    const currentScore = input ? input.value : '';
    const subjectCode = '<?= htmlspecialchars(addslashes($currentSubject['code'] ?? 'IT101')) ?>';
    const subjectTitle = '<?= htmlspecialchars(addslashes($currentSubject['name'] ?? 'Introduction to Computing')) ?>';
    const courseNature = '<?= htmlspecialchars(addslashes($activeSubjectNature)) ?>';
    const periodName = '<?= htmlspecialchars(addslashes($activePeriodName)) ?>';
    const facultyName = '<?= htmlspecialchars(addslashes($user['name'] ?? 'Assigned Faculty')) ?>';
    const schoolYear = '<?= htmlspecialchars(addslashes($academicTerm['academic_year_name'] ?? '2026-2027')) ?>';
    const semesterLabel = '<?= ($selectedSemester ?? '1') === '2' ? '2nd Semester' : '1st Semester' ?>';

    renderPassCard({
        studentName: row.dataset.studentName,
        studentNumber: row.dataset.studentNumber,
        yearLevel: row.dataset.yearLevel,
        status: row.dataset.status,
        subjectCode: subjectCode,
        subjectTitle: subjectTitle,
        courseNature: courseNature,
        facultyName: facultyName,
        schoolYear: schoolYear,
        semesterLabel: semesterLabel,
        prelim: periodName === 'Prelim' ? currentScore : null,
        midterm: periodName === 'Midterm' ? currentScore : null,
        semiFinal: periodName === 'Semi-Final' ? currentScore : null,
        final: periodName === 'Final' ? currentScore : null,
        finalRating: currentScore
    });
}

function exportRosterToExcel(format) {
    const data = [];
    const rows = document.querySelectorAll('#rosterTable tbody tr');
    rows.forEach(tr => {
        const studentNo = tr.dataset.studentNumber || '';
        const name = tr.dataset.studentName || '';
        const year = tr.dataset.yearLevel || '';
        const status = tr.dataset.status || 'Regular';
        const input = tr.querySelector('.grade-input');
        const score = input ? input.value : '';
        data.push({
            'Student ID': studentNo,
            'Student Full Name': name,
            'Year Level': year,
            'Status': status,
            'Period Score': score !== '' ? parseFloat(score) : ''
        });
    });

    if (typeof XLSX === 'undefined') {
        alert('Spreadsheet export engine is still loading. Please try again.');
        return;
    }

    const ws = XLSX.utils.json_to_sheet(data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Roster');

    const subjectCode = '<?= htmlspecialchars($currentSubject['code'] ?? 'SUBJ') ?>';
    const periodName = '<?= htmlspecialchars($activePeriodName) ?>';
    const dateStr = new Date().toISOString().slice(0, 10);
    const filename = `Roster_${subjectCode}_${periodName}_${dateStr}.${format === 'csv' ? 'csv' : 'xlsx'}`;

    if (format === 'csv') {
        XLSX.writeFile(wb, filename, { bookType: 'csv' });
    } else {
        XLSX.writeFile(wb, filename, { bookType: 'xlsx' });
    }
}

function handleExcelImport(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (typeof XLSX === 'undefined') {
        alert('Spreadsheet parsing engine is not loaded.');
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
            const jsonData = XLSX.utils.sheet_to_json(firstSheet);

            let matchedCount = 0;
            jsonData.forEach(row => {
                const rawId = (row['Student ID'] || row['Student Number'] || row['StudentID'] || row['ID'] || row['student_id'] || row['student_number'] || '').toString().trim();
                const rawName = (row['Student Full Name'] || row['Student Name'] || row['Name'] || row['name'] || '').toString().trim().toLowerCase();
                const rawScore = row['Period Score'] ?? row['Score'] ?? row['Grade'] ?? row['grade'] ?? row['score'] ?? row['period_score'];

                if (rawScore !== undefined && rawScore !== null && rawScore !== '') {
                    const parsedScore = parseFloat(rawScore);
                    if (!isNaN(parsedScore)) {
                        let matchedRow = null;
                        if (rawId) {
                            matchedRow = document.querySelector(`tr[data-student-number="${rawId}"]`);
                        }
                        if (!matchedRow && rawName) {
                            document.querySelectorAll('#rosterTable tbody tr').forEach(tr => {
                                if ((tr.dataset.studentName || '').trim().toLowerCase() === rawName) {
                                    matchedRow = tr;
                                }
                            });
                        }

                        if (matchedRow) {
                            const input = matchedRow.querySelector('.grade-input');
                            if (input && !input.disabled) {
                                input.value = parsedScore.toFixed(2);
                                updateRowStatus(matchedRow.dataset.id);
                                matchedRow.style.transition = 'background-color 0.4s ease';
                                matchedRow.style.backgroundColor = '#e0f2fe';
                                matchedCount++;
                            }
                        }
                    }
                }
            });

            if (matchedCount > 0) {
                alert(`Successfully matched and imported scores for ${matchedCount} student(s) from "${file.name}". Click "Save draft" to save these marks.`);
            } else {
                alert('No matching students found. Please verify the spreadsheet contains "Student ID" (or "Student Full Name") and "Period Score".');
            }
        } catch (err) {
            console.error('Import error', err);
            alert('Error parsing spreadsheet file: ' + err.message);
        } finally {
            event.target.value = '';
        }
    };
    reader.readAsArrayBuffer(file);
}

document.addEventListener('DOMContentLoaded', function() {
    switchSubjectTemplate();
    validatePeriodWeightSum();
});
</script>

<script src="<?= asset('vendor/xlsx/xlsx.full.min.js') ?>"></script>
<?php include __DIR__ . '/../../components/pass-card-modal.php'; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
