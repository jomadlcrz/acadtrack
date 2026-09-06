<?php
$pageTitle = 'Curricular Subjects';
$subtitle = 'Manage institutional courses, credit offerings, and academic term placements.';
ob_start();
?>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h6 mb-0 fw-semibold text-dark d-flex align-items-center gap-2">
                <i class="bi bi-book text-primary"></i> Create Curricular Subject
            </h3>
            <small class="text-muted">Register a new academic course into the college program curriculum.</small>
        </div>
    </div>

    <form method="POST" action="<?= url('/dean/subjects') ?>" novalidate>
        <?= csrf_field() ?>

        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="code" class="form-label">Subject code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="code" name="code" placeholder="e.g., IT 101" value="<?= htmlspecialchars($_POST['code'] ?? '') ?>" required>
                </div>

                <div class="col-md-5">
                    <label for="name" class="form-label">Descriptive title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="e.g., Introduction to Computing" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                </div>

                <div class="col-md-2">
                    <label for="year_level" class="form-label">Year level <span class="text-danger">*</span></label>
                    <select class="form-select" id="year_level" name="year_level" required>
                        <option value="1">1st year</option>
                        <option value="2">2nd year</option>
                        <option value="3">3rd year</option>
                        <option value="4">4th year</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
                    <select class="form-select" id="semester" name="semester" required>
                        <option value="1">1st semester</option>
                        <option value="2">2nd semester</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card-footer bg-light py-3 border-top d-flex justify-content-end align-items-center gap-2">
            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                <i class="bi bi-plus-circle"></i> Add subject
            </button>
        </div>
    </form>
</div>

<div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h3 class="h6 mb-0 fw-semibold text-dark d-flex align-items-center gap-2">
            <i class="bi bi-card-checklist text-primary"></i> Master Course Catalog
        </h3>
        <span class="text-muted small"><?= count($subjects) ?> courses registered</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light border-bottom">
                <tr>
                    <th class="fw-semibold text-muted small py-3 px-3" style="width: 150px;">Subject code</th>
                    <th class="fw-semibold text-muted small py-3 px-3">Descriptive title</th>
                    <th class="fw-semibold text-muted small py-3 px-3" style="width: 130px;">Year level</th>
                    <th class="fw-semibold text-muted small py-3 px-3" style="width: 150px;">Semester</th>
                    <th class="fw-semibold text-muted small py-3 px-3 text-end" style="width: 130px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($subjects)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted small">
                            <i class="bi bi-book-half d-block fs-3 mb-2 text-secondary"></i>
                            No subjects have been registered in the curriculum yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($subjects as $subject): ?>
                    <tr>
                        <td class="px-3 fw-semibold text-primary font-monospace"><?= htmlspecialchars($subject['code']) ?></td>
                        <td class="px-3 fw-semibold text-dark"><?= htmlspecialchars($subject['name']) ?></td>
                        <td class="px-3 small text-secondary">
                            <?= htmlspecialchars($subject['year_level']) ?><?= match((int)$subject['year_level']) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' } ?> year
                        </td>
                        <td class="px-3 small text-secondary">
                            <?= $subject['semester'] === '1' ? '1st semester' : '2nd semester' ?>
                        </td>
                        <td class="px-3 text-end">
                            <form method="POST" action="<?= url('/dean/subjects/' . $subject['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete course <?= htmlspecialchars($subject['code']) ?>?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2 d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
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
include __DIR__ . '/../../layouts/dashboard.php';
?>
