<?php
$pageTitle = 'Institutional Settings';
$subtitle = 'Configure institutional grading defaults and global academic calendar parameters.';
ob_start();
?>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; max-width: 760px;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h6 mb-0 fw-semibold text-dark">Academic Governance Configuration</h3>
            <small class="text-muted">Global defaults apply across all colleges, departments, and course offerings.</small>
        </div>
    </div>

    <form method="POST" action="<?= url('/admin/settings') ?>" novalidate>
        <?= csrf_field() ?>

        <div class="card-body p-4">
            <div class="mb-3">
                <label for="grading_method" class="form-label">Default grading method <span class="text-danger">*</span></label>
                <select class="form-select" id="grading_method" name="grading_method" required>
                    <option value="zero_based" <?= (($settings['grading_method'] ?? 'zero_based') === 'zero_based') ? 'selected' : '' ?>>
                        Zero-based formula: (Raw score / Total items) × 100
                    </option>
                    <option value="fifty_based" <?= (($settings['grading_method'] ?? '') === 'fifty_based') ? 'selected' : '' ?>>
                        Fifty-based formula: (Raw score / Total items) × 50 + 50
                    </option>
                </select>
                <div class="form-text">Determines how computed percentages are calculated prior to 1.00 – 5.00 point scale translation.</div>
            </div>

            <div class="mb-3">
                <label for="academic_year" class="form-label">Active academic year <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?= htmlspecialchars($term['academic_year_name'] ?? '2026-2027') ?>" placeholder="e.g., 2026-2027" required>
                <div class="form-text">Specifies the global academic year applied to active grading sheets and enrollment matrices.</div>
            </div>
        </div>

        <div class="card-footer bg-light py-3 border-top d-flex justify-content-end align-items-center gap-2">
            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                <i class="bi bi-check2"></i> Save settings
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
