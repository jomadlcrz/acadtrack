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
            <div class="col-md-3">
                <label for="semester" class="form-label mb-1 fw-semibold small text-muted">Semester:</label>
                <select id="semester" name="semester" class="form-select" onchange="this.form.submit()">
                    <option value="1" <?= ($selectedSemester ?? '1') === '1' ? 'selected' : '' ?>>1st Semester</option>
                    <option value="2" <?= ($selectedSemester ?? '1') === '2' ? 'selected' : '' ?>>2nd Semester</option>
                </select>
            </div>

            <div class="col-md-4">
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

            <div class="col-md-3">
                <label for="period_id" class="form-label mb-1 fw-semibold small text-muted">Grading period:</label>
                <select id="period_id" name="period_id" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($periods as $period): ?>
                        <option value="<?= $period['id'] ?>" <?= $period['id'] == $periodId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($period['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 text-md-end pt-md-3">
                <?php if ($gradingSheet): ?>
                    <span class="badge badge-<?= strtolower($gradingSheet['status']) ?> fs-6 py-2 px-3">
                        <?php if ($gradingSheet['status'] === 'DRAFT'): ?>
                            <i class="bi bi-pencil-square me-1"></i> Draft
                        <?php elseif ($gradingSheet['status'] === 'SUBMITTED'): ?>
                            <i class="bi bi-lock-fill me-1"></i> Submitted
                        <?php elseif ($gradingSheet['status'] === 'APPROVED'): ?>
                            <i class="bi bi-check-circle-fill me-1"></i> Approved
                        <?php elseif ($gradingSheet['status'] === 'RETURNED'): ?>
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Returned
                        <?php else: ?>
                            <?= htmlspecialchars(ucfirst(strtolower($gradingSheet['status']))) ?>
                        <?php endif; ?>
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

<?php if ($gradingSheet && $gradingSheet['status'] === 'SUBMITTED'): ?>
    <div class="alert alert-info d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-info-circle-fill fs-5"></i>
        <div>
            <strong class="fw-semibold">Grading sheet submitted.</strong> This sheet is currently locked under Dean review. Editing is disabled until approved or returned.
        </div>
    </div>
<?php elseif ($gradingSheet && $gradingSheet['status'] === 'RETURNED'): ?>
    <div class="alert alert-warning d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-arrow-counterclockwise fs-5"></i>
        <div>
            <strong class="fw-semibold">Grading sheet returned for revision.</strong> Please inspect the scores below, apply the necessary revisions, and submit for review once finalized.
        </div>
    </div>
<?php elseif ($gradingSheet && $gradingSheet['status'] === 'APPROVED'): ?>
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-check-circle-fill fs-5"></i>
        <div>
            <strong class="fw-semibold">Grading sheet approved.</strong> Scores have been officially verified and locked into permanent student academic records.
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
$activeGradingMethod = $currentSubject['grading_method'] ?? 'zero_based';
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
                        class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1.5"
                        data-bs-toggle="modal" 
                        data-bs-target="#intelliGradeModal"
                        title="Open IntelliGrade-style evaluation calculator">
                    <i class="bi bi-calculator"></i> Calculator
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

                <small class="text-muted d-none d-lg-inline ms-1">
                    <kbd class="bg-secondary-subtle text-dark px-1 rounded">Tab</kbd> to move
                </small>
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
                    <thead class="bg-white border-bottom sticky-top">
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
                                    <?php if (!$isLocked): ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-light border px-2 py-1 text-muted"
                                                title="Calculate grade using component scores"
                                                onclick="openCalculatorForStudent('<?= $student['id'] ?>', '<?= htmlspecialchars(addslashes($student['first_name'] . ' ' . $student['last_name'])) ?>')">
                                            <i class="bi bi-calculator" style="font-size: 13px;"></i>
                                        </button>
                                    <?php endif; ?>
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

<!-- IntelliGrade Component Score Calculator Modal -->
<div class="modal fade" id="intelliGradeModal" tabindex="-1" aria-labelledby="intelliGradeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 8px; overflow: hidden;">
            <div class="modal-header bg-light py-3 px-4 border-bottom">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="modal-title h6 fw-bold mb-0 text-dark" id="intelliGradeModalLabel">
                            <i class="bi bi-calculator me-1 text-primary"></i> Period Score Calculator
                        </h5>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 11px;">
                            <?= htmlspecialchars($activePeriodName) ?>
                        </span>
                    </div>
                    <small class="text-muted">Compute student period mark from syllabus component breakdown (IntelliGrade model).</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="row g-3 mb-3">
                    <!-- Target Student Selector -->
                    <div class="col-md-6">
                        <label for="calcTargetStudent" class="form-label small fw-semibold text-dark mb-1">Target Student:</label>
                        <select id="calcTargetStudent" class="form-select form-select-sm">
                            <option value="">-- General Calculation Scratchpad --</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= $student['id'] ?>">
                                    <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?> (<?= htmlspecialchars($student['student_number'] ?? 'No ID') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Syllabus Template Selector -->
                    <div class="col-md-6">
                        <label for="calcSubjectType" class="form-label small fw-semibold text-dark mb-1">Curricular Syllabus Model:</label>
                        <select id="calcSubjectType" class="form-select form-select-sm" onchange="switchSubjectTemplate()">
                            <option value="major_with_lab" <?= in_array($activeSubjectNature, ['Laboratory', 'Combined']) ? 'selected' : '' ?>>
                                Major with Laboratory (Exam, Quiz, Lab Out/Perf, Part)
                            </option>
                            <option value="major_without_lab" <?= ($activeSubjectNature === 'Lecture') ? 'selected' : '' ?>>
                                Major without Lab / Lecture (Exam, Quiz, Projects, Part, Assign)
                            </option>
                            <option value="research">
                                Research / Thesis (Proposal, Implementation, Paper, Defense, Adviser)
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Grading Method Switcher -->
                <div class="p-2.5 rounded border bg-light mb-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="small fw-semibold text-dark">Computation Rule:</span>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="radio" name="calcGradingMethod" id="methodZeroBased" value="zero_based" <?= ($activeGradingMethod === 'zero_based') ? 'checked' : '' ?> onchange="recalculateComponents()">
                            <label class="form-check-label small" for="methodZeroBased">Zero-Based (Raw %)</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="radio" name="calcGradingMethod" id="methodFiftyBased" value="fifty_based" <?= ($activeGradingMethod === 'fifty_based') ? 'checked' : '' ?> onchange="recalculateComponents()">
                            <label class="form-check-label small" for="methodFiftyBased">50-Based Transmutation</label>
                        </div>
                    </div>
                    <span class="text-muted small" id="methodExplanationText" style="font-size: 11px;">
                        Exam transmuted: (Raw / 2) + 50
                    </span>
                </div>

                <!-- Dynamic Components Table -->
                <div class="table-responsive border rounded mb-3">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-2 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Component</th>
                                <th class="py-2 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 100px; font-size: 11px;">Weight</th>
                                <th class="py-2 px-3 text-secondary text-uppercase fw-semibold text-end" style="width: 160px; font-size: 11px;">Raw Score (0–100)</th>
                                <th class="py-2 px-3 text-secondary text-uppercase fw-semibold text-end" style="width: 140px; font-size: 11px;">Contribution</th>
                            </tr>
                        </thead>
                        <tbody id="calcComponentBody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Live Summary Banner -->
                <div class="p-3 rounded border d-flex justify-content-between align-items-center" id="calcResultBanner" style="background-color: #f8fafc;">
                    <div>
                        <span class="text-muted small d-block">Computed Period Rating</span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="h4 mb-0 fw-bold font-monospace text-primary" id="calcComputedGrade">0.00</span>
                            <span class="text-muted small">/ 100.00</span>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="text-muted small d-block mb-1">CHED Benchmark Standing</span>
                        <span id="calcStatusBadge" class="badge bg-secondary-subtle text-secondary border px-2.5 py-1.5" style="font-size: 12px;">
                            Incomplete
                        </span>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light py-2.5 px-4 border-top d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary" onclick="resetCalculatorInputs()">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Clear inputs
                </button>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1.5" id="btnApplyGrade" onclick="applyCalculatedGrade(false)">
                        <i class="bi bi-check2-circle"></i> Apply to student
                    </button>
                    <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center gap-1.5" id="btnApplyNextGrade" onclick="applyCalculatedGrade(true)">
                        <i class="bi bi-arrow-right-circle"></i> Apply &amp; Next
                    </button>
                </div>
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
        label: 'Major without Lab (Lecture)',
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

let currentModalStudentId = null;

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
    const isFiftyBased = document.getElementById('methodFiftyBased').checked;
    const expl = document.getElementById('methodExplanationText');
    if (expl) {
        expl.textContent = isFiftyBased ? 'Exam transmuted: (Raw / 2) + 50' : 'Direct zero-based absolute scale';
    }

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

function openCalculatorForStudent(studentId, studentName) {
    const studentSelect = document.getElementById('calcTargetStudent');
    if (studentSelect) {
        studentSelect.value = studentId;
    }
    currentModalStudentId = studentId;

    const modalEl = document.getElementById('intelliGradeModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
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

function applyCalculatedGrade(andNext) {
    const gradeEl = document.getElementById('calcComputedGrade');
    const studentSelect = document.getElementById('calcTargetStudent');
    const studentId = studentSelect ? studentSelect.value : currentModalStudentId;

    if (!studentId) {
        alert('Please select a target student from the roster.');
        return;
    }

    const calculatedGrade = parseFloat(gradeEl.textContent) || 0;
    const targetInput = document.getElementById('grade_input_' + studentId);
    const targetRow = document.getElementById('student_row_' + studentId);

    if (targetInput) {
        targetInput.value = calculatedGrade.toFixed(2);
        updateRowStatus(studentId);

        // Flash animation
        if (targetRow) {
            targetRow.style.transition = 'background-color 0.4s ease';
            targetRow.style.backgroundColor = '#dcfce7';
            setTimeout(() => {
                targetRow.style.backgroundColor = '';
            }, 1200);
        }
    }

    if (andNext) {
        // Move to the next student in the select
        const options = studentSelect.options;
        let nextIdx = -1;
        for (let i = 0; i < options.length; i++) {
            if (options[i].value === studentId) {
                nextIdx = i + 1;
                break;
            }
        }

        if (nextIdx > 0 && nextIdx < options.length) {
            studentSelect.selectedIndex = nextIdx;
            currentModalStudentId = studentSelect.value;
            resetCalculatorInputs();
        } else {
            // End of roster
            const modalEl = document.getElementById('intelliGradeModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
        }
    } else {
        const modalEl = document.getElementById('intelliGradeModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }
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
});
</script>

<script src="<?= asset('vendor/xlsx/xlsx.full.min.js') ?>"></script>
<?php include __DIR__ . '/../../components/pass-card-modal.php'; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
