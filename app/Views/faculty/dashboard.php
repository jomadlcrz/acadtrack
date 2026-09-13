<?php
$pageTitle = 'Faculty Dashboard';
$subtitle = 'Teaching workload summary, student enrollment overview, and grade submission portal.';
ob_start();
?>

<!-- Metric KPI Cards (Clickable & Dynamic) -->
<div class="dashboard-stats">
    <a href="<?= url('/faculty/subjects') ?>" class="stat-card stat-card-link">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Assigned subjects</span>
                <div class="stat-icon stat-icon-blue"><i class="bi bi-journal-text"></i></div>
            </div>
            <div class="stat-value tabular-nums"><?= number_format($assignedSubjectsCount ?? 0) ?></div>
        </div>
        <span class="stat-subtext">Teaching workload <i class="bi bi-arrow-right"></i></span>
    </a>

    <a href="<?= url('/faculty/students') ?>" class="stat-card stat-card-link">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Total students</span>
                <div class="stat-icon stat-icon-green"><i class="bi bi-people-fill"></i></div>
            </div>
            <div class="stat-value tabular-nums"><?= number_format($totalStudents ?? 0) ?></div>
        </div>
        <span class="stat-subtext">Active class rosters <i class="bi bi-arrow-right"></i></span>
    </a>

    <a href="<?= url('/faculty/grading') ?>" class="stat-card stat-card-link">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Grading sheets</span>
                <div class="stat-icon stat-icon-amber"><i class="bi bi-file-earmark-check-fill"></i></div>
            </div>
            <div class="stat-value tabular-nums"><?= number_format($submittedCount ?? 0) ?></div>
        </div>
        <span class="stat-subtext">Submitted &amp; confirmed <i class="bi bi-arrow-right"></i></span>
    </a>
</div>

<!-- Quick Administration Shortcuts -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <a href="<?= url('/faculty/grading') ?>" class="quick-action-card">
            <div class="quick-action-icon bg-primary-subtle text-primary">
                <i class="bi bi-pencil-square"></i>
            </div>
            <div>
                <h4 class="h6 mb-0 fw-semibold">Enter grades</h4>
                <p class="text-muted small mb-0">Input Prelim, Midterm, Finals</p>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= url('/faculty/students') ?>" class="quick-action-card">
            <div class="quick-action-icon bg-success-subtle text-success">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <h4 class="h6 mb-0 fw-semibold">Class roster</h4>
                <p class="text-muted small mb-0">Manage enrolled students &amp; sets</p>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= url('/faculty/subjects') ?>" class="quick-action-card">
            <div class="quick-action-icon bg-info-subtle text-info">
                <i class="bi bi-gear-fill"></i>
            </div>
            <div>
                <h4 class="h6 mb-0 fw-semibold">Course setup</h4>
                <p class="text-muted small mb-0">Configure grading weights &amp; nature</p>
            </div>
        </a>
    </div>
</div>

<!-- Workload Courses Table -->
<div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h3 class="h6 mb-0 fw-semibold text-dark">Active Term Assigned Courses</h3>
        <a href="<?= url('/faculty/subjects') ?>" class="btn btn-sm btn-outline-secondary">Course catalog</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-white border-bottom">
                <tr>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Subject code</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Descriptive title</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Nature</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Students</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Grading status</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="font-size: 11px;">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (empty($workload)): ?>
                    <tr>
                        <td colspan="6" class="p-0">
                            <?php
                            $icon = 'bi-book';
                            $title = 'No assigned courses';
                            $message = 'No assigned subjects found for the current academic term.';
                            include __DIR__ . '/../components/empty-state.php';
                            ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($workload as $item): ?>
                        <tr>
                            <td class="px-3 fw-semibold font-monospace text-primary">
                                <?= htmlspecialchars($item['code']) ?>
                            </td>
                            <td class="px-3 fw-semibold text-dark">
                                <?= htmlspecialchars($item['name']) ?>
                            </td>
                            <td class="px-3">
                                <span class="badge <?= match($item['nature'] ?? 'Lecture') {
                                    'Laboratory' => 'bg-info-subtle text-info border border-info-subtle',
                                    'Combined' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                    default => 'bg-primary-subtle text-primary border border-primary-subtle'
                                } ?> fw-semibold">
                                    <?= htmlspecialchars($item['nature'] ?? 'Lecture') ?>
                                </span>
                            </td>
                            <td class="px-3 text-muted small">
                                <i class="bi bi-person me-1"></i><?= (int) ($item['enrolled_count'] ?? 0) ?> enrolled
                            </td>
                            <td class="px-3">
                                <span class="badge badge-<?= strtolower($item['sheet_status'] ?? 'draft') ?> fw-semibold">
                                    <?= htmlspecialchars(ucfirst(strtolower($item['sheet_status'] ?? 'Draft'))) ?>
                                </span>
                            </td>
                            <td class="px-3 text-end">
                                <a href="<?= url('/faculty/grading?subject_id=' . $item['id']) ?>" class="btn btn-sm btn-primary py-1 px-2 me-1">
                                    <i class="bi bi-pencil-square"></i> Grades
                                </a>
                                <a href="<?= url('/faculty/students?subject_id=' . $item['id']) ?>" class="btn btn-sm btn-outline-secondary py-1 px-2">
                                    <i class="bi bi-people"></i> Roster
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard.php';
?>
