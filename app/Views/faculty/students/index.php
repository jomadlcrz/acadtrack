<?php
$pageTitle = 'Enrolled Students';
$subtitle = 'Class enrollment roster and student classification for the active course offering.';
$headerActions = '
    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addStudentModal">
        <i class="bi bi-person-plus"></i> Add new student
    </button>
    <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#enrollExistingModal">
        <i class="bi bi-person-check"></i> Select existing student
    </button>
    <a href="' . url('/faculty/subjects') . '" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
        <i class="bi bi-arrow-left"></i> Back to subjects
    </a>';
ob_start();
?>

<!-- Course Selector Toolbar -->
<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="card-body py-3 px-4">
        <form method="GET" action="<?= url('/faculty/students') ?>" class="row g-3 align-items-center">
            <div class="col-auto d-flex align-items-center gap-2">
                <label for="semester_select" class="form-label mb-0 fw-semibold small text-muted d-flex align-items-center gap-1">
                    <i class="bi bi-calendar3 text-primary"></i> Semester:
                </label>
                <select id="semester_select" name="semester" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="1" <?= ($selectedSemester ?? '1') === '1' ? 'selected' : '' ?>>1st Semester</option>
                    <option value="2" <?= ($selectedSemester ?? '1') === '2' ? 'selected' : '' ?>>2nd Semester</option>
                </select>
            </div>

            <div class="col-auto d-flex align-items-center gap-2">
                <label for="subject_id_select" class="form-label mb-0 fw-semibold small text-muted d-flex align-items-center gap-1">
                    <i class="bi bi-book text-primary"></i> Active subject:
                </label>
                <select id="subject_id_select" name="subject_id" class="form-select form-select-sm" style="min-width: 280px;" onchange="this.form.submit()">
                    <?php foreach (($assignedSubjects ?? []) as $sub): ?>
                        <option value="<?= $sub['id'] ?>" <?= ((int)$sub['id'] === (int)($subjectId ?? 0)) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sub['code'] . ' - ' . $sub['name']) ?>
                        </option>
                </select>
            </div>

            <div class="col-auto d-flex align-items-center gap-2">
                <label for="section_id_filter" class="form-label mb-0 fw-semibold small text-muted d-flex align-items-center gap-1">
                    <i class="bi bi-collection text-primary"></i> Section:
                </label>
                <select id="section_id_filter" name="section_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All sections</option>
                    <?php foreach (($sections ?? []) as $sec): ?>
                        <option value="<?= $sec['id'] ?>" <?= ((int)($selectedSection ?? 0) === (int)$sec['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sec['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if (!empty($currentSubject)): ?>
                <div class="col-auto">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                        <?= htmlspecialchars($currentSubject['nature'] ?? 'Lecture') ?>
                    </span>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h3 class="h6 mb-0 fw-semibold text-dark d-flex align-items-center gap-2">
            <i class="bi bi-people text-primary"></i> Class Enrollment Roster
        </h3>
        <span class="text-muted small"><?= count($students) ?> students enrolled</span>
    </div>

    <?php if (empty($students)): ?>
        <div class="card-body text-center py-5">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-3" style="width: 52px; height: 52px; font-size: 24px;">
                <i class="bi bi-person-x"></i>
            </div>
            <h4 class="h6 fw-semibold text-dark mb-1">No students enrolled</h4>
            <p class="text-muted small mb-0">Use "Add new student" or "Select existing student" above to enroll students into this class.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light border-bottom">
                    <tr>
                        <th class="fw-semibold text-muted small py-3 px-3" style="width: 150px;">Student ID</th>
                        <th class="fw-semibold text-muted small py-3 px-3">Student name</th>
                        <th class="fw-semibold text-muted small py-3 px-3">Email address</th>
                        <th class="fw-semibold text-muted small py-3 px-3" style="width: 120px;">Section</th>
                        <th class="fw-semibold text-muted small py-3 px-3 text-center" style="width: 120px;">Year level</th>
                        <th class="fw-semibold text-muted small py-3 px-3 text-center" style="width: 120px;">Status</th>
                        <th class="fw-semibold text-muted small py-3 px-3 text-end" style="width: 110px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td class="px-3 fw-semibold text-primary font-monospace small">
                            <i class="bi bi-person-vcard me-1 text-muted"></i>
                            <?= htmlspecialchars($student['student_number'] ?? '—') ?>
                        </td>
                        <td class="px-3 fw-semibold text-dark">
                            <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                        </td>
                        <td class="px-3 text-muted small">
                            <?= htmlspecialchars($student['email']) ?>
                        </td>
                        <td class="px-3 small">
                            <?php if (!empty($student['section_name'])): ?>
                                <span class="badge bg-light text-dark border font-monospace"><i class="bi bi-collection me-1"></i><?= htmlspecialchars($student['section_name']) ?></span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 text-center small text-secondary">
                            <?= htmlspecialchars($student['year_level']) ?><?= match((int)$student['year_level']) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' } ?> year
                        </td>
                        <td class="px-3 text-center">
                            <?php if (($student['status'] ?? 'Regular') === 'Irregular'): ?>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Irregular</span>
                            <?php else: ?>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Regular</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 text-end">
                            <form method="POST" action="<?= url('/faculty/students/remove') ?>" class="d-inline" onsubmit="return confirm('Remove student from this course section?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                                <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
                                <input type="hidden" name="academic_term_id" value="<?= htmlspecialchars((string)($academicTerm['id'] ?? 1)) ?>">
                                <input type="hidden" name="semester" value="<?= htmlspecialchars((string)($selectedSemester ?? '1')) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="Remove from class">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Add New Student -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 6px;">
            <div class="modal-header bg-white border-bottom py-3">
                <h5 class="modal-title h6 fw-semibold text-dark d-flex align-items-center gap-2" id="addStudentModalLabel">
                    <i class="bi bi-person-plus text-primary"></i> Add New Student to Roster
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= url('/faculty/students/add') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
                <input type="hidden" name="academic_term_id" value="<?= htmlspecialchars((string)($academicTerm['id'] ?? 1)) ?>">
                <input type="hidden" name="semester" value="<?= htmlspecialchars((string)($selectedSemester ?? '1')) ?>">
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required placeholder="e.g. Juan">
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required placeholder="e.g. Dela Cruz">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="student_number" class="form-label">Student ID number <span class="text-muted">(Optional / Late ID)</span></label>
                        <input type="text" class="form-control" id="student_number" name="student_number" placeholder="e.g. 2026-0045 (leave blank if pending/late ID)">
                        <div class="form-text">Optional. If the student does not have an ID yet, leave blank. If provided, it must be unique.</div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="student@gwc.edu">
                        <div class="form-text">A secure temporary password will be auto-generated and emailed to this address. The student will be required to create their own password upon first sign in.</div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="year_level" class="form-label">Year level <span class="text-danger">*</span></label>
                            <select class="form-select" id="year_level" name="year_level" required>
                                <option value="1">1st Year</option>
                                <option value="2">2nd Year</option>
                                <option value="3">3rd Year</option>
                                <option value="4">4th Year</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Student status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Regular">Regular</option>
                                <option value="Irregular">Irregular</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="add_section_id" class="form-label">Assigned section <span class="text-danger">*</span></label>
                        <select class="form-select" id="add_section_id" name="section_id" required>
                            <option value="">Select section...</option>
                            <?php foreach (($sections ?? []) as $sec): ?>
                                <option value="<?= $sec['id'] ?>" <?= ((int)($selectedSection ?? 0) === (int)$sec['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sec['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3 border-top d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                        <i class="bi bi-check2"></i> Add student
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Select Existing Student -->
<div class="modal fade" id="enrollExistingModal" tabindex="-1" aria-labelledby="enrollExistingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 6px;">
            <div class="modal-header bg-white border-bottom py-3">
                <h5 class="modal-title h6 fw-semibold text-dark d-flex align-items-center gap-2" id="enrollExistingModalLabel">
                    <i class="bi bi-person-check text-primary"></i> Select Existing Student
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= url('/faculty/students/enroll') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
                <input type="hidden" name="academic_term_id" value="<?= htmlspecialchars((string)($academicTerm['id'] ?? 1)) ?>">
                <input type="hidden" name="semester" value="<?= htmlspecialchars((string)($selectedSemester ?? '1')) ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Select student <span class="text-danger">*</span></label>
                        <select class="form-select" id="student_id" name="student_id" required>
                            <option value="">Choose a registered student...</option>
                            <?php foreach (($availableStudents ?? []) as $avail): ?>
                                <option value="<?= $avail['id'] ?>">
                                    <?= htmlspecialchars($avail['last_name'] . ', ' . $avail['first_name'] . ' (' . ($avail['student_number'] ?? 'No ID') . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="existing_year_level" class="form-label">Year level</label>
                            <select class="form-select" id="existing_year_level" name="year_level">
                                <option value="1">1st Year</option>
                                <option value="2">2nd Year</option>
                                <option value="3">3rd Year</option>
                                <option value="4">4th Year</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="existing_status" class="form-label">Student status</label>
                            <select class="form-select" id="existing_status" name="status">
                                <option value="Regular">Regular</option>
                                <option value="Irregular">Irregular</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="enroll_section_id" class="form-label">Assigned section <span class="text-danger">*</span></label>
                        <select class="form-select" id="enroll_section_id" name="section_id" required>
                            <option value="">Select section...</option>
                            <?php foreach (($sections ?? []) as $sec): ?>
                                <option value="<?= $sec['id'] ?>" <?= ((int)($selectedSection ?? 0) === (int)$sec['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sec['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3 border-top d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                        <i class="bi bi-check2"></i> Enroll student
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
