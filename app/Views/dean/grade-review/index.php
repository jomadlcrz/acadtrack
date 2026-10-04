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
$groups = $groups ?? [];
$periods = $periods ?? [];
$statusFilter = $statusFilter ?? 'all';
$selectedSemester = (string) ($selectedSemester ?? '1');
$periodParam = (string) ($periodParam ?? 'all');
$currentSearch = (string) ($currentSearch ?? '');
$metrics = $metrics ?? ['total' => 0, 'pending' => 0, 'approved' => 0, 'finalized' => 0, 'returned' => 0];
$totalGroups = (int) ($pagination['total'] ?? count($groups));
$periodCount = max(1, count($periods));

$periodName = 'All periods';
foreach ($periods as $p) {
    if ((string) $p['id'] === $periodParam) {
        $periodName = (string) $p['name'];
    }
}

// Keep every filter when switching tabs
$filterUrl = static function (array $override = []) use ($selectedSemester, $statusFilter, $periodParam, $currentSearch): string {
    $query = array_filter(
        array_merge(['semester' => $selectedSemester, 'status' => $statusFilter, 'period' => $periodParam, 'search' => $currentSearch], $override),
        static fn ($v): bool => $v !== null && $v !== ''
    );
    return url('/dean/grade-review') . '?' . http_build_query($query);
};

$tabs = [
    'all' => ['label' => 'All', 'count' => $metrics['total']],
    'pending' => ['label' => 'Awaiting Review', 'count' => $metrics['pending']],
    'approved' => ['label' => 'Approved', 'count' => $metrics['approved']],
    'finalized' => ['label' => 'Finalized', 'count' => $metrics['finalized']],
];
if (($metrics['returned'] ?? 0) > 0 || $statusFilter === 'returned') {
    $tabs['returned'] = ['label' => 'Returned', 'count' => $metrics['returned'] ?? 0];
}

$approvedSheets = [];
foreach ($groups as $g) {
    foreach ($g['sheets'] as $sh) {
        if (($sh['status'] ?? '') === 'APPROVED') {
            $approvedSheets[] = $sh;
        }
    }
}
?>

<!-- Summary -->
<div class="summary-strip mb-4">
    <div class="summary-cell">
        <div class="summary-label">Total submissions</div>
        <div class="summary-value" id="statTotalSubmissions"><?= (int) $metrics['total'] ?></div>
        <div class="summary-note">Grading sheets &bull; <?= htmlspecialchars($periodName) ?></div>
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
            <?= $totalGroups ?> <?= $totalGroups === 1 ? 'course' : 'courses' ?>
        </div>
    </div>

    <div class="queue-toolbar">
        <nav class="queue-tabs" aria-label="Filter by status">
            <?php foreach ($tabs as $key => $tab): ?>
                <a href="<?= $filterUrl(['status' => $key]) ?>"
                   class="queue-tab <?= $statusFilter === $key ? 'is-active' : '' ?>"
                   <?= $statusFilter === $key ? 'aria-current="page"' : '' ?>>
                    <?= htmlspecialchars($tab['label']) ?>
                    <span class="queue-tab-count"><?= (int) $tab['count'] ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <form method="GET" action="<?= url('/dean/grade-review') ?>" class="queue-filters">
            <input type="hidden" name="semester" value="<?= htmlspecialchars($selectedSemester) ?>">
            <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
            <select name="period" class="form-select form-select-sm queue-period" aria-label="Grading period" onchange="this.form.submit()">
                <option value="all" <?= $periodParam === 'all' ? 'selected' : '' ?>>All periods</option>
                <?php foreach ($periods as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= (string) $p['id'] === $periodParam ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['name']) ?><?= (int) ($p['is_current'] ?? 0) === 1 ? ' (current)' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="queue-search">
                <i class="bi bi-search"></i>
                <input type="text"
                       id="gradeReviewSearch"
                       name="search"
                       class="form-control form-control-sm"
                       placeholder="Search course, title, or instructor"
                       value="<?= htmlspecialchars($currentSearch) ?>"
                       autocomplete="off">
            </div>
        </form>
    </div>

    <?php if (empty($groups)): ?>
        <?php
        $icon = 'bi-check2-all';
        $iconColor = 'blue';
        $title = 'No grading sheets found';
        $message = 'There are no grading sheets matching the current filters for ' . ($academicTerm['name'] ?? 'this semester') . '.';
        include __DIR__ . '/../../components/empty-state.php';
        ?>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="gradeReviewTable">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Instructor</th>
                        <?php foreach ($periods as $p): ?>
                            <th class="<?= (string) $p['id'] === $periodParam ? 'queue-period-active' : '' ?>"><?= htmlspecialchars($p['name']) ?></th>
                        <?php endforeach; ?>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody id="gradeReviewTableBody">
                    <?php foreach ($groups as $g): ?>
                    <?php
                        $instructorName = trim(($g['first_name'] ?? '') . ' ' . ($g['last_name'] ?? ''));
                        $searchHaystack = strtolower(($g['subject_code'] ?? '') . ' ' . ($g['subject_name'] ?? '') . ' ' . $instructorName);

                        // Next thing the Dean should do for this course
                        $pendingSheet = $approvedSheet = $lastSheet = null;
                        foreach ($periods as $p) {
                            $sh = $g['sheets'][(int) $p['id']] ?? null;
                            if ($sh === null) {
                                continue;
                            }
                            $lastSheet = $sh;
                            if ($pendingSheet === null && in_array($sh['status'], ['SUBMITTED', 'UNDER_REVIEW'], true)) {
                                $pendingSheet = $sh;
                            }
                            if ($approvedSheet === null && $sh['status'] === 'APPROVED') {
                                $approvedSheet = $sh;
                            }
                        }
                    ?>
                    <tr class="grade-review-row" data-search="<?= htmlspecialchars($searchHaystack) ?>">
                        <td>
                            <div class="queue-code"><?= htmlspecialchars($g['subject_code'] ?? '') ?></div>
                            <div class="queue-title"><?= htmlspecialchars($g['subject_name'] ?? '') ?></div>
                        </td>
                        <td>
                            <div class="text-dark text-truncate"><?= htmlspecialchars($instructorName) ?></div>
                            <?php if (!empty($g['email'])): ?>
                                <div class="queue-meta text-truncate"><?= htmlspecialchars($g['email']) ?></div>
                            <?php endif; ?>
                        </td>

                        <?php foreach ($periods as $p): ?>
                            <?php $sh = $g['sheets'][(int) $p['id']] ?? null; ?>
                            <td class="<?= (string) $p['id'] === $periodParam ? 'queue-period-active' : '' ?>">
                                <?php if ($sh !== null): ?>
                                    <?php $st = grading_sheet_status((string) $sh['status']); ?>
                                    <a href="<?= url('/dean/grade-review/' . $sh['id']) ?>"
                                       class="badge <?= $st['class'] ?> text-decoration-none"
                                       title="<?= htmlspecialchars($p['name'] . (!empty($sh['submitted_at']) ? ' — submitted ' . date('M d, Y h:i A', strtotime($sh['submitted_at'])) : '')) ?>">
                                        <i class="bi <?= $st['icon'] ?>"></i> <?= htmlspecialchars($st['short']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="queue-meta">Not submitted</span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>

                        <td>
                            <div class="queue-actions">
                                <?php if ($pendingSheet !== null): ?>
                                    <a class="btn btn-sm btn-primary" href="<?= url('/dean/grade-review/' . $pendingSheet['id']) ?>">Review <?= htmlspecialchars($pendingSheet['period_name']) ?></a>
                                <?php elseif ($approvedSheet !== null): ?>
                                    <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#confirmModal<?= $approvedSheet['id'] ?>">Confirm <?= htmlspecialchars($approvedSheet['period_name']) ?></button>
                                <?php elseif ($lastSheet !== null): ?>
                                    <a class="btn btn-sm btn-outline-secondary" href="<?= url('/dean/grade-review/' . $lastSheet['id']) ?>">View</a>
                                <?php endif; ?>

                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-action-trigger" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false" title="More actions">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end action-dropdown-menu shadow-sm">
                                        <?php foreach ($periods as $p): ?>
                                            <?php $sh = $g['sheets'][(int) $p['id']] ?? null; ?>
                                            <?php if ($sh === null) { continue; } ?>
                                            <li>
                                                <a class="dropdown-item" href="<?= url('/dean/grade-review/' . $sh['id'] . '/print') ?>" target="_blank">
                                                    <i class="bi bi-printer text-muted"></i> Print <?= htmlspecialchars($p['name']) ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                        <?php foreach ($periods as $p): ?>
                                            <?php $sh = $g['sheets'][(int) $p['id']] ?? null; ?>
                                            <?php if ($sh === null || !in_array($sh['status'], ['SUBMITTED', 'UNDER_REVIEW'], true)) { continue; } ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="<?= url('/dean/grade-review/approve') ?>" class="m-0" onsubmit="return confirm('Approve the <?= htmlspecialchars($p['name']) ?> grading sheet for <?= htmlspecialchars($g['subject_code'] ?? '') ?>?');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="grading_sheet_id" value="<?= $sh['id'] ?>">
                                                    <button type="submit" class="dropdown-item text-success">
                                                        <i class="bi bi-check-circle"></i> Approve <?= htmlspecialchars($p['name']) ?>
                                                    </button>
                                                </form>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <!-- Empty Search Result Fallback Row -->
                    <tr id="noResultsRow" style="display: none;">
                        <td colspan="<?= 3 + $periodCount ?>" class="p-0">
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
        <?php include __DIR__ . '/../../components/pagination.php'; ?>

        <?php foreach ($approvedSheets as $sheet): ?>
        <div class="modal fade" id="confirmModal<?= $sheet['id'] ?>" tabindex="-1" aria-labelledby="confirmModalLabel<?= $sheet['id'] ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="<?= url('/dean/grade-review/confirm') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="grading_sheet_id" value="<?= $sheet['id'] ?>">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmModalLabel<?= $sheet['id'] ?>">
                                <i class="bi bi-shield-check text-primary"></i>
                                Confirm Grading Sheet: <?= htmlspecialchars($sheet['subject_code'] ?? $sheet['code'] ?? '') ?> &middot; <?= htmlspecialchars($sheet['period_name'] ?? '') ?>
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

    // Filter the rows already on the page as you type; Enter searches all pages on the server
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
            visibleCounter.textContent = visibleCount + (visibleCount === 1 ? ' course' : ' courses');
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
