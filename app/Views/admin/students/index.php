<?php
$pageTitle = 'Students';
$subtitle = 'Students registered for the selected term. Regular and Irregular status is per term.';
$headerActions = '<div class="d-flex align-items-center gap-2">
    <a href="' . url('/admin/users/import-template') . '" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5"><i class="bi bi-download"></i> Download template</a>
    <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#importExcelModal"><i class="bi bi-file-earmark-excel"></i> Import spreadsheet</button>
    <a href="' . url('/admin/users/create?role=Student') . '" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5" style="background-color: #1e3a8a; border-color: #1e3a8a;"><i class="bi bi-plus-lg"></i> Add student</a>
</div>';
$termLabel = static fn(array $t): string => ($t['academic_year_name'] ?? '') . ' - Semester ' . ($t['semester'] ?? '');
$total = (int) ($students['total'] ?? 0);
ob_start();
?>

<form method="GET" action="<?= url('/admin/students') ?>" id="studentsFilterForm"
      class="d-flex flex-column flex-xl-row gap-3 align-items-xl-center justify-content-between mb-3">
    <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
        <select name="term_id" class="form-select w-auto" onchange="this.form.submit()">
            <?php foreach ($terms as $t): ?>
                <option value="<?= (int) $t['id'] ?>" <?= (int) $t['id'] === (int) $termId ? 'selected' : '' ?>><?= htmlspecialchars($termLabel($t)) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="position-relative flex-grow-1" style="min-width: 220px; max-width: 340px;">
            <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 14px;"></i>
            <input type="text" name="search" class="form-control ps-5" placeholder="Search by name, email, or student ID..."
                   value="<?= htmlspecialchars($filters['search']) ?>" autocomplete="off">
        </div>

        <select name="status" class="form-select w-auto" onchange="this.form.submit()">
            <option value="">Regular and irregular</option>
            <option value="Regular" <?= $filters['status'] === 'Regular' ? 'selected' : '' ?>>Regular</option>
            <option value="Irregular" <?= $filters['status'] === 'Irregular' ? 'selected' : '' ?>>Irregular</option>
        </select>

        <select name="year_level" class="form-select w-auto" onchange="this.form.submit()">
            <option value="">All years</option>
            <?php for ($y = 1; $y <= 5; $y++): ?>
                <option value="<?= $y ?>" <?= (int) $filters['year_level'] === $y ? 'selected' : '' ?>>Year <?= $y ?></option>
            <?php endfor; ?>
        </select>

        <select name="set_id" class="form-select w-auto" onchange="this.form.submit()">
            <option value="">All sets</option>
            <?php foreach ($sets as $set): ?>
                <option value="<?= (int) $set['id'] ?>" <?= (int) $filters['set_id'] === (int) $set['id'] ? 'selected' : '' ?>><?= htmlspecialchars($set['set_name']) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="account_status" class="form-select w-auto" onchange="this.form.submit()">
            <option value="">All accounts</option>
            <option value="active" <?= $filters['account_status'] === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $filters['account_status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
    </div>

    <div class="text-muted small fw-medium text-nowrap">
        <?= number_format($total) ?> <?= $total === 1 ? 'student' : 'students' ?>
    </div>
</form>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div class="small text-muted">
        <?php if ($unregistered > 0): ?>
            <i class="bi bi-info-circle"></i>
            <?= (int) $unregistered ?> <?= $unregistered === 1 ? 'student is' : 'students are' ?> not registered for this term.
        <?php endif; ?>
    </div>

    <?php if ($nextTerm): ?>
        <form method="POST" action="<?= url('/admin/registrations/rollover') ?>"
              onsubmit="return confirm('Register all enrolled students of this term for the next term?');" class="m-0">
            <?= csrf_field() ?>
            <input type="hidden" name="from_term_id" value="<?= (int) $termId ?>">
            <button type="submit" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1.5">
                <i class="bi bi-arrow-right-circle"></i> Roll over to Semester <?= (int) $nextTerm['semester'] ?>
            </button>
        </form>
    <?php else: ?>
        <span class="badge bg-light text-secondary border px-2.5 py-1.5">Next term not created yet</span>
    <?php endif; ?>
</div>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="studentsTable">
            <thead class="bg-white border-bottom">
                <tr>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Student</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Student number</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Year and set</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Status</th>
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
                            $message = 'No students are registered for this term with the current filters.';
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
                        <td class="px-3 font-monospace small"><?= !empty($user['student_number']) ? htmlspecialchars($user['student_number']) : 'No ID' ?></td>
                        <td class="px-3">
                            <span class="badge bg-light text-secondary border">Year <?= (int) $user['year_level'] ?></span>
                            <?php if (!empty($user['set_name'])): ?>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($user['set_name']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3">
                            <span class="badge <?= $user['student_status'] === 'Regular' ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' ?>">
                                <?= htmlspecialchars($user['student_status']) ?>
                            </span>
                            <?php if ($user['registration_status'] !== 'enrolled'): ?>
                                <span class="badge bg-secondary-subtle text-secondary border"><?= htmlspecialchars(ucfirst($user['registration_status'])) ?></span>
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
if (!empty($students['data'])) {
    $pagination = $students;
    include __DIR__ . '/../../components/pagination.php';
}
?>

<?php include __DIR__ . '/../users/_import-modal.php'; ?>

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
