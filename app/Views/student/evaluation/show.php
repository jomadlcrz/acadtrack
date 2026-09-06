<?php
$pageTitle = 'Academic Evaluation';
$subtitle = 'Official curricular evaluation, weighted averages, and academic standing.';
$headerActions = '<a href="' . url('/student/grades') . '" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"><i class="bi bi-arrow-left"></i> Back to grades</a>';
ob_start();
?>

<div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h3 class="h6 mb-0 fw-semibold text-dark d-flex align-items-center gap-2">
            <i class="bi bi-award text-primary"></i> Curriculum Evaluation Matrix
        </h3>
        <span class="badge badge-student">Academic standing audit</span>
    </div>

    <?php if (empty($evaluations)): ?>
        <div class="card-body text-center py-5">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-3" style="width: 52px; height: 52px; font-size: 24px;">
                <i class="bi bi-mortarboard"></i>
            </div>
            <h4 class="h6 fw-semibold text-dark mb-1">No evaluation records available</h4>
            <p class="text-muted small mb-0">Finalized course evaluations will appear here once term grading is officially concluded.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light border-bottom">
                    <tr>
                        <th class="fw-semibold text-muted small py-3 px-3">Subject code & title</th>
                        <th class="fw-semibold text-muted small py-3 px-3 text-center" style="width: 160px;">Computed average</th>
                        <th class="fw-semibold text-muted small py-3 px-3 text-center" style="width: 150px;">Status</th>
                        <th class="fw-semibold text-muted small py-3 px-3" style="width: 200px;">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($evaluations as $subjectCode => $eval): ?>
                    <?php 
                        $status = strtoupper($eval['status'] ?? '');
                        $isPassed = $status === 'PASSED';
                    ?>
                    <tr>
                        <td class="px-3">
                            <div class="fw-semibold text-primary font-monospace small">
                                <?= htmlspecialchars($subjectCode) ?>
                            </div>
                            <div class="fw-semibold text-dark small">
                                <?= htmlspecialchars($eval['subject_name']) ?>
                            </div>
                        </td>
                        <td class="px-3 text-center font-monospace fw-semibold fs-6">
                            <?= number_format((float)$eval['average'], 2) ?>
                        </td>
                        <td class="px-3 text-center">
                            <span class="badge badge-<?= $isPassed ? 'approved' : 'returned' ?>">
                                <i class="bi bi-<?= $isPassed ? 'check-circle' : 'x-circle' ?> me-1"></i>
                                <?= htmlspecialchars($eval['status']) ?>
                            </span>
                        </td>
                        <td class="px-3 small text-secondary">
                            <?= htmlspecialchars($eval['remarks'] ?? '—') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
