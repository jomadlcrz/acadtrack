<?php
$pageTitle = 'User Management';
$subtitle = 'Manage institutional accounts, role assignments, and system access.';
$headerActions = '<a href="' . url('/admin/users/create') . '" class="btn btn-primary d-inline-flex align-items-center gap-2"><i class="bi bi-person-plus"></i> Add user</a>';
ob_start();
?>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="card-body py-3 px-4">
        <form method="GET" action="<?= url('/admin/users') ?>" class="row g-3 align-items-center">
            <div class="col-auto d-flex align-items-center gap-2">
                <label for="filter_role" class="form-label mb-0 fw-semibold small text-muted">Role:</label>
                <select id="filter_role" name="role" class="form-select" style="width: 160px;" onchange="this.form.submit()">
                    <option value="">All roles</option>
                    <option value="Admin" <?= ($currentRole ?? '') === 'Admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="Dean" <?= ($currentRole ?? '') === 'Dean' ? 'selected' : '' ?>>Dean</option>
                    <option value="Faculty" <?= ($currentRole ?? '') === 'Faculty' ? 'selected' : '' ?>>Faculty</option>
                    <option value="Student" <?= ($currentRole ?? '') === 'Student' ? 'selected' : '' ?>>Student</option>
                </select>
            </div>
            <div class="col-auto d-flex align-items-center gap-2">
                <label for="filter_status" class="form-label mb-0 fw-semibold small text-muted">Status:</label>
                <select id="filter_status" name="status" class="form-select" style="width: 160px;" onchange="this.form.submit()">
                    <option value="">All status</option>
                    <option value="active" <?= ($currentStatus ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($currentStatus ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <?php if (!empty($currentRole) || !empty($currentStatus)): ?>
                <div class="col-auto">
                    <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                        <i class="bi bi-x-circle"></i> Clear filters
                    </a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light border-bottom">
                <tr>
                    <th class="fw-semibold text-muted small py-3 px-3">Full name</th>
                    <th class="fw-semibold text-muted small py-3 px-3">Email address</th>
                    <th class="fw-semibold text-muted small py-3 px-3">Role &amp; affiliation</th>
                    <th class="fw-semibold text-muted small py-3 px-3 text-center" style="width: 120px;">Status</th>
                    <th class="fw-semibold text-muted small py-3 px-3 text-end" style="width: 190px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users['data'])): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted small">
                            <i class="bi bi-people d-block fs-3 mb-2 text-secondary"></i>
                            No user accounts found matching the current filter.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users['data'] as $user): ?>
                    <tr>
                        <td class="px-3 fw-semibold text-dark"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                        <td class="px-3 text-muted small"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="px-3">
                            <span class="badge badge-<?= strtolower($user['role']) ?>"><?= htmlspecialchars($user['role']) ?></span>
                            <?php if (in_array($user['role'], ['Faculty', 'Dean'], true) && !empty($user['department_name'])): ?>
                                <div class="small text-muted mt-1">
                                    <i class="bi bi-building me-1 text-primary"></i><?= htmlspecialchars($user['department_name']) ?> (<?= htmlspecialchars($user['department_code']) ?>)
                                </div>
                            <?php elseif ($user['role'] === 'Student'): ?>
                                <div class="small text-muted mt-1 font-monospace d-flex align-items-center gap-2">
                                    <span><?= !empty($user['student_number']) ? htmlspecialchars($user['student_number']) : 'No ID' ?></span>
                                    <?php if (!empty($user['section_name'])): ?>
                                        <span class="badge bg-light text-dark border"><i class="bi bi-collection me-1"></i><?= htmlspecialchars($user['section_name']) ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 text-center">
                            <?php if (($user['status'] ?? 'active') === 'inactive'): ?>
                                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                    <i class="bi bi-dash-circle me-1"></i>Inactive
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-check-circle me-1"></i>Active
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 text-end text-nowrap">
                            <a href="<?= url('/admin/users/' . $user['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary py-1 px-2 d-inline-flex align-items-center gap-1">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <?php if ($user['role'] === 'Admin'): ?>
                                <span class="badge bg-light text-secondary border py-1 px-2 d-inline-flex align-items-center gap-1" title="Administrator accounts cannot be deactivated to prevent system lockout">
                                    <i class="bi bi-shield-check text-primary"></i> Protected
                                </span>
                            <?php elseif (($user['status'] ?? 'active') === 'inactive'): ?>
                                <form method="POST" action="<?= url('/admin/users/' . $user['id'] . '/activate') ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to activate this user account?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2 d-inline-flex align-items-center gap-1" title="Activate account">
                                        <i class="bi bi-person-check"></i> Activate
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="<?= url('/admin/users/' . $user['id'] . '/deactivate') ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to deactivate this user account? The user will immediately be unable to sign in.');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-warning py-1 px-2 d-inline-flex align-items-center gap-1" title="Deactivate account">
                                        <i class="bi bi-person-x"></i> Deactivate
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (!empty($users['data'])): ?>
    <div class="mt-3">
        <?php include __DIR__ . '/../../components/pagination.php'; ?>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
