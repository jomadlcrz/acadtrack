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
$selectedSemester = (string) ($selectedSemester ?? '1');
$metrics = $metrics ?? [
    'total' => count($gradingSheets),
    'pending' => 0,
    'approved' => 0,
    'finalized' => 0,
    'returned' => 0,
];
$totalRecords = (int) ($pagination['total'] ?? count($gradingSheets));

$tabs = [
    'all' => ['label' => 'All', 'count' => $metrics['total']],
    'pending' => ['label' => 'Awaiting Review', 'count' => $metrics['pending']],
    'approved' => ['label' => 'Approved', 'count' => $metrics['approved']],
    'finalized' => ['label' => 'Finalized', 'count' => $metrics['finalized']],
];
if (($metrics['returned'] ?? 0) > 0 || $statusFilter === 'returned') {
    $tabs['returned'] = ['label' => 'Returned', 'count' => $metrics['returned'] ?? 0];
}

// Display label, badge class and icon per grading sheet status
$statusView = static fn (string $status): array => match ($status) {
    'APPROVED' => ['Approved', 'badge-approved', 'bi-check-circle-fill'],
    'FINALIZED' => ['Finalized', 'badge-finalized', 'bi-lock-fill'],
    'RETURNED' => ['Returned', 'badge-returned', 'bi-arrow-return-left'],
    default => ['Awaiting review', 'badge-submitted', 'bi-hourglass-split'],
};
?>

<!-- Summary -->
<div class="summary-strip mb-4">
    <div class="summary-cell">
        <div class="summary-label">Total submissions</div>
        <div class="summary-value" id="statTotalSubmissions"><?= (int) $metrics['total'] ?></div>
        <div class="summary-note">Course grade sheets</div>
    </div>
    <div class="summary-cell">
        <div class="summary-label">Awaiting review</div>
        <div class="summary-value <?= ($metrics['pending'] ?? 0) > 0 ? 'is-attention' : '' ?>" id="statPendingReview"><?= (int) $metrics['pending'] ?></div>
        <div class="summary-note">Needs Dean action</div>
    </div>
    <div class="summary-cell">
        <div class="summary-label">Approved</div>
        <div class="summary-value" id="statApproved"><?= (int) $metrics['approved'] ?></div>
        <div class="summary-note">Ready for confirmation</div>
    </div>
    <div class="summary-cell">
        <div class="summary-label">Finalized</div>
        <div class="summary-value" id="statFinalized"><?= (int) $metrics['finalized'] ?></div>
        <div class="summary-note">Locked and posted</div>
    </div>
</div>

<!-- Grading Sheets Queue -->
<div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="card-header bg-white py-3 border-bottom-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3 class="h6 mb-0 fw-semibold text-dark">Grading Sheets</h3>
            <small class="text-muted"><?= htmlspecialchars($academicTerm['name'] ?? 'Active Term') ?></small>
        </div>
        <div class="queue-count" id="visibleCounter">
            <?= $totalRecords ?> <?= $totalRecords === 1 ? 'record' : 'records' ?>
        </div>
    </div>

    <div class="queue-toolbar">
        <nav class="queue-tabs" aria-label="Filter by status">
            <?php foreach ($tabs as $key => $tab): ?>
                <a href="<?= url('/dean/grade-review?status=' . $key . '&semester=' . $selectedSemester) ?>"
                   class="queue-tab <?= $statusFilter === $key ? 'is-active' : '' ?>"
                   <?= $statusFilter === $key ? 'aria-current="page"' : '' ?>>
                    <?= htmlspecialchars($tab['label']) ?>
                    <span class="queue-tab-count"><?= (int) $tab['count'] ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <form method="GET" action="<?= url('/dean/grade-review') ?>" class="queue-search">
            <input type="hidden" name="semester" value="<?= htmlspecialchars($selectedSemester) ?>">
            <input type="hidden" name="status" value="<?= htmlspecialchars((string) $statusFilter) ?>">
            <i class="bi bi-search"></i>
            <input type="text"
                   id="gradeReviewSearch"
                   name="search"
                   class="form-control form-control-sm"
                   placeholder="Search course, title, or instructor"
                   value="<?= htmlspecialchars($currentSearch ?? '') ?>"
                   autocomplete="off">
        </form>
    </div>

    <?php if (empty($gradingSheets)): ?>
        <?php
        $icon = 'bi-check2-all';
        $iconColor = 'blue';
        $title = 'No grading sheets found';
        $message = 'There are no grading sheets matching the current status filter for ' . ($academicTerm['name'] ?? 'this semester') . '.';
        include __DIR__ . '/../../components/empty-state.php';
        ?>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="gradeReviewTable">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Instructor</th>
                        <th>Period</th>
                        <th>Submitted</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody id="gradeReviewTableBody">
                    <?php foreach ($gradingSheets as $sheet): ?>
                    <?php
                        $code = $sheet['subject_code'] ?? $sheet['code'] ?? '';
                        $instructorName = trim(($sheet['first_name'] ?? '') . ' ' . ($sheet['last_name'] ?? ''));
                        $status = (string) ($sheet['status'] ?? '');
                        [$statusLabel, $statusClass, $statusIcon] = $statusView($status);
                        $searchHaystack = strtolower($code . ' ' . ($sheet['subject_name'] ?? '') . ' ' . $instructorName . ' ' . ($sheet['period_name'] ?? '') . ' ' . $statusLabel);
                        $isPending = in_array($status, ['SUBMITTED', 'UNDER_REVIEW'], true);
                    ?>
                    <tr class="grade-review-row" data-search="<?= htmlspecialchars($searchHaystack) ?>">
                        <td>
                            <div class="queue-code"><?= htmlspecialchars($code) ?></div>
                            <div class="queue-title"><?= htmlspecialchars($sheet['subject_name'] ?? '') ?></div>
                        </td>
                        <td>
                            <div class="text-dark text-truncate"><?= htmlspecialchars($instructorName) ?></div>
                            <?php if (!empty($sheet['email'])): ?>
                                <div class="queue-meta text-truncate"><?= htmlspecialchars($sheet['email']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-dark"><?= htmlspecialchars($sheet['period_name'] ?? '') ?></td>
                        <td>
                            <?php if (!empty($sheet['submitted_at'])): ?>
                                <div class="text-dark tabular-nums"><?= htmlspecialchars(date('M d, Y', strtotime($sheet['submitted_at']))) ?></div>
                                <div class="queue-meta tabular-nums"><?= htmlspecialchars(date('h:i A', strtotime($sheet['submitted_at']))) ?></div>
                            <?php else: ?>
                                <span class="text-muted">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $statusClass ?> d-inline-flex align-items-center gap-1">
                                <i class="bi <?= $statusIcon ?>"></i> <?= htmlspecialchars($statusLabel) ?>
                            </span>
                        </td>
                        <td>
                            <div class="queue-actions">
                                <?php if ($isPending): ?>
                                    <a class="btn btn-sm btn-primary" href="<?= url('/dean/grade-review/' . $sheet['id']) ?>">Review</a>
                                <?php elseif ($status === 'APPROVED'): ?>
                                    <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#confirmModal<?= $sheet['id'] ?>">Confirm</button>
                                <?php else: ?>
                                    <a class="btn btn-sm btn-outline-secondary" href="<?= url('/dean/grade-review/' . $sheet['id']) ?>">View</a>
                                <?php endif; ?>

                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-action-trigger" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false" title="More actions">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end action-dropdown-menu shadow-sm">
                                        <?php if ($isPending || $status === 'APPROVED'): ?>
                                            <li>
                                                <a class="dropdown-item" href="<?= url('/dean/grade-review/' . $sheet['id']) ?>">
                                                    <i class="bi bi-eye text-primary"></i> View details
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        <li>
                                            <a class="dropdown-item" href="<?= url('/dean/grade-review/' . $sheet['id'] . '/print') ?>" target="_blank">
                                                <i class="bi bi-printer text-muted"></i> Print sheet
                                            </a>
                                        </li>
                                        <?php if ($isPending): ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="<?= url('/dean/grade-review/approve') ?>" class="m-0" onsubmit="return confirm('Approve this grading sheet for <?= htmlspecialchars($code) ?>?');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="grading_sheet_id" value="<?= $sheet['id'] ?>">
                                                    <button type="submit" class="dropdown-item text-success">
                                                        <i class="bi bi-check-circle"></i> Approve sheet
                                                    </button>
                                                </form>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <!-- Empty Search Result Fallback Row -->
                    <tr id="noResultsRow" style="display: none;">
                        <td colspan="6" class="p-0">
                            <?php
                            $icon = 'bi-search';
                            $title = 'No matching submissions found';
                            $message = 'Try adjusting your search keywords or clear the filter.';
                            include __DIR__ . '/../../components/empty-state.php';
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php if (!empty($gradingSheets)): ?>
            <?php include __DIR__ . '/../../components/pagination.php'; ?>
        <?php endif; ?>

        <?php foreach ($gradingSheets as $sheet): ?>
        <?php if ($sheet['status'] === 'APPROVED'): ?>
        <div class="modal fade" id="confirmModal<?= $sheet['id'] ?>" tabindex="-1" aria-labelledby="confirmModalLabel<?= $sheet['id'] ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="<?= url('/dean/grade-review/confirm') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="grading_sheet_id" value="<?= $sheet['id'] ?>">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmModalLabel<?= $sheet['id'] ?>">
                                <i class="bi bi-shield-check text-primary"></i>
                                Confirm Grading Sheet: <?= htmlspecialchars($sheet['subject_code'] ?? $sheet['code'] ?? '') ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning d-flex align-items-start gap-2 mb-3 py-2 px-3 small border-0 bg-warning-subtle text-warning-emphasis">
                                <i class="bi bi-exclamation-triangle-fill fs-6 mt-0.5 flex-shrink-0"></i>
                                <div>Confirming will formally finalize and permanently lock this grading sheet. No further grade revisions can be recorded once confirmed.</div>
                            </div>
                            <div class="mb-2">
                                <label for="remarks_<?= $sheet['id'] ?>" class="form-label small fw-semibold text-dark">Institutional remarks / sign-off notes</label>
                                <textarea class="form-control" id="remarks_<?= $sheet['id'] ?>" name="remarks" rows="3" placeholder="Enter confirmation remarks (e.g. Official semester rating confirmed by the Dean's Office.)"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-dark d-inline-flex align-items-center gap-1.5 shadow-sm">
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
            visibleCounter.textContent = visibleCount + (visibleCount === 1 ? ' record' : ' records');
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
