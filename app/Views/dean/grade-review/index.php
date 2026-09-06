<?php
$pageTitle = 'Grade Review & Approval';
$subtitle = 'Audit instructor grading submissions, verify score distributions, and authorize official approval.';
$headerActions = '
<div class="btn-group btn-group-sm" role="group" aria-label="Semester selection">
    <a href="' . url('/dean/grade-review?semester=1&status=' . urlencode($statusFilter ?? 'all')) . '" class="btn ' . (($selectedSemester ?? '1') === '1' ? 'btn-primary' : 'btn-outline-secondary') . '">
        1st Semester
    </a>
    <a href="' . url('/dean/grade-review?semester=2&status=' . urlencode($statusFilter ?? 'all')) . '" class="btn ' . (($selectedSemester ?? '1') === '2' ? 'btn-primary' : 'btn-outline-secondary') . '">
        2nd Semester
    </a>
</div>';
ob_start();
$statusFilter = $statusFilter ?? 'all';
?>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="card-body py-2 px-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="nav nav-pills small gap-1">
                <a href="<?= url('/dean/grade-review?status=all&semester=' . ($selectedSemester ?? '1')) ?>" class="nav-link py-1 px-3 <?= $statusFilter === 'all' ? 'active' : 'bg-light text-dark' ?>">
                    All Submissions
                </a>
                <a href="<?= url('/dean/grade-review?status=pending&semester=' . ($selectedSemester ?? '1')) ?>" class="nav-link py-1 px-3 <?= $statusFilter === 'pending' ? 'active' : 'bg-light text-dark' ?>">
                    Pending Review
                </a>
                <a href="<?= url('/dean/grade-review?status=approved&semester=' . ($selectedSemester ?? '1')) ?>" class="nav-link py-1 px-3 <?= $statusFilter === 'approved' ? 'active' : 'bg-light text-dark' ?>">
                    Approved
                </a>
                <a href="<?= url('/dean/grade-review?status=finalized&semester=' . ($selectedSemester ?? '1')) ?>" class="nav-link py-1 px-3 <?= $statusFilter === 'finalized' ? 'active' : 'bg-light text-dark' ?>">
                    Finalized
                </a>
                <a href="<?= url('/dean/grade-review?status=returned&semester=' . ($selectedSemester ?? '1')) ?>" class="nav-link py-1 px-3 <?= $statusFilter === 'returned' ? 'active' : 'bg-light text-dark' ?>">
                    Returned
                </a>
            </div>
            <span class="text-muted small">
                <?= count($gradingSheets) ?> <?= count($gradingSheets) === 1 ? 'record' : 'records' ?> found
            </span>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h6 mb-0 fw-semibold text-dark">Grading Sheets Roster</h3>
            <small class="text-muted">Submissions for <?= htmlspecialchars($academicTerm['name'] ?? 'Active Term') ?></small>
        </div>
    </div>

    <?php if (empty($gradingSheets)): ?>
        <div class="card-body text-center py-5">
            <div class="d-inline-flex align-items-center justify-content-center bg-light text-muted rounded-circle mb-3" style="width: 52px; height: 52px; font-size: 24px;">
                <i class="bi bi-check2-all"></i>
            </div>
            <h4 class="h6 fw-semibold text-dark mb-1">No grading sheets found</h4>
            <p class="text-muted small mb-0">There are no grading sheets matching the current status filter for <?= htmlspecialchars($academicTerm['name'] ?? 'this semester') ?>.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light border-bottom">
                    <tr>
                        <th class="fw-semibold text-muted small py-3 px-3">Subject code &amp; title</th>
                        <th class="fw-semibold text-muted small py-3 px-3" style="width: 140px;">Grading period</th>
                        <th class="fw-semibold text-muted small py-3 px-3" style="width: 170px;">Faculty instructor</th>
                        <th class="fw-semibold text-muted small py-3 px-3" style="width: 150px;">Submitted at</th>
                        <th class="fw-semibold text-muted small py-3 px-3" style="width: 130px;">Status</th>
                        <th class="fw-semibold text-muted small py-3 px-3 text-end" style="width: 260px;">Actions</th>
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
                            <?= !empty($sheet['submitted_at']) ? htmlspecialchars(date('M d, Y • h:i A', strtotime($sheet['submitted_at']))) : '—' ?>
                        </td>
                        <td class="px-3">
                            <span class="badge <?= match($sheet['status']) {
                                'APPROVED' => 'bg-success-subtle text-success border border-success-subtle',
                                'FINALIZED' => 'bg-dark-subtle text-dark border border-secondary',
                                'RETURNED' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                default => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                            } ?>">
                                <?= htmlspecialchars($sheet['status']) ?>
                            </span>
                        </td>
                        <td class="px-3 text-end text-nowrap">
                            <a href="<?= url('/dean/grade-review/' . $sheet['id']) ?>" class="btn btn-sm btn-outline-primary py-1 px-2 d-inline-flex align-items-center gap-1">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <a href="<?= url('/dean/grade-review/' . $sheet['id'] . '/print') ?>" target="_blank" class="btn btn-sm btn-outline-secondary py-1 px-2 d-inline-flex align-items-center gap-1 ms-1">
                                <i class="bi bi-printer"></i> Print
                            </a>

                            <?php if (in_array($sheet['status'], ['SUBMITTED', 'UNDER_REVIEW'])): ?>
                                <form method="POST" action="<?= url('/dean/grade-review/approve') ?>" class="d-inline ms-1" onsubmit="return confirm('Approve this grading sheet for <?= htmlspecialchars($sheet['subject_code']) ?>?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="grading_sheet_id" value="<?= $sheet['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-success py-1 px-2 d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-check-circle"></i> Approve
                                    </button>
                                </form>
                            <?php elseif ($sheet['status'] === 'APPROVED'): ?>
                                <button type="button" class="btn btn-sm btn-dark py-1 px-2 d-inline-flex align-items-center gap-1 ms-1" data-bs-toggle="modal" data-bs-target="#confirmModal<?= $sheet['id'] ?>">
                                    <i class="bi bi-shield-check"></i> Confirm
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php foreach ($gradingSheets as $sheet): ?>
        <?php if ($sheet['status'] === 'APPROVED'): ?>
        <div class="modal fade" id="confirmModal<?= $sheet['id'] ?>" tabindex="-1" aria-labelledby="confirmModalLabel<?= $sheet['id'] ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form method="POST" action="<?= url('/dean/grade-review/confirm') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="grading_sheet_id" value="<?= $sheet['id'] ?>">
                        <div class="modal-header border-bottom">
                            <h5 class="modal-title h6 fw-semibold mb-0" id="confirmModalLabel<?= $sheet['id'] ?>">
                                Confirm Grading Sheet: <?= htmlspecialchars($sheet['subject_code']) ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small mb-3">Confirming will formally finalize and permanently lock this grading sheet. No further grade revisions can be recorded once confirmed.</p>
                            <div class="mb-2">
                                <label for="remarks_<?= $sheet['id'] ?>" class="form-label small fw-semibold">Institutional remarks / sign-off notes</label>
                                <textarea class="form-control" id="remarks_<?= $sheet['id'] ?>" name="remarks" rows="3" placeholder="Enter confirmation remarks (e.g. Official semester rating confirmed by the Dean's Office.)"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-top py-3 d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-dark d-inline-flex align-items-center gap-2">
                                <i class="bi bi-check2"></i> Confirm &amp; finalize
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>

    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>

