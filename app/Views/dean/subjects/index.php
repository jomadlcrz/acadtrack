<?php
$pageTitle = 'Curricular Subjects';
$subtitle = 'Manage institutional courses, credit offerings, and academic term placements.';
$currentFilter = $statusFilter ?? 'all';
ob_start();
?>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h6 mb-0 fw-semibold text-dark">Create Curricular Subject</h3>
            <small class="text-muted">Register a new academic course into the college program curriculum.</small>
        </div>
    </div>

    <form method="POST" action="<?= url('/dean/subjects') ?>" novalidate>
        <?= csrf_field() ?>

        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="subject_code" class="form-label">Subject code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="subject_code" name="subject_code" placeholder="e.g., IT 101" value="<?= htmlspecialchars($_POST['subject_code'] ?? $_POST['code'] ?? '') ?>" required>
                </div>

                <div class="col-md-5">
                    <label for="descriptive_title" class="form-label">Descriptive title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="descriptive_title" name="descriptive_title" placeholder="e.g., Introduction to Computing" value="<?= htmlspecialchars($_POST['descriptive_title'] ?? $_POST['name'] ?? '') ?>" required>
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
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="h6 mb-0 fw-semibold text-dark">Master Course Catalog</h3>
            <span class="text-muted small"><?= count($subjects) ?> courses registered</span>
        </div>

        <div class="btn-group btn-group-sm" role="group" aria-label="Filter status">
            <a href="<?= url('/dean/subjects?status=all') ?>" class="btn <?= $currentFilter === 'all' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                All
            </a>
            <a href="<?= url('/dean/subjects?status=active') ?>" class="btn <?= $currentFilter === 'active' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                Active Only
            </a>
            <a href="<?= url('/dean/subjects?status=archived') ?>" class="btn <?= $currentFilter === 'archived' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                Archived Only
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light border-bottom">
                <tr>
                    <th class="fw-semibold text-muted small py-3 px-3" style="width: 130px;">Subject code</th>
                    <th class="fw-semibold text-muted small py-3 px-3">Descriptive title</th>
                    <th class="fw-semibold text-muted small py-3 px-3" style="width: 110px;">Year level</th>
                    <th class="fw-semibold text-muted small py-3 px-3" style="width: 120px;">Semester</th>
                    <th class="fw-semibold text-muted small py-3 px-3 text-center" style="width: 110px;">Status</th>
                    <th class="fw-semibold text-muted small py-3 px-3 text-end" style="width: 180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($subjects)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted small">
                            <i class="bi bi-book-half d-block fs-3 mb-2 text-secondary"></i>
                            No subjects found matching the selected filter.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($subjects as $subject): ?>
                        <?php 
                            $isArchived = !empty($subject['is_archived']);
                            $hasDeps = ($subject['assigned_faculty_count'] ?? 0) > 0 
                                || ($subject['enrolled_students_count'] ?? 0) > 0 
                                || ($subject['grades_count'] ?? 0) > 0;
                        ?>
                    <tr class="<?= $isArchived ? 'table-light text-muted' : '' ?>">
                        <td class="px-3 fw-semibold <?= $isArchived ? 'text-secondary' : 'text-primary' ?> font-monospace">
                            <?= htmlspecialchars($subject['subject_code'] ?? $subject['code']) ?>
                        </td>
                        <td class="px-3 fw-semibold <?= $isArchived ? 'text-secondary' : 'text-dark' ?>">
                            <?= htmlspecialchars($subject['descriptive_title'] ?? $subject['name']) ?>
                            <?php if ($isArchived && !empty($subject['archived_at'])): ?>
                                <small class="d-block text-muted fw-normal" style="font-size: 0.75rem;">
                                     Archived on <?= date('M d, Y', strtotime($subject['archived_at'])) ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 small text-secondary">
                            <?= htmlspecialchars((string) $subject['year_level']) ?><?= match((int)$subject['year_level']) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' } ?> year
                        </td>
                        <td class="px-3 small text-secondary">
                            <?= ((int)$subject['semester']) === 1 ? '1st semester' : (((int)$subject['semester']) === 2 ? '2nd semester' : 'Summer') ?>
                        </td>
                        <td class="px-3 text-center">
                            <?php if ($isArchived): ?>
                                <span class="badge rounded-pill fw-medium" style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">
                                    <i class="bi bi-archive me-1"></i>Archived
                                </span>
                            <?php else: ?>
                                <span class="badge rounded-pill fw-medium" style="background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;">
                                    <i class="bi bi-check-circle me-1"></i>Active
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 text-end">
                            <div class="d-inline-flex align-items-center gap-1">
                                <?php if ($isArchived): ?>
                                    <form method="POST" action="<?= url('/dean/subjects/' . $subject['id'] . '/restore') ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2 d-inline-flex align-items-center gap-1" title="Restore to active catalog">
                                            <i class="bi bi-arrow-counterclockwise"></i> Restore
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="<?= url('/dean/subjects/' . $subject['id'] . '/archive') ?>" class="d-inline" onsubmit="return confirm('Archive course <?= htmlspecialchars($subject['subject_code'] ?? $subject['code']) ?>? The subject will be retired from active offerings, and all existing academic records and grades will remain permanently preserved.');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-secondary py-1 px-2 d-inline-flex align-items-center gap-1" title="Archive Subject">
                                            <i class="bi bi-archive"></i> Archive
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
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
