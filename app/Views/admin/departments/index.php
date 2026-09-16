<?php
$pageTitle = 'Departments';
$subtitle = 'Manage academic colleges, faculties, and departmental divisions.';
$headerActions = '<button type="button" class="btn text-white d-inline-flex align-items-center gap-1.5" style="background-color: #2f4a86; border-color: #2f4a86;" data-bs-toggle="modal" data-bs-target="#addDepartmentModal"><i class="bi bi-plus-lg"></i> New Department</button>';
ob_start();
?>

<!-- Search & Statistics Bar -->
<div class="d-flex flex-column flex-sm-row gap-3 align-items-sm-center justify-content-between mb-4">
    <div class="position-relative w-100" style="max-width: 420px;">
        <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 14px;"></i>
        <input type="text" 
               id="deptSearch" 
               class="form-control ps-5" 
               placeholder="Search by code or department name..." 
               autocomplete="off">
    </div>
    <div class="text-muted small fw-medium" id="deptCounter">
        <?= count($departments) ?> <?= count($departments) === 1 ? 'department' : 'departments' ?>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="departmentsTable">
            <thead class="bg-white border-bottom">
                <tr>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="width: 160px; font-size: 11px;">Department code</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Department Name</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 150px; font-size: 11px;">Assigned faculty</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 120px; font-size: 11px;">Status</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="width: 160px; font-size: 11px;">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (empty($departments)): ?>
                    <tr id="emptyDeptRow">
                        <td colspan="5" class="p-0">
                            <?php
                            $icon = 'bi-building-x';
                            $title = 'No departments created yet';
                            $message = 'Click "New Department" above to register an academic department.';
                            include __DIR__ . '/../../components/empty-state.php';
                            ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($departments as $dept): ?>
                    <tr class="dept-row" data-search="<?= strtolower(htmlspecialchars($dept['code'] . ' ' . $dept['name'])) ?>">
                        <td class="py-3 px-4 fw-bold text-dark font-monospace"><?= htmlspecialchars($dept['code']) ?></td>
                        <td class="px-4 fw-semibold text-dark"><?= htmlspecialchars($dept['name']) ?></td>
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
                    <tr id="emptyDeptRow" style="display: none;">
                        <td colspan="5" class="p-0">
                            <?php
                            $icon = 'bi-search';
                            $title = 'No departments found';
                            $message = 'No departments match your search keywords.';
                            include __DIR__ . '/../../components/empty-state.php';
                            ?>
                        </td>
                    </tr>
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
                        <div class="form-text">Unique uppercase department code.</div>
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
                <div class="modal-footer bg-light py-3 px-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm text-white d-inline-flex align-items-center gap-1.5" style="background-color: #2f4a86; border-color: #2f4a86;">
                        <i class="bi bi-check-lg"></i> Save Department
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('deptSearch');
    const rows = document.querySelectorAll('.dept-row');
    const counter = document.getElementById('deptCounter');
    const emptyRow = document.getElementById('emptyDeptRow');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            let visibleCount = 0;

            rows.forEach(function(row) {
                const searchData = row.getAttribute('data-search') || '';
                if (!query || searchData.includes(query)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (counter) {
                counter.textContent = visibleCount + (visibleCount === 1 ? ' department' : ' departments');
            }

            if (emptyRow && rows.length > 0) {
                emptyRow.style.display = visibleCount === 0 ? '' : 'none';
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
