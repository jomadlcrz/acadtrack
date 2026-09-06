<?php
$pageTitle = 'Students';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1 fw-semibold text-dark">Enrolled Students</h2>
        <p class="text-muted small mb-0">Class enrollment roster for the active course offering.</p>
    </div>
    <a href="<?= url('/faculty/subjects') ?>" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
        <i class="bi bi-arrow-left"></i> Back to subjects
    </a>
</div>

<div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h3 class="h6 mb-0 fw-semibold text-dark d-flex align-items-center gap-2">
            <i class="bi bi-people text-primary"></i> Registered Student Roster
        </h3>
        <span class="text-muted small"><?= count($students) ?> students enrolled</span>
    </div>

    <?php if (empty($students)): ?>
        <div class="card-body text-center py-5">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-3" style="width: 52px; height: 52px; font-size: 24px;">
                <i class="bi bi-person-x"></i>
            </div>
            <h4 class="h6 fw-semibold text-dark mb-1">No students enrolled</h4>
            <p class="text-muted small mb-0">No student records are currently enrolled in this subject offering.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light border-bottom">
                    <tr>
                        <th class="fw-semibold text-muted small py-3 px-3" style="width: 180px;">Student ID</th>
                        <th class="fw-semibold text-muted small py-3 px-3">Student name</th>
                        <th class="fw-semibold text-muted small py-3 px-3">Email address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td class="px-3 fw-semibold text-primary font-monospace small">
                            <i class="bi bi-person-vcard me-1 text-muted"></i>
                            <?= htmlspecialchars($student['student_number']) ?>
                        </td>
                        <td class="px-3 fw-semibold text-dark">
                            <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                        </td>
                        <td class="px-3 text-muted small">
                            <?= htmlspecialchars($student['email']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Students';
include __DIR__ . '/../../layouts/dashboard.php';
?>
