<?php
$pageTitle = 'Institutional Settings';
$subtitle = 'Configure institutional grading defaults, evaluation period weights, and academic calendar parameters.';
$semesterLabel = match ((int)($term['semester'] ?? 1)) {
    1 => '1st Semester',
    2 => '2nd Semester',
    3 => 'Summer Term',
    default => 'Semester ' . ($term['semester'] ?? 1)
};
$schoolYear = $term['school_year'] ?? $term['academic_year_name'] ?? '2026-2027';
$prelimWeight = (float)($settings['prelim_weight'] ?? 20.00);
$midtermWeight = (float)($settings['midterm_weight'] ?? 20.00);
$semiFinalWeight = (float)($settings['semi_final_weight'] ?? 20.00);
$finalWeight = (float)($settings['final_weight'] ?? 40.00);
$activeMethod = $settings['grading_method'] ?? 'zero_based';

ob_start();
?>

<form method="POST" action="<?= url('/admin/settings') ?>" id="settingsForm" novalidate>
    <?= csrf_field() ?>

    <div class="row g-4 mb-4">
        <!-- Column 1: Academic Calendar & Formula Governance -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="h6 mb-0 fw-semibold text-dark">Academic Governance & Formula</h3>
                        <small class="text-muted">Global evaluation standards across colleges and departments.</small>
                    </div>
                    <?php if ($term): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            Active Term
                        </span>
                    <?php endif; ?>
                </div>

                <div class="card-body p-4 d-flex flex-column gap-3">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="academic_term_id" class="form-label fw-medium mb-0">Active academic term & school year <span class="text-danger">*</span></label>
                            <a href="<?= url('/admin/academic-terms') ?>" class="text-decoration-none small text-primary fw-medium">
                                Manage Terms
                            </a>
                        </div>
                        <select class="form-select" id="academic_term_id" name="academic_term_id" onchange="if(this.value){ window.location.href = '<?= url('/admin/settings') ?>?term_id=' + this.value; }" required>
                            <?php if (empty($allTerms)): ?>
                                <option value="" disabled selected>No academic terms configured</option>
                            <?php else: ?>
                                <?php foreach ($allTerms as $t): ?>
                                    <?php 
                                    $semText = match ((int)($t['semester'] ?? 1)) {
                                        1 => '1st Semester',
                                        2 => '2nd Semester',
                                        3 => 'Summer Term',
                                        default => 'Semester ' . $t['semester'],
                                    };
                                    $isSelected = ((int)($term['id'] ?? 0) === (int)$t['id']);
                                    ?>
                                    <option value="<?= (int)$t['id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($t['school_year_display']) ?> &mdash; <?= htmlspecialchars($semText) ?><?= !empty($t['is_active']) ? ' (Active)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <input type="hidden" name="academic_year" value="<?= htmlspecialchars((string)$schoolYear) ?>">
                        <div class="form-text">
                            Active term: <strong class="text-dark"><?= htmlspecialchars($semesterLabel) ?></strong> (<?= htmlspecialchars((string)$schoolYear) ?>). Applies to grading sheets and enrollment catalogs.
                        </div>
                    </div>

                    <div>
                        <label for="grading_method" class="form-label fw-medium">Grading computation method <span class="text-danger">*</span></label>
                        <select class="form-select" id="grading_method" name="grading_method" required>
                            <option value="zero_based" <?= ($activeMethod === 'zero_based') ? 'selected' : '' ?>>
                                Zero-based formula (0.00 – 100.00)
                            </option>
                            <option value="fifty_based" <?= ($activeMethod === 'fifty_based') ? 'selected' : '' ?>>
                                Fifty-based transmutation formula (50.00 – 100.00)
                            </option>
                        </select>
                        <div class="form-text">Governs how raw examination scores are converted into evaluative marks.</div>
                    </div>

                    <!-- Dynamic Formula Preview Box -->
                    <div class="p-3 rounded border bg-light" id="formulaPreviewBox">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="small fw-semibold text-dark" id="formulaTitle">Zero-Based Evaluation</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 11px;">Active Rule</span>
                        </div>
                        <code class="d-block text-primary fw-medium mb-1" id="formulaEquation" style="font-size: 13px;">
                            Percentage = (Raw Score / Total Items) × 100%
                        </code>
                        <p class="small text-muted mb-0" id="formulaNote" style="font-size: 12px;">
                            Standard absolute scale where 0 points earned equals 0.00% score.
                        </p>
                    </div>

                    <!-- Passing Benchmark Standard -->
                    <div class="p-3 rounded border" style="background-color: #f8fafc;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="d-block small fw-semibold text-dark">CHED Passing Benchmark</span>
                                <span class="small text-muted">Minimum evaluative grade required for academic credit:</span>
                            </div>
                            <span class="badge bg-secondary-subtle text-dark border px-2 py-1" style="font-size: 13px;">
                                75.00% (3.00)
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Column 2: Period Weighting Matrix (Inspired by IntelliGrade) -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="h6 mb-0 fw-semibold text-dark">Period Evaluation Weights</h3>
                        <small class="text-muted">Weight contribution of each examination period (Sum must equal 100%).</small>
                    </div>
                    <span id="weightHeaderBadge" class="badge bg-success-subtle text-success border border-success-subtle">
                        100.00%
                    </span>
                </div>

                <div class="card-body p-4 d-flex flex-column gap-3">
                    <!-- Segmented Proportional Visual Bar -->
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1 small text-muted" style="font-size: 11px;">
                            <span>Weight Allocation</span>
                            <span id="totalAllocationText">100.00% allocated</span>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 6px; overflow: hidden; background-color: #e2e8f0;">
                            <div id="barPrelim" class="progress-bar bg-primary" role="progressbar" style="width: 20%;" title="Prelim"></div>
                            <div id="barMidterm" class="progress-bar" role="progressbar" style="width: 20%; background-color: #0284c7;" title="Midterm"></div>
                            <div id="barSemiFinal" class="progress-bar" role="progressbar" style="width: 20%; background-color: #6366f1;" title="Semi-Final"></div>
                            <div id="barFinal" class="progress-bar" role="progressbar" style="width: 40%; background-color: #1e3a8a;" title="Final"></div>
                        </div>
                    </div>

                    <!-- 4 Period Inputs -->
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label for="prelim_weight" class="form-label small fw-medium text-dark mb-1">
                                Prelim period <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input 
                                    type="number" 
                                    step="0.5" 
                                    min="0" 
                                    max="100" 
                                    class="form-control weight-input text-end fw-semibold" 
                                    id="prelim_weight" 
                                    name="prelim_weight" 
                                    value="<?= number_format($prelimWeight, 2, '.', '') ?>" 
                                    required
                                >
                                <span class="input-group-text bg-light text-muted small">%</span>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <label for="midterm_weight" class="form-label small fw-medium text-dark mb-1">
                                Midterm period <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input 
                                    type="number" 
                                    step="0.5" 
                                    min="0" 
                                    max="100" 
                                    class="form-control weight-input text-end fw-semibold" 
                                    id="midterm_weight" 
                                    name="midterm_weight" 
                                    value="<?= number_format($midtermWeight, 2, '.', '') ?>" 
                                    required
                                >
                                <span class="input-group-text bg-light text-muted small">%</span>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <label for="semi_final_weight" class="form-label small fw-medium text-dark mb-1">
                                Semi-final period <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input 
                                    type="number" 
                                    step="0.5" 
                                    min="0" 
                                    max="100" 
                                    class="form-control weight-input text-end fw-semibold" 
                                    id="semi_final_weight" 
                                    name="semi_final_weight" 
                                    value="<?= number_format($semiFinalWeight, 2, '.', '') ?>" 
                                    required
                                >
                                <span class="input-group-text bg-light text-muted small">%</span>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <label for="final_weight" class="form-label small fw-medium text-dark mb-1">
                                Final examination <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input 
                                    type="number" 
                                    step="0.5" 
                                    min="0" 
                                    max="100" 
                                    class="form-control weight-input text-end fw-semibold" 
                                    id="final_weight" 
                                    name="final_weight" 
                                    value="<?= number_format($finalWeight, 2, '.', '') ?>" 
                                    required
                                >
                                <span class="input-group-text bg-light text-muted small">%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Preset Selectors -->
                    <div class="pt-2">
                        <label class="form-label small text-muted mb-1" style="font-size: 11px;">Standard Distribution Presets:</label>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 12px;" onclick="applyPreset(20, 20, 20, 40)">
                                Standard (20 / 20 / 20 / 40)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 12px;" onclick="applyPreset(25, 25, 25, 25)">
                                Balanced (25 / 25 / 25 / 25)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 12px;" onclick="applyPreset(15, 35, 15, 35)">
                                Midterm/Final (15 / 35 / 15 / 35)
                            </button>
                        </div>
                    </div>

                    <!-- Live Sum Feedback Box -->
                    <div class="p-3 rounded border mt-auto d-flex align-items-center justify-content-between" id="weightSummaryBox" style="background-color: #f8fafc;">
                        <div>
                            <span class="d-block small fw-semibold text-dark">Cumulative Total</span>
                            <span class="small text-muted" id="weightSummaryText">Weights sum to 100.00%</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="h5 mb-0 fw-bold text-dark font-monospace" id="totalWeightValue">100.00%</span>
                            <span id="validationIcon" class="text-success"><i class="bi bi-check-circle-fill"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sticky / Bottom Action Bar -->
    <div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
        <div class="card-body py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="small text-muted">
                Changes take effect immediately across all department grading sheets for active term.
            </span>
            <button type="submit" id="saveSettingsBtn" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 py-2">
                <i class="bi bi-check2"></i> Save institutional settings
            </button>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const methodSelect = document.getElementById('grading_method');
    const formulaTitle = document.getElementById('formulaTitle');
    const formulaEquation = document.getElementById('formulaEquation');
    const formulaNote = document.getElementById('formulaNote');

    const prelimInput = document.getElementById('prelim_weight');
    const midtermInput = document.getElementById('midterm_weight');
    const semiFinalInput = document.getElementById('semi_final_weight');
    const finalInput = document.getElementById('final_weight');

    const totalWeightValue = document.getElementById('totalWeightValue');
    const weightSummaryText = document.getElementById('weightSummaryText');
    const weightSummaryBox = document.getElementById('weightSummaryBox');
    const weightHeaderBadge = document.getElementById('weightHeaderBadge');
    const validationIcon = document.getElementById('validationIcon');
    const saveBtn = document.getElementById('saveSettingsBtn');
    const totalAllocationText = document.getElementById('totalAllocationText');

    const barPrelim = document.getElementById('barPrelim');
    const barMidterm = document.getElementById('barMidterm');
    const barSemiFinal = document.getElementById('barSemiFinal');
    const barFinal = document.getElementById('barFinal');

    function updateFormulaPreview() {
        if (!methodSelect) return;
        if (methodSelect.value === 'fifty_based') {
            formulaTitle.textContent = 'Fifty-Based Transmutation Evaluation';
            formulaEquation.textContent = 'Percentage = (Raw Score / Total Items) × 50 + 50%';
            formulaNote.textContent = 'Standard Philippine institutional transmutation where 0 points earned equals a base mark of 50.00%.';
        } else {
            formulaTitle.textContent = 'Zero-Based Evaluation';
            formulaEquation.textContent = 'Percentage = (Raw Score / Total Items) × 100%';
            formulaNote.textContent = 'Standard absolute scale where 0 points earned equals 0.00% score.';
        }
    }

    function calculateTotalWeights() {
        const p = parseFloat(prelimInput.value) || 0;
        const m = parseFloat(midtermInput.value) || 0;
        const s = parseFloat(semiFinalInput.value) || 0;
        const f = parseFloat(finalInput.value) || 0;

        const total = Math.round((p + m + s + f) * 100) / 100;
        totalWeightValue.textContent = total.toFixed(2) + '%';
        weightHeaderBadge.textContent = total.toFixed(2) + '%';
        totalAllocationText.textContent = total.toFixed(2) + '% allocated';

        // Update proportional bars
        barPrelim.style.width = Math.min(100, Math.max(0, p)) + '%';
        barMidterm.style.width = Math.min(100, Math.max(0, m)) + '%';
        barSemiFinal.style.width = Math.min(100, Math.max(0, s)) + '%';
        barFinal.style.width = Math.min(100, Math.max(0, f)) + '%';

        const isValid = Math.abs(total - 100.00) < 0.01;

        if (isValid) {
            weightSummaryText.textContent = 'Weights sum to exactly 100.00%';
            weightSummaryBox.style.borderColor = '#bbf7d0';
            weightSummaryBox.style.backgroundColor = '#f0fdf4';
            totalWeightValue.className = 'h5 mb-0 fw-bold text-success font-monospace';
            weightHeaderBadge.className = 'badge bg-success-subtle text-success border border-success-subtle';
            validationIcon.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i>';
            saveBtn.disabled = false;
        } else {
            weightSummaryText.textContent = 'Warning: Total must equal 100.00%';
            weightSummaryBox.style.borderColor = '#fecaca';
            weightSummaryBox.style.backgroundColor = '#fef2f2';
            totalWeightValue.className = 'h5 mb-0 fw-bold text-danger font-monospace';
            weightHeaderBadge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle';
            validationIcon.innerHTML = '<i class="bi bi-exclamation-circle-fill text-danger"></i>';
            saveBtn.disabled = true;
        }
    }

    window.applyPreset = function(p, m, s, f) {
        prelimInput.value = p.toFixed(2);
        midtermInput.value = m.toFixed(2);
        semiFinalInput.value = s.toFixed(2);
        finalInput.value = f.toFixed(2);
        calculateTotalWeights();
    };

    if (methodSelect) {
        methodSelect.addEventListener('change', updateFormulaPreview);
    }

    [prelimInput, midtermInput, semiFinalInput, finalInput].forEach(function(input) {
        if (input) {
            input.addEventListener('input', calculateTotalWeights);
            input.addEventListener('change', calculateTotalWeights);
        }
    });

    updateFormulaPreview();
    calculateTotalWeights();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
