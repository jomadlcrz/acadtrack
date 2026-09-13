<?php
$pageTitle = 'Student Dashboard';
$subtitle = 'Academic records, active semester enrollment, and curriculum evaluation.';
ob_start();
?>

<!-- Metric KPI Cards (Clickable & Dynamic) -->
<div class="dashboard-stats">
    <a href="<?= url('/student/grades') ?>" class="stat-card stat-card-link">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Enrolled subjects</span>
                <div class="stat-icon stat-icon-blue"><i class="bi bi-journal-check"></i></div>
            </div>
            <div class="stat-value tabular-nums"><?= (int) ($enrolledCount ?? 0) ?></div>
        </div>
        <span class="stat-subtext">Active semester courses <i class="bi bi-arrow-right"></i></span>
    </a>

    <div class="stat-card">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Academic standing</span>
                <div class="stat-icon stat-icon-green"><i class="bi bi-mortarboard-fill"></i></div>
            </div>
            <div class="stat-value" style="font-size: 20px; font-weight: 600;">
                <?= htmlspecialchars(ucfirst($student['status'] ?? 'Regular')) ?>
            </div>
        </div>
        <span class="stat-subtext">
            ID: <?= !empty($user['student_number']) ? htmlspecialchars($user['student_number']) : 'No ID' ?> • <?= htmlspecialchars((string)($student['year_level'] ?? 1)) ?><?= match((int)($student['year_level'] ?? 1)) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' } ?> year
        </span>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Set batch</span>
                <div class="stat-icon stat-icon-purple"><i class="bi bi-diagram-3-fill"></i></div>
            </div>
            <div class="stat-value" style="font-size: 20px; font-weight: 600;">
                <?= htmlspecialchars($set['name'] ?? 'Unassigned') ?>
            </div>
        </div>
        <span class="stat-subtext">Class set assignment</span>
    </div>

    <a href="<?= url('/student/evaluation') ?>" class="stat-card stat-card-link">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Evaluation progress</span>
                <div class="stat-icon stat-icon-amber"><i class="bi bi-award-fill"></i></div>
            </div>
            <div class="stat-value tabular-nums">
                <?= isset($summary['passed_count']) ? (int)$summary['passed_count'] : 0 ?>
            </div>
        </div>
        <span class="stat-subtext">Completed subjects <i class="bi bi-arrow-right"></i></span>
    </a>
</div>

<!-- Quick Administration Shortcuts -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <a href="<?= url('/student/grades') ?>" class="quick-action-card">
            <div class="quick-action-icon bg-primary-subtle text-primary">
                <i class="bi bi-file-earmark-spreadsheet-fill"></i>
            </div>
            <div>
                <h4 class="h6 mb-0 fw-semibold">View semester grades</h4>
                <p class="text-muted small mb-0">Check periodic grades (Prelim, Midterm, Semi-final, Final)</p>
            </div>
        </a>
    </div>
    <div class="col-md-6">
        <a href="<?= url('/student/evaluation') ?>" class="quick-action-card">
            <div class="quick-action-icon bg-success-subtle text-success">
                <i class="bi bi-mortarboard"></i>
            </div>
            <div>
                <h4 class="h6 mb-0 fw-semibold">Curriculum evaluation</h4>
                <p class="text-muted small mb-0">Complete program checklist and graduation progress</p>
            </div>
        </a>
    </div>
</div>

<!-- Enrolled Subjects & Periodic Ratings Preview -->
<div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h3 class="h6 mb-0 fw-semibold text-dark">Current Term Enrolled Subjects</h3>
        <a href="<?= url('/student/grades') ?>" class="btn btn-sm btn-outline-secondary">Full grade report</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-white border-bottom">
                <tr>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Subject code</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Descriptive title</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="font-size: 11px;">Prelim</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="font-size: 11px;">Midterm</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="font-size: 11px;">Semi-final</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="font-size: 11px;">Final</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="font-size: 11px;">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (empty($grades)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted small">
                            No enrolled courses recorded for the current term.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($grades as $g): ?>
                        <tr>
                            <td class="px-3 fw-semibold font-monospace text-primary">
                                <?= htmlspecialchars($g['code'] ?? '') ?>
                            </td>
                            <td class="px-3 fw-semibold text-dark">
                                <?= htmlspecialchars($g['name'] ?? '') ?>
                            </td>
                            <td class="px-3 text-center font-monospace small">
                                <?= isset($g['prelim']) && $g['prelim'] !== null ? number_format((float)$g['prelim'], 2) : '—' ?>
                            </td>
                            <td class="px-3 text-center font-monospace small">
                                <?= isset($g['midterm']) && $g['midterm'] !== null ? number_format((float)$g['midterm'], 2) : '—' ?>
                            </td>
                            <td class="px-3 text-center font-monospace small">
                                <?= isset($g['semi_final']) && $g['semi_final'] !== null ? number_format((float)$g['semi_final'], 2) : '—' ?>
                            </td>
                            <td class="px-3 text-center font-monospace small">
                                <?= isset($g['final']) && $g['final'] !== null ? number_format((float)$g['final'], 2) : '—' ?>
                            </td>
                            <td class="px-3 text-center">
                                <?php if (!empty($g['final_grade'])): ?>
                                    <span class="badge <?= ($g['final_grade'] <= 3.0) ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' ?>">
                                        <?= ($g['final_grade'] <= 3.0) ? 'Passed' : 'Failed' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">In progress</span>
                                <?php endif; ?>
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