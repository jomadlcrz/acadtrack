<?php
$pageTitle = 'My Assigned Subjects';
$subtitle = 'Curricular course sections assigned to your instructional teaching workload.';
ob_start();
?>

<div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h3 class="h6 mb-0 fw-semibold text-dark d-flex align-items-center gap-2">
            <i class="bi bi-mortarboard text-primary"></i> Teaching Workload Roster
        </h3>
        <span class="text-muted small"><?= count($subjects) ?> courses assigned</span>
    </div>

    <?php if (empty($subjects)): ?>
        <div class="card-body text-center py-5">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-3" style="width: 52px; height: 52px; font-size: 24px;">
                <i class="bi bi-book"></i>
            </div>
            <h4 class="h6 fw-semibold text-dark mb-1">No subjects assigned yet</h4>
            <p class="text-muted small mb-0">You have not been designated to any course sections for the active term. Contact the College Dean for curriculum assignments.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light border-bottom">
                    <tr>
                        <th class="fw-semibold text-muted small py-3 px-3" style="width: 150px;">Subject code</th>
                        <th class="fw-semibold text-muted small py-3 px-3">Descriptive title</th>
                        <th class="fw-semibold text-muted small py-3 px-3" style="width: 140px;">Year level</th>
                        <th class="fw-semibold text-muted small py-3 px-3 text-end" style="width: 240px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $subject): ?>
                    <tr>
                        <td class="px-3 fw-semibold text-primary font-monospace"><?= htmlspecialchars($subject['code']) ?></td>
                        <td class="px-3 fw-semibold text-dark"><?= htmlspecialchars($subject['name']) ?></td>
                        <td class="px-3 small text-secondary">
                            <?= htmlspecialchars($subject['year_level']) ?><?= match((int)$subject['year_level']) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' } ?> year
                        </td>
                        <td class="px-3 text-end text-nowrap">
                            <a href="<?= url('/faculty/grading?subject_id=' . $subject['id']) ?>" class="btn btn-sm btn-primary py-1 px-2 d-inline-flex align-items-center gap-1">
                                <i class="bi bi-pencil-square"></i> Enter grades
                            </a>
                            <a href="<?= url('/faculty/students?subject_id=' . $subject['id']) ?>" class="btn btn-sm btn-outline-secondary py-1 px-2 d-inline-flex align-items-center gap-1 ms-1">
                                <i class="bi bi-people"></i> View students
                            </a>
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
