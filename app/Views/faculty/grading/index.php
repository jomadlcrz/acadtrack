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
.method-card {
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 14px;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    display: block;
    position: relative;
    user-select: none;
    text-decoration: none;
}
.method-card:hover {
    border-color: #93c5fd;
    background-color: #fbfdff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
}
.method-card.is-active,
.method-card:has(input[type="radio"]:checked) {
    border-color: #2563eb !important;
    background-color: #f4f8ff !important;
    box-shadow: 0 0 0 1px #2563eb, 0 4px 12px rgba(37, 99, 235, 0.1) !important;
}
.method-card .method-radio {
    accent-color: #2563eb;
    width: 17px;
    height: 17px;
    cursor: pointer;
}
.method-card .method-tag {
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 6px;
    font-weight: 600;
}
.method-card.is-active .method-tag,
.method-card:has(input[type="radio"]:checked) .method-tag {
    background-color: #dbeafe !important;
    color: #1e40af !important;
    border-color: #bfdbfe !important;
}
</style>

<!-- IntelliGrade Settings Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
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
                <form method="POST" action="<?= url('/faculty/grading/settings') ?>" id="subjectSettingsForm" class="mb-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="subject_id" value="<?= htmlspecialchars((string)$subjectId) ?>">
                    <input type="hidden" name="period_id" value="<?= htmlspecialchars((string)$periodId) ?>">
                    <input type="hidden" name="academic_term_id" value="<?= htmlspecialchars((string)($academicTerm['id'] ?? 1)) ?>">
                    <input type="hidden" name="semester" value="<?= htmlspecialchars((string)($selectedSemester ?? '1')) ?>">

                    <!-- Section 1: General (Grading Method) -->
                    <div class="border rounded-3 p-3 mb-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2.5">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-sliders text-primary fs-6"></i>
                                <div>
                                    <span class="small fw-bold text-dark d-block lh-sm">General</span>
                                    <span class="text-muted" style="font-size: 11px;">Subject grading computation rule</span>
                                </div>
                            </div>
                            <span class="badge bg-white text-secondary border px-2.5 py-1 rounded-pill d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                                <i class="bi bi-mortarboard text-primary"></i> Applies to Subject
                            </span>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="method-card h-100 <?= ($activeGradingMethod === 'zero_based') ? 'is-active' : '' ?>" for="methodZeroBased">
                                    <div class="d-flex align-items-start justify-content-between mb-1.5">
                                        <div class="d-flex align-items-center gap-2">
                                            <input class="form-check-input method-radio mt-0" 
                                                   type="radio" 
                                                   name="grading_method" 
                                                   id="methodZeroBased" 
                                                   value="zero_based" 
                                                   <?= ($activeGradingMethod === 'zero_based') ? 'checked' : '' ?> 
                                                   onchange="onGradingMethodChanged()">
                                            <span class="fw-bold text-dark small">Zero-Based (Raw %)</span>
                                        </div>
                                        <span class="method-tag badge bg-light text-secondary border font-monospace">Raw = Grade</span>
                                    </div>
                                    <p class="text-muted small mb-2 ps-4" style="font-size: 11.5px; line-height: 1.45;">
                                        Absolute percentage scale (0.00 – 100.00). Exam scores are unadjusted; raw performance reflects final mark directly.
                                    </p>
                                    <div class="ps-4 pt-1.5 border-top d-flex align-items-center justify-content-between text-muted" style="font-size: 11px;">
                                        <span><i class="bi bi-shield-check text-success me-1"></i>Direct Scale</span>
                                        <span class="font-monospace text-dark">50 raw &rarr; 50.00%</span>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="method-card h-100 <?= ($activeGradingMethod === 'fifty_based') ? 'is-active' : '' ?>" for="methodFiftyBased">
                                    <div class="d-flex align-items-start justify-content-between mb-1.5">
                                        <div class="d-flex align-items-center gap-2">
                                            <input class="form-check-input method-radio mt-0" 
                                                   type="radio" 
                                                   name="grading_method" 
                                                   id="methodFiftyBased" 
                                                   value="fifty_based" 
                                                   <?= ($activeGradingMethod === 'fifty_based') ? 'checked' : '' ?> 
                                                   onchange="onGradingMethodChanged()">
                                            <span class="fw-bold text-dark small">50-Based Transmutation</span>
                                        </div>
                                        <span class="method-tag badge bg-light text-secondary border font-monospace">(Raw ÷ 2) + 50</span>
                                    </div>
                                    <p class="text-muted small mb-2 ps-4" style="font-size: 11.5px; line-height: 1.45;">
                                        Transmutes examination mark so that a 50% raw score reaches the 75.00% passing threshold. Philippine collegiate standard.
                                    </p>
                                    <div class="ps-4 pt-1.5 border-top d-flex align-items-center justify-content-between text-muted" style="font-size: 11px;">
                                        <span><i class="bi bi-award text-primary me-1"></i>College Standard</span>
                                        <span class="font-monospace fw-semibold text-primary">50 raw &rarr; 75.00%</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Period Weights -->
                    <div class="border rounded p-3 mb-3 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label small fw-bold text-dark mb-0">
                                <i class="bi bi-layers me-1 text-primary"></i> Period Weights
                            </label>
                            <span id="periodWeightSumBadge" class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 11px;">
                                Sum: 100.00%
                            </span>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6 col-md-3">
                                <label for="prelimWeightInput" class="form-label small text-muted mb-1">Prelim (%)</label>
                                <input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm text-center font-monospace" id="prelimWeightInput" name="prelim_weight" value="<?= number_format($activePrelimWeight, 2, '.', '') ?>" oninput="validatePeriodWeightSum()">
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="midtermWeightInput" class="form-label small text-muted mb-1">Midterm (%)</label>
                                <input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm text-center font-monospace" id="midtermWeightInput" name="midterm_weight" value="<?= number_format($activeMidtermWeight, 2, '.', '') ?>" oninput="validatePeriodWeightSum()">
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="semiFinalWeightInput" class="form-label small text-muted mb-1">Semi-Final (%)</label>
                                <input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm text-center font-monospace" id="semiFinalWeightInput" name="semi_final_weight" value="<?= number_format($activeSemiFinalWeight, 2, '.', '') ?>" oninput="validatePeriodWeightSum()">
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="finalWeightInput" class="form-label small text-muted mb-1">Final (%)</label>
                                <input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm text-center font-monospace" id="finalWeightInput" name="final_weight" value="<?= number_format($activeFinalWeight, 2, '.', '') ?>" oninput="validatePeriodWeightSum()">
                            </div>
                        </div>
                        <div id="periodWeightError" class="text-danger small mb-2 d-none">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Period weights must sum to exactly 100.00%.
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetPeriodWeightsDefault()" style="font-size: 11.5px;">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset weights to 20/20/20/40
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1.5" id="btnSaveSubjectSettings">
                                <i class="bi bi-check2-circle"></i> Save & Apply to Subject
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Section 3: Syllabus Component Breakdown & Test Preview -->
                <div class="border rounded p-3 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label small fw-bold text-dark mb-0">
                            <i class="bi bi-pie-chart me-1 text-primary"></i> Syllabus Component Weights
                        </label>
                        <select id="calcSubjectType" class="form-select form-select-sm" style="width: auto; min-width: 170px;" onchange="switchSubjectTemplate()">
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

                    <!-- Dynamic Components Table -->
                    <div class="table-responsive border rounded mb-3">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-2 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Component</th>
                                    <th class="py-2 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 110px; font-size: 11px;">Syllabus Weight</th>
                                    <th class="py-2 px-3 text-secondary text-uppercase fw-semibold text-end" style="width: 150px; font-size: 11px;">Score (0–100)</th>
                                    <th class="py-2 px-3 text-secondary text-uppercase fw-semibold text-end" style="width: 130px; font-size: 11px;">Weighted Mark</th>
                                </tr>
                            </thead>
                            <tbody id="calcComponentBody">
                                <!-- Populated dynamically via JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Live Summary Banner -->
                    <div class="p-3 rounded border d-flex justify-content-between align-items-center mb-0" id="calcResultBanner" style="background-color: #f8fafc;">
                        <div>
                            <span class="text-muted small d-block">Computed Period Rating</span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="h4 mb-0 fw-bold font-monospace text-primary" id="calcComputedGrade">0.00</span>
                                <span class="text-muted small">/ 100.00</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-end">
                                <span class="text-muted small d-block mb-1">Status Preview</span>
                                <span id="calcStatusBadge" class="badge bg-secondary-subtle text-secondary border px-2.5 py-1.5" style="font-size: 12px;">
                                    Awaiting scores
                                </span>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetCalculatorInputs()" title="Clear calculator inputs">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Clear inputs
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

    const badge = document.getElementById('periodWeightSumBadge');
    const err = document.getElementById('periodWeightError');
    const btn = document.getElementById('btnSaveSubjectSettings');

    if (badge) {
        badge.textContent = `Sum: ${total.toFixed(2)}%`;
        if (Math.abs(total - 100) < 0.01) {
            badge.className = 'badge bg-success-subtle text-success border border-success-subtle';
            if (err) err.classList.add('d-none');
            if (btn) btn.disabled = false;
        } else {
            badge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle';
            if (err) {
                err.classList.remove('d-none');
                err.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> Period weights must equal 100.00% (currently ${total.toFixed(2)}%).`;
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
    document.querySelectorAll('.method-card').forEach(card => {
        const radio = card.querySelector('input[type="radio"]');
        if (radio && radio.checked) {
            card.classList.add('is-active');
        } else {
            card.classList.remove('is-active');
        }
    });
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
            <td class="px-3">
                <div class="fw-semibold text-dark small">${comp.label}</div>
                ${comp.isExam ? '<span class="badge bg-light text-muted border" style="font-size: 10px;">Subject to Transmutation</span>' : ''}
            </td>
            <td class="px-3 text-center font-monospace small fw-semibold">
                ${(comp.weight * 100).toFixed(0)}%
            </td>
            <td class="px-3 text-end">
                <input type="number" 
                       min="0" max="100" step="0.5" 
                       data-key="${comp.key}" 
                       data-weight="${comp.weight}" 
                       data-is-exam="${comp.isExam ? '1' : '0'}"
                       class="form-control form-control-sm text-end font-monospace comp-score-input" 
                       placeholder="0.00" 
                       oninput="recalculateComponents()">
            </td>
            <td class="px-3 text-end font-monospace small fw-semibold text-primary" id="contrib_${comp.key}">
                0.00%
            </td>
        `;
        tbody.appendChild(tr);
    });

    recalculateComponents();
}

function recalculateComponents() {
    const fiftyEl = document.getElementById('methodFiftyBased');
    const isFiftyBased = fiftyEl ? fiftyEl.checked : false;

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
    const bannerEl = document.getElementById('calcResultBanner');

    const rounded = Math.round(totalScore * 100) / 100;
    if (gradeEl) gradeEl.textContent = rounded.toFixed(2);

    if (!hasAnyData) {
        if (badgeEl) {
            badgeEl.className = 'badge bg-secondary-subtle text-secondary border px-2.5 py-1.5';
            badgeEl.textContent = 'Awaiting scores';
        }
        if (bannerEl) bannerEl.style.backgroundColor = '#f8fafc';
    } else if (rounded >= 75.0) {
        if (badgeEl) {
            badgeEl.className = 'badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5';
            badgeEl.textContent = 'PASSED (≥ 75.00%)';
        }
        if (bannerEl) bannerEl.style.backgroundColor = '#f0fdf4';
    } else {
        if (badgeEl) {
            badgeEl.className = 'badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1.5';
            badgeEl.textContent = 'FAILED (< 75.00%)';
        }
        if (bannerEl) bannerEl.style.backgroundColor = '#fef2f2';
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
