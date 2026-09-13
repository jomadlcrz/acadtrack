<?php
$pageTitle = 'Institutional Departments';
$subtitle = 'Manage academic colleges, faculties, and departmental divisions.';
$headerActions = '<button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addDepartmentModal"><i class="bi bi-plus-circle"></i> Add department</button>';
ob_start();
?>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h6 mb-0 fw-semibold text-dark">Academic Departments Roster</h3>
            <small class="text-muted">Colleges and departments governing faculty assignments and academic curricula.</small>
        </div>
        <span class="badge bg-light text-dark border"><?= count($departments) ?> registered</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light border-bottom">
                <tr>
                    <th class="fw-semibold text-muted small py-3 px-3" style="width: 140px;">Department code</th>
                    <th class="fw-semibold text-muted small py-3 px-3">Department name</th>
                    <th class="fw-semibold text-muted small py-3 px-3">Description</th>
                    <th class="fw-semibold text-muted small py-3 px-3 text-center" style="width: 150px;">Assigned faculty</th>
                    <th class="fw-semibold text-muted small py-3 px-3 text-center" style="width: 120px;">Status</th>
                    <th class="fw-semibold text-muted small py-3 px-3 text-end" style="width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($departments)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted small">
                            <i class="bi bi-building-x d-block fs-3 mb-2 text-secondary"></i>
                            No academic departments registered in the system yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($departments as $dept): ?>
                    <tr>
                        <td class="px-3 fw-semibold text-primary font-monospace"><?= htmlspecialchars($dept['code']) ?></td>
                        <td class="px-3 fw-semibold text-dark"><?= htmlspecialchars($dept['name']) ?></td>
                        <td class="px-3 text-muted small"><?= htmlspecialchars($dept['description'] ?? '—') ?></td>
                        <td class="px-3 text-center">
                            <?php $facCount = (int) ($dept['faculty_count'] ?? 0); ?>
                            <?php if ($facCount > 0): ?>
                                <span class="badge badge-faculty">
                                    <i class="bi bi-person-badge me-1"></i> <?= $facCount ?> <?= $facCount === 1 ? 'instructor' : 'instructors' ?>
                                </span>
                            <?php else: ?>
                                <span class="badge badge-draft">0 instructors</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 text-center">
                            <?php if (($dept['status'] ?? 'active') === 'active'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary border">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 d-inline-flex align-items-center gap-1"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editDepartmentModal<?= $dept['id'] ?>">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <?php if (($dept['status'] ?? 'active') === 'active'): ?>
                                <form method="POST" action="<?= url('/admin/departments/' . $dept['id'] . '/archive') ?>" class="d-inline" onsubmit="return confirm('Archive the <?= htmlspecialchars(addslashes($dept['name'])) ?> department?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-secondary py-1 px-2 d-inline-flex align-items-center gap-1" title="Archive department">
                                        <i class="bi bi-archive"></i> Archive
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="<?= url('/admin/departments/' . $dept['id'] . '/restore') ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2 d-inline-flex align-items-center gap-1" title="Restore department">
                                        <i class="bi bi-arrow-counterclockwise"></i> Restore
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <!-- Edit Department Modal -->
                    <div class="modal fade" id="editDepartmentModal<?= $dept['id'] ?>" tabindex="-1" aria-labelledby="editDepartmentModalLabel<?= $dept['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="<?= url('/admin/departments/' . $dept['id']) ?>">
                                    <?= csrf_field() ?>
                                    <div class="modal-header">
                                        <h5 class="modal-title h6 fw-semibold" id="editDepartmentModalLabel<?= $dept['id'] ?>">Edit Department</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="code_<?= $dept['id'] ?>" class="form-label">Department code <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control font-monospace" id="code_<?= $dept['id'] ?>" name="code" value="<?= htmlspecialchars($dept['code']) ?>" required style="text-transform: uppercase;">
                                            <div class="form-text">e.g., CIT, CBA, CAS</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="name_<?= $dept['id'] ?>" class="form-label">Department name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name_<?= $dept['id'] ?>" name="name" value="<?= htmlspecialchars($dept['name']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="description_<?= $dept['id'] ?>" class="form-label">Description</label>
                                            <textarea class="form-control" id="description_<?= $dept['id'] ?>" name="description" rows="3"><?= htmlspecialchars($dept['description'] ?? '') ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="status_<?= $dept['id'] ?>" class="form-label">Status</label>
                                            <select class="form-select" id="status_<?= $dept['id'] ?>" name="status">
                                                <option value="active" <?= ($dept['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                                <option value="inactive" <?= ($dept['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                                            <i class="bi bi-check2"></i> Save changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Department Modal -->
<div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('/admin/departments') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title h6 fw-semibold" id="addDepartmentModalLabel">Add New Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_code" class="form-label">Department code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-monospace" id="add_code" name="code" placeholder="e.g., CIT" required style="text-transform: uppercase;">
                        <div class="form-text">Unique uppercase institutional code.</div>
                    </div>
                    <div class="mb-3">
                        <label for="add_name" class="form-label">Department name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add_name" name="name" placeholder="e.g., College of Information Technology" required>
                    </div>
                    <div class="mb-3">
                        <label for="add_description" class="form-label">Description</label>
                        <textarea class="form-control" id="add_description" name="description" rows="3" placeholder="Optional brief overview of programs handled by this department."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="add_status" class="form-label">Status</label>
                        <select class="form-select" id="add_status" name="status">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                        <i class="bi bi-plus-circle"></i> Save department
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
