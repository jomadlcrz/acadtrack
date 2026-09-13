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
$metrics = $metrics ?? [
    'total' => count($gradingSheets),
    'pending' => 0,
    'approved' => 0,
    'finalized' => 0,
    'returned' => 0,
];
?>

<!-- Metric KPI Cards (Matching Dashboard Standard) -->
<div class="dashboard-stats mb-4">
    <div class="stat-card">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Total submissions</span>
                <div class="stat-icon stat-icon-blue"><i class="bi bi-file-earmark-spreadsheet-fill"></i></div>
            </div>
            <div class="stat-value tabular-nums" id="statTotalSubmissions"><?= $metrics['total'] ?></div>
        </div>
        <span class="stat-subtext">Course grade sheets</span>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Pending review</span>
                <div class="stat-icon stat-icon-amber"><i class="bi bi-hourglass-split"></i></div>
            </div>
            <div class="stat-value tabular-nums <?= ($metrics['pending'] ?? 0) > 0 ? 'text-amber' : '' ?>" id="statPendingReview">
                <?= $metrics['pending'] ?>
            </div>
        </div>
        <span class="stat-subtext">Awaiting Dean action</span>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Approved</span>
                <div class="stat-icon stat-icon-green"><i class="bi bi-check-circle-fill"></i></div>
            </div>
            <div class="stat-value tabular-nums text-success" id="statApproved">
                <?= $metrics['approved'] ?>
            </div>
        </div>
        <span class="stat-subtext">Ready for confirmation</span>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Finalized</span>
                <div class="stat-icon stat-icon-purple"><i class="bi bi-shield-lock-fill"></i></div>
            </div>
            <div class="stat-value tabular-nums" id="statFinalized">
                <?= $metrics['finalized'] ?>
            </div>
        </div>
        <span class="stat-subtext">Locked &amp; permanently posted</span>
    </div>
</div>

<!-- Search & Filtering Controls Bar -->
<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="card-body p-3">
        <div class="row g-3 align-items-center justify-content-between">
            <div class="col-12 col-xl-auto">
                <div class="nav nav-pills small gap-1 flex-wrap">
                    <a href="<?= url('/dean/grade-review?status=all&semester=' . ($selectedSemester ?? '1')) ?>" 
                       class="nav-link py-1.5 px-3 d-inline-flex align-items-center gap-1.5 <?= $statusFilter === 'all' ? 'active' : 'bg-light text-dark' ?>">
                        All Submissions
                        <span class="badge rounded-pill <?= $statusFilter === 'all' ? 'bg-white text-primary' : 'bg-secondary-subtle text-secondary' ?>">
                            <?= $metrics['total'] ?>
                        </span>
                    </a>
                    <a href="<?= url('/dean/grade-review?status=pending&semester=' . ($selectedSemester ?? '1')) ?>" 
                       class="nav-link py-1.5 px-3 d-inline-flex align-items-center gap-1.5 <?= $statusFilter === 'pending' ? 'active' : 'bg-light text-dark' ?>">
                        Pending Review
                        <span class="badge rounded-pill <?= $statusFilter === 'pending' ? 'bg-white text-primary' : 'bg-warning-subtle text-warning-emphasis' ?>">
                            <?= $metrics['pending'] ?>
                        </span>
                    </a>
                    <a href="<?= url('/dean/grade-review?status=approved&semester=' . ($selectedSemester ?? '1')) ?>" 
                       class="nav-link py-1.5 px-3 d-inline-flex align-items-center gap-1.5 <?= $statusFilter === 'approved' ? 'active' : 'bg-light text-dark' ?>">
                        Approved
                        <span class="badge rounded-pill <?= $statusFilter === 'approved' ? 'bg-white text-primary' : 'bg-success-subtle text-success' ?>">
                            <?= $metrics['approved'] ?>
                        </span>
                    </a>
                    <a href="<?= url('/dean/grade-review?status=finalized&semester=' . ($selectedSemester ?? '1')) ?>" 
                       class="nav-link py-1.5 px-3 d-inline-flex align-items-center gap-1.5 <?= $statusFilter === 'finalized' ? 'active' : 'bg-light text-dark' ?>">
                        Finalized
                        <span class="badge rounded-pill <?= $statusFilter === 'finalized' ? 'bg-white text-primary' : 'bg-dark-subtle text-dark' ?>">
                            <?= $metrics['finalized'] ?>
                        </span>
                    </a>
                    <?php if (($metrics['returned'] ?? 0) > 0 || $statusFilter === 'returned'): ?>
                    <a href="<?= url('/dean/grade-review?status=returned&semester=' . ($selectedSemester ?? '1')) ?>" 
                       class="nav-link py-1.5 px-3 d-inline-flex align-items-center gap-1.5 <?= $statusFilter === 'returned' ? 'active' : 'bg-light text-dark' ?>">
                        Returned
                        <span class="badge rounded-pill <?= $statusFilter === 'returned' ? 'bg-white text-primary' : 'bg-danger-subtle text-danger' ?>">
                            <?= $metrics['returned'] ?>
                        </span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-12 col-xl d-flex flex-wrap align-items-center justify-content-xl-end gap-3">
                <div class="position-relative flex-grow-1" style="max-width: 380px; min-width: 240px;">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 12px;"></i>
                    <input type="text" 
                           id="gradeReviewSearch" 
                           class="form-control form-control-sm ps-5" 
                           placeholder="Search course code, title, or instructor..." 
                           autocomplete="off">
                </div>

                <div class="text-muted small fw-medium text-nowrap" id="visibleCounter">
                    <?= count($gradingSheets) ?> <?= count($gradingSheets) === 1 ? 'record' : 'records' ?> found
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grading Sheets Roster Card -->
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
            <table class="table table-hover align-middle mb-0" id="gradeReviewTable">
                <thead class="bg-white border-bottom">
                    <tr>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Subject Code &amp; Title</th>
                        <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 140px; font-size: 11px;">Grading Period</th>
                        <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="width: 220px; font-size: 11px;">Faculty Instructor</th>
                        <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="width: 170px; font-size: 11px;">Submitted At</th>
                        <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 130px; font-size: 11px;">Status</th>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="width: 240px; font-size: 11px;">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y" id="gradeReviewTableBody">
                    <?php foreach ($gradingSheets as $sheet): ?>
                    <?php
                        $code = $sheet['subject_code'] ?? $sheet['code'] ?? '';
                        $instructorName = trim(($sheet['first_name'] ?? '') . ' ' . ($sheet['last_name'] ?? ''));
                        $searchHaystack = strtolower($code . ' ' . ($sheet['subject_name'] ?? '') . ' ' . $instructorName . ' ' . ($sheet['period_name'] ?? '') . ' ' . ($sheet['status'] ?? ''));
                        $initials = strtoupper(substr($sheet['first_name'] ?? 'F', 0, 1) . substr($sheet['last_name'] ?? 'I', 0, 1));
                    ?>
                    <tr class="grade-review-row" data-search="<?= htmlspecialchars($searchHaystack) ?>">
                        <td class="py-2.5 px-4">
                            <div class="fw-bold text-primary font-monospace fs-7 mb-0.5">
                                <?= htmlspecialchars($code) ?>
                            </div>
                            <div class="fw-semibold text-dark fs-7">
                                <?= htmlspecialchars($sheet['subject_name']) ?>
                            </div>
                        </td>
                        <td class="py-2.5 px-3 text-center">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fs-8 fw-semibold">
                                <?= htmlspecialchars($sheet['period_name']) ?>
                            </span>
                        </td>
                        <td class="py-2.5 px-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-circle-sm bg-primary-subtle text-primary fw-bold flex-shrink-0" style="width: 28px; height: 28px; font-size: 11px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">
                                    <?= htmlspecialchars($initials) ?>
                                </div>
                                <div class="overflow-hidden">
                                    <div class="fw-medium text-dark fs-7 text-truncate"><?= htmlspecialchars($instructorName) ?></div>
                                    <?php if (!empty($sheet['email'])): ?>
                                        <div class="text-muted text-truncate" style="font-size: 11px;"><?= htmlspecialchars($sheet['email']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="py-2.5 px-3">
                            <?php if (!empty($sheet['submitted_at'])): ?>
                                <div class="text-dark fs-7 font-monospace fw-medium">
                                    <?= htmlspecialchars(date('M d, Y', strtotime($sheet['submitted_at']))) ?>
                                </div>
                                <div class="text-muted font-monospace" style="font-size: 11px;">
                                    <?= htmlspecialchars(date('h:i A', strtotime($sheet['submitted_at']))) ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-2.5 px-3 text-center">
                            <span class="badge <?= match($sheet['status']) {
                                'APPROVED' => 'bg-success-subtle text-success border border-success-subtle',
                                'FINALIZED' => 'bg-dark-subtle text-dark border border-secondary',
                                'RETURNED' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                default => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                            } ?> px-2.5 py-1 fs-8">
                                <?= htmlspecialchars($sheet['status']) ?>
                            </span>
                        </td>
                        <td class="py-2.5 px-4 text-end text-nowrap">
                            <a href="<?= url('/dean/grade-review/' . $sheet['id']) ?>" class="btn btn-sm btn-outline-primary py-1 px-2.5 d-inline-flex align-items-center gap-1 fs-7">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <a href="<?= url('/dean/grade-review/' . $sheet['id'] . '/print') ?>" target="_blank" class="btn btn-sm btn-outline-secondary py-1 px-2.5 d-inline-flex align-items-center gap-1 ms-1 fs-7">
                                <i class="bi bi-printer"></i> Print
                            </a>

                            <?php if (in_array($sheet['status'], ['SUBMITTED', 'UNDER_REVIEW'])): ?>
                                <form method="POST" action="<?= url('/dean/grade-review/approve') ?>" class="d-inline ms-1" onsubmit="return confirm('Approve this grading sheet for <?= htmlspecialchars($code) ?>?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="grading_sheet_id" value="<?= $sheet['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-success py-1 px-2.5 d-inline-flex align-items-center gap-1 fs-7">
                                        <i class="bi bi-check-circle"></i> Approve
                                    </button>
                                </form>
                            <?php elseif ($sheet['status'] === 'APPROVED'): ?>
                                <button type="button" class="btn btn-sm btn-dark py-1 px-2.5 d-inline-flex align-items-center gap-1 ms-1 fs-7" data-bs-toggle="modal" data-bs-target="#confirmModal<?= $sheet['id'] ?>">
                                    <i class="bi bi-shield-check"></i> Confirm
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <!-- Empty Search Result Fallback Row -->
                    <tr id="noResultsRow" style="display: none;">
                        <td colspan="6" class="text-center py-5">
                            <div class="d-inline-flex align-items-center justify-content-center bg-light text-muted rounded-circle mb-2" style="width: 44px; height: 44px; font-size: 20px;">
                                <i class="bi bi-search"></i>
                            </div>
                            <div class="fw-semibold text-dark">No matching submissions found</div>
                            <div class="text-muted small">Try adjusting your search keywords or clear the filter.</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php foreach ($gradingSheets as $sheet): ?>
        <?php if ($sheet['status'] === 'APPROVED'): ?>
        <div class="modal fade" id="confirmModal<?= $sheet['id'] ?>" tabindex="-1" aria-labelledby="confirmModalLabel<?= $sheet['id'] ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow" style="border-radius: 8px;">
                    <form method="POST" action="<?= url('/dean/grade-review/confirm') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="grading_sheet_id" value="<?= $sheet['id'] ?>">
                        <div class="modal-header border-bottom py-3">
                            <h5 class="modal-title h6 fw-semibold mb-0 d-flex align-items-center gap-2" id="confirmModalLabel<?= $sheet['id'] ?>">
                                <i class="bi bi-shield-check text-primary"></i>
                                Confirm Grading Sheet: <?= htmlspecialchars($sheet['subject_code'] ?? $sheet['code'] ?? '') ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="alert alert-warning d-flex align-items-start gap-2 mb-3 py-2 px-3 small border-0 bg-warning-subtle text-warning-emphasis">
                                <i class="bi bi-exclamation-triangle-fill fs-6 mt-0.5 flex-shrink-0"></i>
                                <div>Confirming will formally finalize and permanently lock this grading sheet. No further grade revisions can be recorded once confirmed.</div>
                            </div>
                            <div class="mb-2">
                                <label for="remarks_<?= $sheet['id'] ?>" class="form-label small fw-semibold text-dark">Institutional remarks / sign-off notes</label>
                                <textarea class="form-control" id="remarks_<?= $sheet['id'] ?>" name="remarks" rows="3" placeholder="Enter confirmation remarks (e.g. Official semester rating confirmed by the Dean's Office.)"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-top py-3 d-flex justify-content-end gap-2 bg-light">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-dark d-inline-flex align-items-center gap-1.5 shadow-sm">
                                <i class="bi bi-shield-check"></i> Confirm &amp; Finalize
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('gradeReviewSearch');
    const rows = document.querySelectorAll('.grade-review-row');
    const visibleCounter = document.getElementById('visibleCounter');
    const noResultsRow = document.getElementById('noResultsRow');

    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();
        let visibleCount = 0;

        rows.forEach(function (row) {
            const searchData = row.getAttribute('data-search') || '';
            if (!query || searchData.includes(query)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (visibleCounter) {
            visibleCounter.textContent = visibleCount + (visibleCount === 1 ? ' record found' : ' records found');
        }

        if (noResultsRow) {
            noResultsRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
