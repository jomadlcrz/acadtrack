<?php
$dept = $department ?? [];
$programs = $dept['programs'] ?? [];
$faculty = $dept['faculty'] ?? [];
$facultyCount = (int) ($dept['faculty_count'] ?? count($faculty));
$programCount = count($programs);

$pageTitle = htmlspecialchars($dept['name'] ?? 'Department');
$subtitle = htmlspecialchars($dept['code'] ?? '') . ' &bull; Academic department overview, programs, and assigned faculty roster.';
$headerActions = '
<div class="d-flex align-items-center gap-2">
    <a href="' . url('/admin/departments') . '" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1.5">
        <i class="bi bi-arrow-left"></i> Back to departments
    </a>
    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#editDepartmentModal">
        <i class="bi bi-pencil"></i> Edit department
    </button>
</div>';

ob_start();
?>

<!-- Overview Metrics -->
<div class="summary-strip mb-4">
    <div class="summary-cell">
        <div class="summary-label">Department abbrev</div>
        <div class="summary-value font-monospace text-primary"><?= htmlspecialchars($dept['code'] ?? '') ?></div>
        <div class="summary-note">Official academic code</div>
    </div>
    <div class="summary-cell">
        <div class="summary-label">Department status</div>
        <div class="summary-value">
            <?php if (($dept['status'] ?? 'active') === 'active'): ?>
                <span class="badge bg-success-subtle text-success border border-success-subtle fs-6 py-1 px-2.5">Active</span>
            <?php else: ?>
                <span class="badge bg-secondary-subtle text-secondary border fs-6 py-1 px-2.5">Inactive</span>
            <?php endif; ?>
        </div>
        <div class="summary-note">Institutional status</div>
    </div>
    <div class="summary-cell">
        <div class="summary-label">Academic programs</div>
        <div class="summary-value"><?= $programCount ?></div>
        <div class="summary-note"><?= $programCount === 1 ? 'Degree program' : 'Degree programs' ?></div>
    </div>
    <div class="summary-cell">
        <div class="summary-label">Assigned faculty</div>
        <div class="summary-value"><?= $facultyCount ?></div>
        <div class="summary-note"><?= $facultyCount === 1 ? 'Instructor' : 'Instructors' ?> registered</div>
    </div>
</div>

<?php if (!empty($dept['description'])): ?>
<div class="card mb-4 border-0 shadow-sm" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="card-body p-3">
        <div class="small fw-semibold text-muted text-uppercase mb-1" style="font-size: 11px;">Department Overview</div>
        <p class="mb-0 text-dark"><?= nl2br(htmlspecialchars($dept['description'])) ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Academic Programs Section -->
<div class="card mb-4 border-0 shadow-sm" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div>
            <h3 class="h6 mb-0 fw-semibold text-dark">Academic Programs</h3>
            <small class="text-muted">Degree programs offered under this academic department</small>
        </div>
        <a href="<?= url('/admin/program-curricula') ?>" class="btn btn-sm btn-outline-secondary">
            View all curricula
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-white border-bottom">
                <tr>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 11px;">Program abbrev</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Program name</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="width: 180px; font-size: 11px;">Program type</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 130px; font-size: 11px;">Duration</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 120px; font-size: 11px;">Status</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="width: 140px; font-size: 11px;">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (empty($programs)): ?>
                    <tr>
                        <td colspan="6" class="p-0">
                            <?php
                            $icon = 'bi-journal-x';
                            $title = 'No academic programs registered';
                            $message = 'There are no degree programs linked to this department yet.';
                            include __DIR__ . '/../../components/empty-state.php';
                            ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($programs as $prog): ?>
                    <tr>
                        <td class="py-3 px-4 fw-bold text-dark font-monospace"><?= htmlspecialchars($prog['program_abbrev'] ?? '') ?></td>
                        <td class="px-4 fw-semibold text-dark"><?= htmlspecialchars($prog['program_name'] ?? '') ?></td>
                        <td class="px-3 text-muted"><?= htmlspecialchars($prog['program_type'] ?? 'Degree') ?></td>
                        <td class="px-3 text-center text-muted"><?= htmlspecialchars($prog['program_length'] ?? '4 Years') ?></td>
                        <td class="px-3 text-center">
                            <?php if (($prog['status'] ?? 'active') === 'active'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary border">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 text-end">
                            <a href="<?= url('/admin/program-curricula') ?>" class="btn btn-sm btn-outline-secondary">
                                Curricula
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Assigned Faculty Section -->
<div class="card mb-4 border-0 shadow-sm" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div>
            <h3 class="h6 mb-0 fw-semibold text-dark">Assigned Faculty</h3>
            <small class="text-muted">Instructors and academic deans affiliated with this department</small>
        </div>
        <a href="<?= url('/admin/faculty?department_id=' . (int) ($dept['id'] ?? 0)) ?>" class="btn btn-sm btn-outline-secondary">
            Filter in faculty directory
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-white border-bottom">
                <tr>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Instructor name</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Email address</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 140px; font-size: 11px;">Role</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 120px; font-size: 11px;">Account status</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="width: 140px; font-size: 11px;">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (empty($faculty)): ?>
                    <tr>
                        <td colspan="5" class="p-0">
                            <?php
                            $icon = 'bi-people';
                            $title = 'No faculty members assigned';
                            $message = 'No instructors are currently assigned to this department.';
                            include __DIR__ . '/../../components/empty-state.php';
                            ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($faculty as $fac): ?>
                    <?php
                        $u = $fac['user'] ?? [];
                        $detail = $u['faculty_detail'] ?? [];
                        $fname = trim(($detail['first_name'] ?? $u['first_name'] ?? '') . ' ' . ($detail['last_name'] ?? $u['last_name'] ?? ''));
                        if (empty($fname)) {
                            $fname = $u['email'] ?? 'Instructor #' . ($fac['id'] ?? '');
                        }
                        $role = $u['role'] ?? 'Faculty';
                        $uStatus = $u['status'] ?? 'active';
                    ?>
                    <tr>
                        <td class="py-3 px-4 fw-semibold text-dark">
                            <?= htmlspecialchars($fname) ?>
                        </td>
                        <td class="px-4 text-muted font-monospace small">
                            <?= htmlspecialchars($u['email'] ?? '') ?>
                        </td>
                        <td class="px-3 text-center">
                            <?php if ($role === 'Dean'): ?>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Dean</span>
                            <?php else: ?>
                                <span class="badge badge-faculty">Faculty</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 text-center">
                            <?php if ($uStatus === 'active'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary border">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 text-end">
                            <a href="<?= url('/admin/faculty?search=' . urlencode($fname)) ?>" class="btn btn-sm btn-outline-secondary">
                                View account
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Department Modal -->
<div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-labelledby="editDepartmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('/admin/departments/' . $dept['id']) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="_redirect" value="<?= url('/admin/departments/' . $dept['id']) ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDepartmentModalLabel">Edit Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="code_<?= $dept['id'] ?>" class="form-label">Department abbrev <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-monospace" id="code_<?= $dept['id'] ?>" name="code" value="<?= htmlspecialchars($dept['code'] ?? '') ?>" required style="text-transform: uppercase;">
                        <div class="form-text">e.g., CITE</div>
                    </div>
                    <div class="mb-3">
                        <label for="name_<?= $dept['id'] ?>" class="form-label">Department name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name_<?= $dept['id'] ?>" name="name" value="<?= htmlspecialchars($dept['name'] ?? '') ?>" required>
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-check2"></i> Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('edit') === '1') {
        const modalEl = document.getElementById('editDepartmentModal');
        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
