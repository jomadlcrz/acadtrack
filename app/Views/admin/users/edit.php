<?php
$pageTitle = 'Edit User';
$subtitle = 'Modify account credentials and privilege assignments for ' . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . '.';
$headerActions = '<a href="' . url('/admin/users') . '" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"><i class="bi bi-arrow-left"></i> Back to users</a>';
ob_start();
?>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; max-width: 760px;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h6 mb-0 fw-semibold text-dark">Account Profile &amp; Privilege Level</h3>
            <small class="text-muted">Changes take effect immediately across all active sessions.</small>
        </div>
        <span class="badge badge-<?= strtolower($user['role']) ?>"><?= htmlspecialchars($user['role']) ?></span>
    </div>

    <form method="POST" action="<?= url('/admin/users/' . $user['id']) ?>">
        <?= csrf_field() ?>

        <div class="card-body p-4">
            <!-- Row 1: Name -->
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

            <!-- Row 2: Email Address -->
            <div class="mb-3">
                <label for="email_display" class="form-label">Email address</label>
                <input type="email" class="form-control bg-light" id="email_display" value="<?= htmlspecialchars($user['email']) ?>" readonly disabled>
                <div class="form-text">Primary institutional account identifier (cannot be modified).</div>
            </div>

            <?php if ($user['role'] === 'Student'): ?>
                <!-- Row 3 (Student): ID & Assigned Set -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="student_number" class="form-label">Student ID number <span class="text-muted">(Optional / Late ID)</span></label>
                        <input type="text" class="form-control" id="student_number" name="student_number" placeholder="e.g., 2026-0001 (leave blank if pending)" value="<?= htmlspecialchars($user['student_number'] ?? '') ?>">
                        <div class="form-text">Optional for students with late or pending ID. Must be unique if provided.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="set_id" class="form-label">Assigned set <span class="text-danger">*</span></label>
                        <select class="form-select" id="set_id" name="set_id" required>
                            <option value="">Select set...</option>
                            <?php foreach ($sets ?? [] as $set): ?>
                                <option value="<?= $set['id'] ?>" <?= ((string)($user['student']['set_id'] ?? '') === (string)$set['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($set['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Active class set batch.</div>
                    </div>
                </div>

                <!-- Row 4 (Student): Year Level & Enrollment Status -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="year_level" class="form-label">Year level <span class="text-danger">*</span></label>
                        <select class="form-select" id="year_level" name="year_level">
                            <?php $currentYearLevel = (int)($user['student']['year_level'] ?? 1); ?>
                            <option value="1" <?= $currentYearLevel === 1 ? 'selected' : '' ?>>1st Year</option>
                            <option value="2" <?= $currentYearLevel === 2 ? 'selected' : '' ?>>2nd Year</option>
                            <option value="3" <?= $currentYearLevel === 3 ? 'selected' : '' ?>>3rd Year</option>
                            <option value="4" <?= $currentYearLevel === 4 ? 'selected' : '' ?>>4th Year</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="student_status" class="form-label">Enrollment status <span class="text-danger">*</span></label>
                        <select class="form-select" id="student_status" name="student_status">
                            <?php $currentStatus = $user['student']['status'] ?? 'Regular'; ?>
                            <option value="Regular" <?= $currentStatus === 'Regular' ? 'selected' : '' ?>>Regular</option>
                            <option value="Irregular" <?= $currentStatus === 'Irregular' ? 'selected' : '' ?>>Irregular</option>
                        </select>
                    </div>
                </div>

            <?php elseif (in_array($user['role'], ['Faculty', 'Dean'], true)): ?>
                <!-- Row 3 (Faculty/Dean): Department -->
                <div class="mb-3">
                    <label for="department_id" class="form-label">Faculty department / College <span class="text-danger">*</span></label>
                    <select class="form-select" id="department_id" name="department_id" required>
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
            <?php endif; ?>
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
include __DIR__ . '/../../layouts/dashboard.php';
?>
