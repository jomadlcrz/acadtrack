<?php
$pageTitle = 'Faculty and Staff';
$subtitle = 'Manage faculty, dean, and administrator accounts.';
$headerActions = '<div class="dropdown">
    <button class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: #1e3a8a; border-color: #1e3a8a;"><i class="bi bi-plus-lg"></i> Add account</button>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
        <li><a class="dropdown-item" href="' . url('/admin/users/create?role=Faculty') . '">Faculty</a></li>
        <li><a class="dropdown-item" href="' . url('/admin/users/create?role=Dean') . '">Dean</a></li>
        <li><a class="dropdown-item" href="' . url('/admin/users/create?role=Admin') . '">Administrator</a></li>
    </ul>
</div>';
$total = (int) ($staff['total'] ?? 0);
ob_start();
?>

<form method="GET" action="<?= url('/admin/staff') ?>" id="staffFilterForm"
      class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between mb-4">
    <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
        <div class="position-relative flex-grow-1" style="min-width: 220px; max-width: 340px;">
            <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 14px;"></i>
            <input type="text" name="search" class="form-control ps-5" placeholder="Search by name or email..."
                   value="<?= htmlspecialchars($currentSearch) ?>" autocomplete="off">
        </div>

        <select name="role" class="form-select w-auto" onchange="this.form.submit()">
            <option value="">All roles</option>
            <?php foreach (['Faculty', 'Dean', 'Admin'] as $r): ?>
                <option value="<?= $r ?>" <?= $currentRole === $r ? 'selected' : '' ?>><?= $r ?></option>
            <?php endforeach; ?>
        </select>

        <select name="department_id" class="form-select w-auto" onchange="this.form.submit()">
            <option value="">All departments</option>
            <?php foreach ($departments as $d): ?>
                <option value="<?= (int) $d['id'] ?>" <?= (int) $currentDepartment === (int) $d['id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['dept_name']) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="status" class="form-select w-auto" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <option value="active" <?= $currentStatus === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $currentStatus === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
    </div>

    <div class="text-muted small fw-medium text-nowrap"><?= number_format($total) ?> <?= $total === 1 ? 'account' : 'accounts' ?></div>
</form>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="staffTable">
            <thead class="bg-white border-bottom">
                <tr>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Name</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Role and department</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 120px; font-size: 11px;">Status</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="width: 140px; font-size: 11px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($staff['data'])): ?>
                    <tr>
                        <td colspan="4" class="p-0">
                            <?php
                            $icon = 'bi-people';
                            $title = 'No accounts found';
                            $message = 'No faculty or staff accounts match the current filters.';
                            include __DIR__ . '/../../components/empty-state.php';
                            ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($staff['data'] as $user): ?>
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
if (!empty($staff['data'])) {
    $pagination = $staff;
    include __DIR__ . '/../../components/pagination.php';
}
?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
