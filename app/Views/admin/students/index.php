<?php
$pageTitle = 'Students';
$subtitle = 'Manage student accounts, year levels, and sections.';
$headerActions = '<a href="' . url('/admin/users/create?role=Student') . '" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5" style="background-color: #1e3a8a; border-color: #1e3a8a;">
    <i class="bi bi-plus-lg"></i> Add student
</a>';
$total = (int) ($students['total'] ?? 0);
$yearLabels = [1 => '1st Year', 2 => '2nd Year', 3 => '3rd Year', 4 => '4th Year'];
$hasActiveFilters = !empty($filters['search']) || !empty($filters['year_level']) || !empty($filters['set_id']) || !empty($filters['status']) || !empty($filters['account_status']);
ob_start();
?>

<?php
$activeFilterCount = 0;
if (!empty($filters['year_level'])) $activeFilterCount++;
if (!empty($filters['set_id'])) $activeFilterCount++;
if (!empty($filters['status'])) $activeFilterCount++;
if (!empty($filters['account_status'])) $activeFilterCount++;
?>
<form method="GET" action="<?= url('/admin/students') ?>" id="studentsFilterForm" class="mb-4">
    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between">
        <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
            <div class="position-relative flex-grow-1" style="min-width: 240px; max-width: 380px;">
                <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 14px;"></i>
                <input type="text" name="search" class="form-control ps-5" placeholder="Search by name, email, or student ID..."
                       value="<?= htmlspecialchars($filters['search']) ?>" autocomplete="off">
            </div>

            <!-- Single Consolidated Filter Dropdown (Linear / Stripe style) -->
            <div class="dropdown filter-dropdown">
                <button class="filter-trigger-btn <?= $activeFilterCount > 0 ? 'has-filters' : '' ?>" 
                        type="button" 
                        id="adminStudentsFilterBtn" 
                        data-bs-toggle="dropdown" 
                        data-bs-auto-close="outside" 
                        aria-expanded="false">
                    <i class="bi bi-filter"></i>
                    <span>Filters</span>
                    <?php if ($activeFilterCount > 0): ?>
                        <span class="badge bg-primary text-white rounded-pill px-1.5 py-0.5" style="font-size: 10px;"><?= $activeFilterCount ?></span>
                    <?php endif; ?>
                </button>
                <div class="dropdown-menu dropdown-menu-start filter-popover-panel" aria-labelledby="adminStudentsFilterBtn">
                    <div class="filter-popover-header">
                        <h6 class="filter-popover-title">
                            <i class="bi bi-filter text-primary"></i> Filter Students
                        </h6>
                        <?php if ($activeFilterCount > 0): ?>
                            <a href="<?= url('/admin/students' . (!empty($filters['search']) ? '?search=' . urlencode($filters['search']) : '')) ?>" class="filter-popover-reset">Reset</a>
                        <?php endif; ?>
                    </div>
                    <div class="filter-popover-body">
                        <div class="filter-field-group">
                            <label class="filter-field-label">Year level</label>
                            <select name="year_level" class="form-select form-select-sm">
                                <option value="">All year levels</option>
                                <?php foreach ($yearLabels as $lvl => $lbl): ?>
                                    <option value="<?= $lvl ?>" <?= (int) $filters['year_level'] === $lvl ? 'selected' : '' ?>><?= $lbl ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-field-group">
                            <label class="filter-field-label">Section</label>
                            <select name="set_id" class="form-select form-select-sm">
                                <option value="">All sections</option>
                                <?php foreach ($sets as $set): ?>
                                    <option value="<?= (int) $set['id'] ?>" <?= (int) $filters['set_id'] === (int) $set['id'] ? 'selected' : '' ?>><?= htmlspecialchars($set['set_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-field-group">
                            <label class="filter-field-label">Student standing</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All standings</option>
                                <option value="Regular" <?= $filters['status'] === 'Regular' ? 'selected' : '' ?>>Regular</option>
                                <option value="Irregular" <?= $filters['status'] === 'Irregular' ? 'selected' : '' ?>>Irregular</option>
                            </select>
                        </div>

                        <div class="filter-field-group">
                            <label class="filter-field-label">Account status</label>
                            <select name="account_status" class="form-select form-select-sm">
                                <option value="">All statuses</option>
                                <option value="active" <?= $filters['account_status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $filters['account_status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="filter-popover-footer">
                        <button type="button" class="btn btn-sm btn-light" onclick="bootstrap.Dropdown.getInstance(document.getElementById('adminStudentsFilterBtn')).hide()">Close</button>
                        <button type="submit" class="btn btn-sm btn-primary px-3">Apply filters</button>
                    </div>
                </div>
            </div>

            <?php if ($hasActiveFilters): ?>
                <a href="<?= url('/admin/students') ?>" class="btn btn-outline-secondary btn-sm" title="Clear all filters">
                    <i class="bi bi-x-circle me-1"></i> Clear
                </a>
            <?php endif; ?>
        </div>

        <div class="text-muted small fw-medium text-nowrap">
            <?= number_format($total) ?> <?= $total === 1 ? 'student' : 'students' ?>
        </div>
    </div>

    <!-- Active filter chips -->
    <?php if ($activeFilterCount > 0): ?>
        <div class="filter-chip-bar mt-2">
            <span class="small text-muted me-1">Active filters:</span>
            <?php if (!empty($filters['year_level']) && isset($yearLabels[$filters['year_level']])): ?>
                <span class="filter-chip">
                    <span class="chip-label">Year:</span> <?= $yearLabels[$filters['year_level']] ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($filters['set_id'])): ?>
                <?php
                $activeSetName = '';
                foreach ($sets as $s) {
                    if ((int)$s['id'] === (int)$filters['set_id']) { $activeSetName = $s['set_name']; break; }
                }
                ?>
                <?php if ($activeSetName): ?>
                    <span class="filter-chip">
                        <span class="chip-label">Section:</span> <?= htmlspecialchars($activeSetName) ?>
                    </span>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (!empty($filters['status'])): ?>
                <span class="filter-chip">
                    <span class="chip-label">Standing:</span> <?= htmlspecialchars($filters['status']) ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($filters['account_status'])): ?>
                <span class="filter-chip">
                    <span class="chip-label">Status:</span> <?= ucfirst($filters['account_status']) ?>
                </span>
            <?php endif; ?>
            <a href="<?= url('/admin/students') ?>" class="filter-clear-all-chip">Clear all</a>
        </div>
    <?php endif; ?>
</form>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="studentsTable">
            <thead class="bg-white border-bottom">
                <tr>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Student</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Student number</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Year and section</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Standing</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 120px; font-size: 11px;">Account</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="width: 120px; font-size: 11px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students['data'])): ?>
                    <tr>
                        <td colspan="6" class="p-0">
                            <?php
                            $icon = 'bi-people';
                            $title = 'No students found';
                            $message = 'No student accounts match the current filters.';
                            include __DIR__ . '/../../components/empty-state.php';
                            ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($students['data'] as $user): $user['role'] = 'Student'; ?>
                    <tr>
                        <td class="px-4">
                            <div class="fw-semibold text-dark"><?= htmlspecialchars(trim($user['first_name'] . ' ' . $user['last_name'])) ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($user['email']) ?></div>
                        </td>
                        <td class="px-3 font-monospace small"><?= !empty($user['student_number']) ? htmlspecialchars($user['student_number']) : '<span class="text-muted">No ID</span>' ?></td>
                        <td class="px-3">
                            <span class="badge bg-light text-secondary border"><?= $yearLabels[(int) $user['year_level']] ?? ('Year ' . (int) $user['year_level']) ?></span>
                            <?php if (!empty($user['set_name'])): ?>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($user['set_name']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3">
                            <span class="badge <?= ($user['student_status'] ?? '') === 'Regular' ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' ?>">
                                <?= htmlspecialchars($user['student_status'] ?? 'Regular') ?>
                            </span>
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
if (!empty($students['data'])) {
    $pagination = $students;
    include __DIR__ . '/../../components/pagination.php';
}
?>

<?php
$renderedModalIds = [];
foreach ($students['data'] ?? [] as $user) {
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
