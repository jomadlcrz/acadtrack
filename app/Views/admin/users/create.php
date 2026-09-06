<?php
$pageTitle = 'Add New User';
$subtitle = 'Create a new authenticated account and assign institutional access privileges.';
$headerActions = '<a href="' . url('/admin/users') . '" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"><i class="bi bi-arrow-left"></i> Back to users</a>';
ob_start();
?>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; max-width: 760px;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h6 mb-0 fw-semibold text-dark d-flex align-items-center gap-2">
                <i class="bi bi-person-plus text-primary"></i> Account Credentials & Profile
            </h3>
            <small class="text-muted">All primary fields are required for initial account provisioning.</small>
        </div>
    </div>

    <form method="POST" action="<?= url('/admin/users') ?>" novalidate>
        <?= csrf_field() ?>

        <div class="card-body p-4">
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="first_name" class="form-label">First name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="first_name" name="first_name" placeholder="e.g., Juan" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="last_name" class="form-label">Last name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="last_name" name="last_name" placeholder="e.g., Dela Cruz" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email address <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" placeholder="name@gwc.edu" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-text">Must be a valid institutional or personal email address.</div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Initial password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to auto-generate a random secure password">
                </div>
                <div class="form-text">If left blank, a strong temporary password will be randomly generated and emailed.</div>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" value="1" id="force_password_change" name="force_password_change" checked>
                <label class="form-check-label text-dark" for="force_password_change">
                    Require user to set a new password upon first sign in
                </label>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="role" class="form-label">System role <span class="text-danger">*</span></label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="Student" <?= ($_POST['role'] ?? '') === 'Student' ? 'selected' : '' ?>>Student</option>
                        <option value="Faculty" <?= ($_POST['role'] ?? '') === 'Faculty' ? 'selected' : '' ?>>Faculty</option>
                        <option value="Dean" <?= ($_POST['role'] ?? '') === 'Dean' ? 'selected' : '' ?>>Dean</option>
                        <option value="Admin" <?= ($_POST['role'] ?? '') === 'Admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <div class="form-text">Determines system permissions and portal navigation.</div>
                </div>

                <div class="col-md-6">
                    <label for="student_number" class="form-label">Student number</label>
                    <input type="text" class="form-control" id="student_number" name="student_number" placeholder="e.g., 2026-0001 (optional for late ID)" value="<?= htmlspecialchars($_POST['student_number'] ?? '') ?>">
                    <div class="form-text">Optional for students with late or pending ID.</div>
                </div>
            </div>

            <div class="mb-3" id="department_group">
                <label for="department_id" class="form-label">Department / College</label>
                <select class="form-select" id="department_id" name="department_id">
                    <option value="">Select department (assigned to Faculty and Dean)</option>
                    <?php foreach ($departments ?? [] as $dept): ?>
                        <option value="<?= $dept['id'] ?>" <?= ((string)($_POST['department_id'] ?? '') === (string)$dept['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dept['name']) ?> (<?= htmlspecialchars($dept['code']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Designates the academic department or college this faculty member or dean belongs to.</div>
            </div>
        </div>

        <div class="card-footer bg-light py-3 border-top d-flex justify-content-end align-items-center gap-2">
            <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                <i class="bi bi-check2"></i> Create user
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
