<?php
$activeRole = $selectedRole ?? $_POST['role'] ?? $_GET['role'] ?? 'Student';
$pageTitle = 'Add New ' . htmlspecialchars($activeRole);
$subtitle = 'Create a new authenticated ' . strtolower(htmlspecialchars($activeRole)) . ' account with institutional access privileges.';
$headerActions = '<a href="' . url('/admin/users') . '" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5"><i class="bi bi-arrow-left"></i> Back to users</a>';
ob_start();
?>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 10px; max-width: 720px; background: #ffffff;">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h6 mb-0.5 fw-semibold text-dark">Add New <?= htmlspecialchars($activeRole) ?></h3>
            <div class="text-muted" style="font-size: 12.5px;">Fill in the profile credentials and academic placement details below.</div>
        </div>
        <span class="badge badge-<?= strtolower($activeRole) ?> px-2.5 py-1 text-xs fw-semibold"><?= htmlspecialchars($activeRole) ?></span>
    </div>

    <form method="POST" action="<?= url('/admin/users') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="role" value="<?= htmlspecialchars($activeRole) ?>">
        <input type="hidden" name="force_password_change" value="1">

        <div class="card-body p-4">
            <!-- Section 1: Account Information -->
            <div class="mb-1">
                <div class="fw-semibold text-uppercase mb-3" style="font-size: 11px; letter-spacing: 0.05em; color: #475569;">
                    Account Information
                </div>

                <!-- Row 1: Name -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label">First name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="first_name" name="first_name" placeholder="e.g., Juan" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required autocomplete="given-name">
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label">Last name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="last_name" name="last_name" placeholder="e.g., Dela Cruz" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required autocomplete="family-name">
                    </div>
                </div>

                <!-- Row 2: Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="name@gwc.edu" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autocomplete="email">
                    <div class="form-text">A secure temporary password will be auto-generated and emailed to this address.</div>
                </div>
            </div>

            <?php if ($activeRole === 'Student'): ?>
                <!-- Section 2: Academic Placement -->
                <hr class="my-4" style="border-top: 1px solid #f1f5f9;">

                <div class="mb-1">
                    <div class="fw-semibold text-uppercase mb-3" style="font-size: 11px; letter-spacing: 0.05em; color: #475569;">
                        Academic Placement
                    </div>

                    <!-- Row 3 (Student): ID & Assigned Set -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="student_number" class="form-label">Student ID number <span class="text-muted fw-normal small">(Optional / Late ID)</span></label>
                            <input type="text" class="form-control" id="student_number" name="student_number" placeholder="e.g., 2026-0001 (leave blank if pending)" value="<?= htmlspecialchars($_POST['student_number'] ?? '') ?>">
                            <div class="form-text">Leave blank if pending official student ID issuance.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="set_id" class="form-label">Assigned class set <span class="text-danger">*</span></label>
                            <select class="form-select" id="set_id" name="set_id" required>
                                <option value="">Select class set...</option>
                                <?php foreach ($sets ?? [] as $set): ?>
                                    <option value="<?= $set['id'] ?>" <?= ((string)($_POST['set_id'] ?? '') === (string)$set['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($set['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Active academic term cohort and schedule.</div>
                        </div>
                    </div>

                    <!-- Row 4 (Student): Year Level & Enrollment Status -->
                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <label for="year_level" class="form-label">Year level <span class="text-danger">*</span></label>
                            <select class="form-select" id="year_level" name="year_level">
                                <option value="1" <?= ($_POST['year_level'] ?? '1') === '1' ? 'selected' : '' ?>>1st Year</option>
                                <option value="2" <?= ($_POST['year_level'] ?? '') === '2' ? 'selected' : '' ?>>2nd Year</option>
                                <option value="3" <?= ($_POST['year_level'] ?? '') === '3' ? 'selected' : '' ?>>3rd Year</option>
                                <option value="4" <?= ($_POST['year_level'] ?? '') === '4' ? 'selected' : '' ?>>4th Year</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="student_status" class="form-label">Enrollment status <span class="text-danger">*</span></label>
                            <select class="form-select" id="student_status" name="student_status">
                                <option value="Regular" <?= ($_POST['student_status'] ?? 'Regular') === 'Regular' ? 'selected' : '' ?>>Regular</option>
                                <option value="Irregular" <?= ($_POST['student_status'] ?? '') === 'Irregular' ? 'selected' : '' ?>>Irregular</option>
                            </select>
                        </div>
                    </div>
                </div>

            <?php elseif (in_array($activeRole, ['Faculty', 'Dean'], true)): ?>
                <!-- Section 2: Department Affiliation -->
                <hr class="my-4" style="border-top: 1px solid #f1f5f9;">

                <div class="mb-1">
                    <div class="fw-semibold text-uppercase mb-3" style="font-size: 11px; letter-spacing: 0.05em; color: #475569;">
                        Department Affiliation
                    </div>

                    <div class="mb-2">
                        <label for="department_id" class="form-label">College / Academic department <span class="text-danger">*</span></label>
                        <select class="form-select" id="department_id" name="department_id" required>
                            <option value="">Select college / department...</option>
                            <?php foreach ($departments ?? [] as $dept): ?>
                                <option value="<?= $dept['id'] ?>" <?= ((string)($_POST['department_id'] ?? '') === (string)$dept['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['name']) ?> (<?= htmlspecialchars($dept['code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">The collegiate division or academic program this <?= strtolower(htmlspecialchars($activeRole)) ?> is assigned to.</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="card-footer bg-light py-3 px-4 border-top d-flex justify-content-end align-items-center gap-2" style="border-bottom-left-radius: 10px; border-bottom-right-radius: 10px;">
            <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3">
                <i class="bi bi-check2"></i> Create <?= strtolower(htmlspecialchars($activeRole)) ?>
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
