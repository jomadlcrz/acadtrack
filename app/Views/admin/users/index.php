<?php
$pageTitle = 'Manage Users';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1 fw-semibold text-dark">User Management</h2>
        <p class="text-muted small mb-0">Manage institutional accounts, role assignments, and system access.</p>
    </div>
    <a href="<?= url('/admin/users/create') ?>" class="btn btn-primary d-inline-flex align-items-center gap-2">
        <i class="bi bi-person-plus"></i> Add user
    </a>
</div>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="card-body py-3 px-4">
        <form method="GET" action="<?= url('/admin/users') ?>" class="row g-3 align-items-center">
            <div class="col-auto d-flex align-items-center gap-2">
                <label for="filter_role" class="form-label mb-0 fw-semibold small text-muted d-flex align-items-center gap-1">
                    <i class="bi bi-funnel text-primary"></i> Filter by role:
                </label>
                <select id="filter_role" name="role" class="form-select form-select-sm" style="width: 180px;" onchange="this.form.submit()">
                    <option value="">All roles</option>
                    <option value="Admin" <?= ($currentRole ?? '') === 'Admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="Dean" <?= ($currentRole ?? '') === 'Dean' ? 'selected' : '' ?>>Dean</option>
                    <option value="Faculty" <?= ($currentRole ?? '') === 'Faculty' ? 'selected' : '' ?>>Faculty</option>
                    <option value="Student" <?= ($currentRole ?? '') === 'Student' ? 'selected' : '' ?>>Student</option>
                </select>
            </div>
            <?php if (!empty($currentRole)): ?>
                <div class="col-auto">
                    <a href="<?= url('/admin/users') ?>" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
                        <i class="bi bi-x-circle"></i> Clear filter
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
                    <th class="fw-semibold text-muted small py-3 px-3">Role</th>
                    <th class="fw-semibold text-muted small py-3 px-3 text-end" style="width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users['data'])): ?>
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted small">
                            <i class="bi bi-people d-block fs-3 mb-2 text-secondary"></i>
                            No user accounts found matching the current filter.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users['data'] as $user): ?>
                    <tr>
                        <td class="px-3 fw-semibold text-dark"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                        <td class="px-3 text-muted small"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="px-3"><span class="badge badge-<?= strtolower($user['role']) ?>"><?= htmlspecialchars($user['role']) ?></span></td>
                        <td class="px-3 text-end">
                            <a href="<?= url('/admin/users/' . $user['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary py-1 px-2 d-inline-flex align-items-center gap-1">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form method="POST" action="<?= url('/admin/users/' . $user['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user account?');">
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

<?php if (!empty($users['data'])): ?>
    <div class="mt-3">
        <?php include __DIR__ . '/../../components/pagination.php'; ?>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
$pageTitle = 'Manage Users';
include __DIR__ . '/../../layouts/dashboard.php';
?>
