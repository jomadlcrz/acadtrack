<?php
$pageTitle = 'Edit User';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1 fw-semibold text-dark">Edit User</h2>
        <p class="text-muted small mb-0">Modify account credentials and privilege assignments for <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>.</p>
    </div>
    <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
        <i class="bi bi-arrow-left"></i> Back to users
    </a>
</div>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; max-width: 760px;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h6 mb-0 fw-semibold text-dark d-flex align-items-center gap-2">
                <i class="bi bi-person-gear text-primary"></i> Account Profile & Privilege Level
            </h3>
            <small class="text-muted">Changes take effect immediately across all active sessions.</small>
        </div>
        <span class="badge badge-<?= strtolower($user['role']) ?>"><?= htmlspecialchars($user['role']) ?></span>
    </div>

    <form method="POST" action="<?= url('/admin/users/' . $user['id']) ?>" novalidate>
        <?= csrf_field() ?>

        <div class="card-body p-4">
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="first_name" class="form-label">First name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="last_name" class="form-label">Last name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email address <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="form-text">Institutional communication and password recovery notices are sent here.</div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">New password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to preserve current password">
                </div>
                <div class="form-text">Enter a new password only if you wish to reset this user's sign-in credentials.</div>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">System role <span class="text-danger">*</span></label>
                <select class="form-select" id="role" name="role" required>
                    <option value="Student" <?= $user['role'] === 'Student' ? 'selected' : '' ?>>Student</option>
                    <option value="Faculty" <?= $user['role'] === 'Faculty' ? 'selected' : '' ?>>Faculty</option>
                    <option value="Dean" <?= $user['role'] === 'Dean' ? 'selected' : '' ?>>Dean</option>
                    <option value="Admin" <?= $user['role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                </select>
                <div class="form-text">Assigning a new role adjusts the navigation landmarks and authorization privileges immediately.</div>
            </div>
        </div>

        <div class="card-footer bg-light py-3 border-top d-flex justify-content-end align-items-center gap-2">
            <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                <i class="bi bi-check2"></i> Update user
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Edit User';
include __DIR__ . '/../../layouts/dashboard.php';
?>
