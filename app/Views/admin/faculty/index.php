<?php
$pageTitle = 'Faculty';
$subtitle = 'Manage faculty and academic head accounts.';
$headerActions = '<div class="dropdown">
    <button class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: #1e3a8a; border-color: #1e3a8a;"><i class="bi bi-plus-lg"></i> Add account</button>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
        <li><a class="dropdown-item" href="' . url('/admin/users/create?role=Faculty') . '">Faculty</a></li>
        <li><a class="dropdown-item" href="' . url('/admin/users/create?role=Dean') . '">Dean</a></li>
    </ul>
</div>';
$total = (int) ($faculty['total'] ?? 0);
ob_start();
?>

<?php
$activeFilterCount = 0;
if (!empty($currentRole)) { $activeFilterCount++; }
if (!empty($currentDepartment)) { $activeFilterCount++; }
if (!empty($currentStatus)) { $activeFilterCount++; }
$hasActiveFilters = $activeFilterCount > 0 || !empty($currentSearch);

$activeDeptName = '';
if (!empty($currentDepartment)) {
    foreach ($departments as $d) {
        if ((int)$d['id'] === (int)$currentDepartment) {
            $activeDeptName = $d['dept_name'];
            break;
        }
    }
}
?>

<form method="GET" action="<?= url('/admin/faculty') ?>" id="facultyFilterForm" class="mb-4">
    <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between">
        <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
            <div class="position-relative flex-grow-1" style="min-width: 240px; max-width: 360px;">
                <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 14px;"></i>
                <input type="text" name="search" class="form-control ps-5" placeholder="Search by name or email..."
                       value="<?= htmlspecialchars($currentSearch) ?>" autocomplete="off">
            </div>

            <!-- Single Consolidated Filter Dropdown Popover -->
            <div class="dropdown filter-dropdown">
                <button type="button" 
                        class="btn filter-trigger-btn <?= $activeFilterCount > 0 ? 'has-filters' : '' ?>" 
                        id="adminFacultyFilterBtn" 
                        data-bs-toggle="dropdown" 
                        data-bs-auto-close="outside" 
                        aria-expanded="false">
                    <i class="bi bi-filter"></i>
                    <span>Filters</span>
                    <?php if ($activeFilterCount > 0): ?>
                        <span class="badge bg-primary text-white rounded-pill px-1.5 py-0.5" style="font-size: 10px;"><?= $activeFilterCount ?></span>
                    <?php endif; ?>
                </button>
                <div class="dropdown-menu dropdown-menu-start filter-popover-panel" aria-labelledby="adminFacultyFilterBtn">
                    <div class="filter-popover-header">
                        <h6 class="filter-popover-title">
                            <i class="bi bi-filter text-primary"></i> Filter Faculty
                        </h6>
                        <?php if ($activeFilterCount > 0): ?>
                            <a href="<?= url('/admin/faculty' . (!empty($currentSearch) ? '?search=' . urlencode($currentSearch) : '')) ?>" class="filter-popover-reset"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
                        <?php endif; ?>
                    </div>
                    <div class="filter-popover-body">
                        <div class="filter-field-group">
                            <label class="filter-field-label">Role</label>
                            <select name="role" class="form-select form-select-sm">
                                <option value="">All roles</option>
                                <?php foreach (['Faculty', 'Dean'] as $r): ?>
                                    <option value="<?= $r ?>" <?= $currentRole === $r ? 'selected' : '' ?>><?= $r ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-field-group">
                            <label class="filter-field-label">Department</label>
                            <select name="department_id" class="form-select form-select-sm">
                                <option value="">All departments</option>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= (int) $d['id'] ?>" <?= (int) $currentDepartment === (int) $d['id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['dept_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-field-group">
                            <label class="filter-field-label">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All statuses</option>
                                <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $currentStatus === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="filter-popover-footer">
                        <button type="button" class="btn btn-sm btn-light" onclick="bootstrap.Dropdown.getInstance(document.getElementById('adminFacultyFilterBtn')).hide()">Close</button>
                        <button type="submit" class="btn btn-sm btn-primary px-3">Apply filters</button>
                    </div>
                </div>
            </div>

            <?php if ($hasActiveFilters): ?>
                <a href="<?= url('/admin/faculty') ?>" class="btn btn-outline-secondary btn-sm" title="Clear all filters">
                    <i class="bi bi-x-circle me-1"></i> Clear
                </a>
            <?php endif; ?>
        </div>

        <div class="text-muted small fw-medium text-nowrap"><?= number_format($total) ?> <?= $total === 1 ? 'account' : 'accounts' ?></div>
    </div>

    <!-- Active filter chips -->
    <?php if ($activeFilterCount > 0): ?>
        <div class="filter-chip-bar mt-2">
            <span class="small text-muted me-1">Active filters:</span>
            <?php if (!empty($currentRole)): ?>
                <span class="filter-chip">
                    <span class="chip-label">Role:</span> <?= htmlspecialchars($currentRole) ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($activeDeptName)): ?>
                <span class="filter-chip">
                    <span class="chip-label">Department:</span> <?= htmlspecialchars($activeDeptName) ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($currentStatus)): ?>
                <span class="filter-chip">
                    <span class="chip-label">Status:</span> <?= ucfirst($currentStatus) ?>
                </span>
            <?php endif; ?>
            <a href="<?= url('/admin/faculty' . (!empty($currentSearch) ? '?search=' . urlencode($currentSearch) : '')) ?>" class="filter-clear-all-chip">
                Clear filters
            </a>
        </div>
    <?php endif; ?>
</form>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="facultyTable">
            <thead class="bg-white border-bottom">
                <tr>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Name</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Role and department</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 120px; font-size: 11px;">Status</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="width: 140px; font-size: 11px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($faculty['data'])): ?>
                    <tr>
                        <td colspan="4" class="p-0">
                            <?php
                            $icon = 'bi-people';
                            $title = 'No accounts found';
                            $message = 'No faculty accounts match the current filters.';
                            include __DIR__ . '/../../components/empty-state.php';
                            ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($faculty['data'] as $user): ?>
                    <tr>
                        <td class="px-4">
                            <div class="fw-semibold text-dark"><?= htmlspecialchars(trim($user['first_name'] . ' ' . $user['last_name'])) ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($user['email']) ?></div>
                        </td>
                        <td class="px-3">
                            <span class="badge badge-<?= strtolower($user['role']) ?>"><?= htmlspecialchars($user['role']) ?></span>
                            <?php if (!empty($user['department_name'])): ?>
                                <div class="small text-muted mt-1"><?= htmlspecialchars($user['department_name']) ?> (<?= htmlspecialchars((string) $user['department_code']) ?>)</div>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 text-center">
                            <?php if (($user['status'] ?? 'active') === 'inactive'): ?>
                                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1"><i class="bi bi-dash-circle me-1"></i>Inactive</span>
                            <?php else: ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="bi bi-check-circle me-1"></i>Active</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 text-end text-nowrap"><?php include __DIR__ . '/../users/_row-actions.php'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
if (!empty($faculty['data'])) {
    $pagination = $faculty;
    include __DIR__ . '/../../components/pagination.php';
}
?>

<?php
$renderedModalIds = [];
foreach ($faculty['data'] ?? [] as $user) {
    $renderedModalIds[(int) $user['id']] = true;
    include __DIR__ . '/../users/_edit-modal.php';
}
if (!empty($editingUser) && empty($renderedModalIds[(int) $editingUser['id']])) {
    $user = $editingUser;
    include __DIR__ . '/../users/_edit-modal.php';
}
include __DIR__ . '/../users/_edit-modal-scripts.php';
?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
