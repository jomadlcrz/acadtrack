<?php
$pageTitle = 'Grade Review';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1 fw-semibold text-dark">Grade Review & Approval</h2>
        <p class="text-muted small mb-0">Audit instructor grading submissions, verify score distributions, and authorize official approval.</p>
    </div>
</div>

<div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h3 class="h6 mb-0 fw-semibold text-dark d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-check text-primary"></i> Pending Grade Review Queue
        </h3>
        <span class="text-muted small"><?= count($gradingSheets) ?> submissions waiting</span>
    </div>

    <?php if (empty($gradingSheets)): ?>
        <div class="card-body text-center py-5">
            <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle mb-3" style="width: 52px; height: 52px; font-size: 24px;">
                <i class="bi bi-check2-all"></i>
            </div>
            <h4 class="h6 fw-semibold text-dark mb-1">No grading sheets pending review</h4>
            <p class="text-muted small mb-0">All instructor grade sheets for the active term have been reviewed and processed.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light border-bottom">
                    <tr>
                        <th class="fw-semibold text-muted small py-3 px-3">Subject code & title</th>
                        <th class="fw-semibold text-muted small py-3 px-3" style="width: 150px;">Grading period</th>
                        <th class="fw-semibold text-muted small py-3 px-3" style="width: 180px;">Faculty instructor</th>
                        <th class="fw-semibold text-muted small py-3 px-3" style="width: 170px;">Submitted at</th>
                        <th class="fw-semibold text-muted small py-3 px-3" style="width: 140px;">Status</th>
                        <th class="fw-semibold text-muted small py-3 px-3 text-end" style="width: 200px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gradingSheets as $sheet): ?>
                    <tr>
                        <td class="px-3">
                            <div class="fw-semibold text-primary font-monospace small">
                                <?= htmlspecialchars($sheet['subject_code']) ?>
                            </div>
                            <div class="fw-semibold text-dark small">
                                <?= htmlspecialchars($sheet['subject_name']) ?>
                            </div>
                        </td>
                        <td class="px-3">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <?= htmlspecialchars($sheet['period_name']) ?>
                            </span>
                        </td>
                        <td class="px-3 small text-dark">
                            <i class="bi bi-person text-muted me-1"></i><?= htmlspecialchars($sheet['first_name'] . ' ' . $sheet['last_name']) ?>
                        </td>
                        <td class="px-3 small text-muted font-monospace">
                            <?= !empty($sheet['submitted_at']) ? htmlspecialchars(date('M d, Y • h:i A', strtotime($sheet['submitted_at']))) : 'Recently' ?>
                        </td>
                        <td class="px-3">
                            <span class="badge badge-<?= strtolower($sheet['status']) ?>">
                                <i class="bi bi-clock-history me-1"></i><?= htmlspecialchars(ucfirst(strtolower($sheet['status']))) ?>
                            </span>
                        </td>
                        <td class="px-3 text-end text-nowrap">
                            <form method="POST" action="<?= url('/dean/grade-review/approve') ?>" class="d-inline" onsubmit="return confirm('Approve this grading sheet for <?= htmlspecialchars($sheet['subject_code']) ?>?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="grading_sheet_id" value="<?= $sheet['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-success py-1 px-2 d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-check-circle"></i> Approve
                                </button>
                            </form>
                            <form method="POST" action="<?= url('/dean/grade-review/return') ?>" class="d-inline ms-1" onsubmit="return confirm('Return this grading sheet to faculty for revisions?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="grading_sheet_id" value="<?= $sheet['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-warning py-1 px-2 d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-arrow-counterclockwise"></i> Return
                                </button>
                            </form>
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
$pageTitle = 'Grade Review';
include __DIR__ . '/../../layouts/dashboard.php';
?>
