<?php
$pageTitle = 'Faculty Subject Assignments';
$subtitle = 'Assign teaching instructors to official college curriculum subjects for the active semester.';
ob_start();
?>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3 class="h6 mb-0 fw-semibold text-dark d-flex align-items-center gap-2">
                <i class="bi bi-person-badge text-primary"></i> Assign Instructor to Course
            </h3>
            <small class="text-muted">Select an accredited faculty member and designate an active curricular course offering &bull; <?= htmlspecialchars($academicTerm['name'] ?? 'Active Term') ?></small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small fw-semibold">Semester:</span>
            <div class="btn-group btn-group-sm" role="group" aria-label="Semester selection">
                <a href="<?= url('/dean/faculty-assignments?semester=1') ?>" class="btn <?= ($selectedSemester ?? '1') === '1' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    1st Semester
                </a>
                <a href="<?= url('/dean/faculty-assignments?semester=2') ?>" class="btn <?= ($selectedSemester ?? '1') === '2' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    2nd Semester
                </a>
            </div>
        </div>
    </div>

    <form method="POST" action="<?= url('/dean/faculty-assignments/assign') ?>" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="academic_term_id" value="<?= htmlspecialchars((string)($academicTerm['id'] ?? 1)) ?>">
        <input type="hidden" name="semester" value="<?= htmlspecialchars((string)($selectedSemester ?? '1')) ?>">

        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="faculty_id" class="form-label">Faculty member <span class="text-danger">*</span></label>
                    <select class="form-select" id="faculty_id" name="faculty_id" required>
                        <option value="">Select faculty member</option>
                        <?php foreach ($faculty as $f): ?>
                            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['first_name'] . ' ' . $f['last_name']) ?> (<?= htmlspecialchars($f['email']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Instructors granted assignment can encode grades for enrolled students.</div>
                </div>

                <div class="col-md-6">
                    <label for="subject_id" class="form-label">Subject offering <span class="text-danger">*</span></label>
                    <select class="form-select" id="subject_id" name="subject_id" required>
                        <option value="">Select subject</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['code']) ?> — <?= htmlspecialchars($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Course subjects active in the current institutional curriculum.</div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-light py-3 border-top d-flex justify-content-end align-items-center gap-2">
            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                <i class="bi bi-plus-circle"></i> Assign instructor
            </button>
        </div>
    </form>
</div>

<div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h3 class="h6 mb-0 fw-semibold text-dark d-flex align-items-center gap-2">
            <i class="bi bi-journal-bookmark text-primary"></i> Course Allocation Roster
        </h3>
        <span class="text-muted small"><?= count($subjects) ?> courses registered</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light border-bottom">
                <tr>
                    <th class="fw-semibold text-muted small py-3 px-3" style="width: 160px;">Subject code</th>
                    <th class="fw-semibold text-muted small py-3 px-3">Descriptive title</th>
                    <th class="fw-semibold text-muted small py-3 px-3" style="width: 180px;">Assigned faculty</th>
                    <th class="fw-semibold text-muted small py-3 px-3 text-end" style="width: 200px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($subjects)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted small">
                            <i class="bi bi-journal-x d-block fs-3 mb-2 text-secondary"></i>
                            No course offerings available in the system yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($subjects as $subject): ?>
                    <tr>
                        <td class="px-3 fw-semibold text-primary font-monospace"><?= htmlspecialchars($subject['code']) ?></td>
                        <td class="px-3 fw-semibold text-dark"><?= htmlspecialchars($subject['name']) ?></td>
                        <td class="px-3">
                            <?php $count = (int)($subject['assigned_faculty_count'] ?? 0); ?>
                            <?php if ($count > 0): ?>
                                <span class="badge badge-faculty">
                                    <i class="bi bi-check-circle me-1"></i> <?= $count ?> assigned
                                </span>
                            <?php else: ?>
                                <span class="badge badge-draft">
                                    <i class="bi bi-dash-circle me-1"></i> Unassigned
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 text-end">
                            <?php if ($count > 0): ?>
                                <form method="POST" action="<?= url('/dean/faculty-assignments/remove') ?>" class="d-inline" onsubmit="return confirm('Remove faculty assignment for <?= htmlspecialchars($subject['code']) ?>?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="subject_id" value="<?= $subject['id'] ?>">
                                    <input type="hidden" name="academic_term_id" value="<?= htmlspecialchars((string)($academicTerm['id'] ?? 1)) ?>">
                                    <input type="hidden" name="semester" value="<?= htmlspecialchars((string)($selectedSemester ?? '1')) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2 d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-person-x"></i> Remove assignment
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
