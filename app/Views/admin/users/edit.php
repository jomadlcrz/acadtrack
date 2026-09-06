<?php
$pageTitle = 'Edit User';
$subtitle = 'Modify account credentials and privilege assignments for ' . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . '.';
$headerActions = '<a href="' . url('/admin/users') . '" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"><i class="bi bi-arrow-left"></i> Back to users</a>';
ob_start();
?>

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

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" value="1" id="force_password_change" name="force_password_change" <?= !empty($user['force_password_change']) ? 'checked' : '' ?>>
                <label class="form-check-label text-dark" for="force_password_change">
                    Require user to set a new password upon next sign in
                </label>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="role" class="form-label">System role <span class="text-danger">*</span></label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="Student" <?= $user['role'] === 'Student' ? 'selected' : '' ?>>Student</option>
                        <option value="Faculty" <?= $user['role'] === 'Faculty' ? 'selected' : '' ?>>Faculty</option>
                        <option value="Dean" <?= $user['role'] === 'Dean' ? 'selected' : '' ?>>Dean</option>
                        <option value="Admin" <?= $user['role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <div class="form-text">Assigning a new role adjusts permissions immediately.</div>
                </div>

                <div class="col-md-6" id="student_number_group">
                    <label for="student_number" class="form-label">Student ID number <span class="text-muted">(Optional / Late ID)</span></label>
                    <input type="text" class="form-control" id="student_number" name="student_number" placeholder="e.g., 2026-0001 (leave blank if pending)" value="<?= htmlspecialchars($user['student_number'] ?? '') ?>">
                    <div class="form-text">Optional for students with late or pending ID. Must be unique if provided.</div>
                </div>

                <div class="col-md-6" id="department_group" style="display: none;">
                    <label for="department_id" class="form-label">Faculty department / College <span class="text-danger">*</span></label>
                    <select class="form-select" id="department_id" name="department_id">
                        <option value="">Select department</option>
                        <?php
                        $currentDeptId = $user['faculty']['department_id'] ?? null;
                        foreach ($departments ?? [] as $dept):
                        ?>
                            <option value="<?= $dept['id'] ?>" <?= ((string)$currentDeptId === (string)$dept['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept['name']) ?> (<?= htmlspecialchars($dept['code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Academic college/department the instructor or dean belongs to.</div>
                </div>
            </div>

            <div class="row g-3 mb-3" id="student_details_group">
                <?php
                $currentSectionId = $user['student']['section_id'] ?? null;
                $currentYearLevel = (int)($user['student']['year_level'] ?? 1);
                $currentStatus = $user['student']['status'] ?? 'Regular';
                ?>
                <div class="col-md-4">
                    <label for="section_id" class="form-label">Assigned section <span class="text-danger">*</span></label>
                    <select class="form-select" id="section_id" name="section_id" required>
                        <option value="">Select section...</option>
                        <?php foreach ($sections ?? [] as $sec): ?>
                            <option value="<?= $sec['id'] ?>" <?= ((string)$currentSectionId === (string)$sec['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sec['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Active class section cohort.</div>
                </div>
                <div class="col-md-4">
                    <label for="year_level" class="form-label">Year level <span class="text-danger">*</span></label>
                    <select class="form-select" id="year_level" name="year_level">
                        <option value="1" <?= $currentYearLevel === 1 ? 'selected' : '' ?>>1st Year</option>
                        <option value="2" <?= $currentYearLevel === 2 ? 'selected' : '' ?>>2nd Year</option>
                        <option value="3" <?= $currentYearLevel === 3 ? 'selected' : '' ?>>3rd Year</option>
                        <option value="4" <?= $currentYearLevel === 4 ? 'selected' : '' ?>>4th Year</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="student_status" class="form-label">Enrollment status <span class="text-danger">*</span></label>
                    <select class="form-select" id="student_status" name="student_status">
                        <option value="Regular" <?= $currentStatus === 'Regular' ? 'selected' : '' ?>>Regular</option>
                        <option value="Irregular" <?= $currentStatus === 'Irregular' ? 'selected' : '' ?>>Irregular</option>
                    </select>
                </div>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.getElementById('role');
    const studentGroup = document.getElementById('student_number_group');
    const studentDetailsGroup = document.getElementById('student_details_group');
    const deptGroup = document.getElementById('department_group');
    const studentInput = document.getElementById('student_number');
    const sectionSelect = document.getElementById('section_id');
    const deptSelect = document.getElementById('department_id');

    function syncRoleFields() {
        const role = roleSelect.value;
        if (role === 'Student') {
            studentGroup.style.display = 'block';
            studentDetailsGroup.style.display = 'flex';
            if (sectionSelect) sectionSelect.required = true;
            deptGroup.style.display = 'none';
        } else if (role === 'Faculty' || role === 'Dean') {
            studentGroup.style.display = 'none';
            studentDetailsGroup.style.display = 'none';
            if (sectionSelect) sectionSelect.required = false;
            deptGroup.style.display = 'block';
        } else {
            studentGroup.style.display = 'none';
            studentDetailsGroup.style.display = 'none';
            if (sectionSelect) sectionSelect.required = false;
            deptGroup.style.display = 'none';
        }
    }

    roleSelect.addEventListener('change', syncRoleFields);
    syncRoleFields();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
