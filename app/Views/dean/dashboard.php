<?php
$pageTitle = 'Dean Dashboard';
$subtitle = 'Academic oversight, faculty workload governance, and semester grade review.';
ob_start();
?>

<!-- Metric KPI Cards (Clickable & Dynamic) -->
<div class="dashboard-stats">
    <a href="<?= url('/dean/grade-review') ?>" class="stat-card stat-card-link">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Pending reviews</span>
                <div class="stat-icon stat-icon-amber"><i class="bi bi-file-earmark-check-fill"></i></div>
            </div>
            <div class="stat-value tabular-nums <?= ($pendingReviewCount ?? 0) > 0 ? 'text-amber' : '' ?>"><?= number_format($pendingReviewCount ?? 0) ?></div>
        </div>
        <span class="stat-subtext">
            <?= ($pendingReviewCount ?? 0) > 0 ? 'Action required &rarr;' : 'All sheets up to date &rarr;' ?>
        </span>
    </a>

    <a href="<?= url('/dean/subjects') ?>" class="stat-card stat-card-link">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Curriculum subjects</span>
                <div class="stat-icon stat-icon-blue"><i class="bi bi-journal-bookmark-fill"></i></div>
            </div>
            <div class="stat-value tabular-nums"><?= number_format($totalSubjects ?? 0) ?></div>
        </div>
        <span class="stat-subtext">Master course catalog <i class="bi bi-arrow-right"></i></span>
    </a>

    <a href="<?= url('/dean/faculty-assignments') ?>" class="stat-card stat-card-link">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Faculty members</span>
                <div class="stat-icon stat-icon-green"><i class="bi bi-person-badge-fill"></i></div>
            </div>
            <div class="stat-value tabular-nums"><?= number_format($totalFaculty ?? 0) ?></div>
        </div>
        <span class="stat-subtext">View teaching workload <i class="bi bi-arrow-right"></i></span>
    </a>

    <a href="<?= url('/admin/sets') ?>" class="stat-card stat-card-link">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Class sets</span>
                <div class="stat-icon stat-icon-purple"><i class="bi bi-diagram-3-fill"></i></div>
            </div>
            <div class="stat-value tabular-nums"><?= number_format($totalSets ?? 0) ?></div>
        </div>
        <span class="stat-subtext">View class sets <i class="bi bi-arrow-right"></i></span>
    </a>
</div>

<!-- Quick Administration Shortcuts -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <a href="<?= url('/dean/grade-review') ?>" class="quick-action-card">
            <div class="quick-action-icon bg-warning-subtle text-warning-emphasis">
                <i class="bi bi-clipboard-check-fill"></i>
            </div>
            <div>
                <h4 class="h6 mb-0 fw-semibold">Grade reviews</h4>
                <p class="text-muted small mb-0">Review submitted grading sheets</p>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= url('/dean/faculty-assignments') ?>" class="quick-action-card">
            <div class="quick-action-icon bg-primary-subtle text-primary">
                <i class="bi bi-person-workspace"></i>
            </div>
            <div>
                <h4 class="h6 mb-0 fw-semibold">Workload assignments</h4>
                <p class="text-muted small mb-0">Assign instructors to courses</p>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= url('/dean/subjects') ?>" class="quick-action-card">
            <div class="quick-action-icon bg-info-subtle text-info">
                <i class="bi bi-book-half"></i>
            </div>
            <div>
                <h4 class="h6 mb-0 fw-semibold">Course catalog</h4>
                <p class="text-muted small mb-0">View curriculum courses &amp; units</p>
            </div>
        </a>
    </div>
</div>

<!-- Main Dashboard Content Grid -->
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h3 class="h6 mb-0 fw-semibold text-dark">Submissions Awaiting Dean Sign-Off</h3>
                <a href="<?= url('/dean/grade-review') ?>" class="btn btn-sm btn-outline-secondary">Open review center</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-white border-bottom">
                        <tr>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Subject</th>
                            <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Instructor</th>
                            <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Period</th>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="font-size: 11px;">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php if (empty($pendingSheets)): ?>
                            <tr>
                                <td colspan="4" class="p-0">
                                    <?php
                                    $icon = 'bi-check2-circle';
                                    $iconColor = 'green';
                                    $title = 'All caught up';
                                    $message = 'All submitted grading sheets have been reviewed and finalized.';
                                    include __DIR__ . '/../components/empty-state.php';
                                    ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pendingSheets as $ps): ?>
                                <tr>
                                    <td class="px-3">
                                        <div class="fw-semibold font-monospace text-primary"><?= htmlspecialchars($ps['subject_code']) ?></div>
                                        <div class="text-dark small"><?= htmlspecialchars($ps['subject_name']) ?></div>
                                    </td>
                                    <td class="px-3 text-muted small">
                                        <?= htmlspecialchars($ps['faculty_first_name'] . ' ' . $ps['faculty_last_name']) ?>
                                    </td>
                                    <td class="px-3">
                                        <span class="badge bg-light text-dark border fw-semibold">
                                            <?= htmlspecialchars($ps['period_name']) ?>
                                        </span>
                                    </td>
                                    <td class="px-3 text-end">
                                        <a href="<?= url('/dean/grade-review/' . $ps['id']) ?>" class="btn btn-sm btn-primary py-1 px-2">
                                            <i class="bi bi-check-circle"></i> Review sheet
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
            <div class="card-header bg-white py-3 border-bottom">
                <h3 class="h6 mb-0 fw-semibold text-dark">Academic Governance</h3>
            </div>
            <div class="card-body py-3 px-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="stat-icon stat-icon-purple" style="width: 44px; height: 44px; font-size: 20px;">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <div>
                        <div class="fw-semibold text-dark fs-6"><?= htmlspecialchars($activeTerm['academic_year_name'] ?? '2026-2027') ?></div>
                        <div class="text-muted small"><?= ($activeTerm['semester'] ?? '1') === '1' ? '1st Semester' : '2nd Semester' ?></div>
                    </div>
                </div>
                <hr class="my-2 border-light">
                <div class="d-flex justify-content-between py-1 small">
                    <span class="text-muted">Total curriculum courses</span>
                    <span class="fw-semibold text-dark"><?= $totalSubjects ?? 0 ?> subjects</span>
                </div>
                <div class="d-flex justify-content-between py-1 small">
                    <span class="text-muted">Active sets</span>
                    <span class="fw-semibold text-dark"><?= $totalSets ?? 0 ?> batches</span>
                </div>
                <div class="d-flex justify-content-between py-1 small">
                    <span class="text-muted">Faculty strength</span>
                    <span class="fw-semibold text-dark"><?= $totalFaculty ?? 0 ?> members</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard.php';
?>
