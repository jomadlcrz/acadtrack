<?php
$hasSubjects = !empty($assignedSubjects);
$pageTitle = 'Enrolled Students';
$subtitle = 'Class enrollment roster and student classification for the active course offering.';
$headerActions = '
    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addStudentModal"' . (!$hasSubjects ? ' disabled title="Assign a subject first before adding students"' : '') . '>
        <i class="bi bi-person-plus"></i> Add new student
    </button>
    <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#enrollExistingModal"' . (!$hasSubjects ? ' disabled title="Assign a subject first before enrolling students"' : '') . '>
        <i class="bi bi-person-check"></i> Select existing student
    </button>';
ob_start();
?>

<!-- Search, Filter & Statistics Bar -->
<div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between mb-4">
    <form method="GET" action="<?= url('/faculty/students') ?>" class="d-flex flex-wrap align-items-center gap-2 flex-grow-1" id="studentsFilterForm">
        <div class="position-relative flex-grow-1" style="min-width: 220px; max-width: 320px;">
            <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 14px;"></i>
            <input type="text" 
                   id="studentSearch" 
                   name="search"
                   class="form-control ps-5" 
                   placeholder="Search by name, ID, or email..." 
                   value="<?= htmlspecialchars($currentSearch ?? '') ?>"
                   autocomplete="off"
                   <?= !$hasSubjects ? 'disabled' : '' ?>>
        </div>

        <select id="semester_select" name="semester" class="form-select w-auto" onchange="this.form.submit()">
            <option value="1" <?= ($selectedSemester ?? '1') === '1' ? 'selected' : '' ?>>1st Semester</option>
            <option value="2" <?= ($selectedSemester ?? '1') === '2' ? 'selected' : '' ?>>2nd Semester</option>
        </select>

        <select id="subject_id_select" name="subject_id" class="form-select w-auto" style="min-width: 200px; max-width: 280px;" onchange="this.form.submit()" <?= !$hasSubjects ? 'disabled' : '' ?>>
            <?php if (!$hasSubjects): ?>
                <option value="">No subjects assigned</option>
            <?php else: ?>
                <?php foreach ($assignedSubjects as $sub): ?>
                    <option value="<?= $sub['id'] ?>" <?= ((int)$sub['id'] === (int)($subjectId ?? 0)) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sub['code'] . ' - ' . $sub['name']) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>

        <select id="set_id_filter" name="set_id" class="form-select w-auto" onchange="window.fetchStudents()" <?= !$hasSubjects ? 'disabled' : '' ?>>
            <option value="">All Sets</option>
            <?php foreach (($sets ?? []) as $set): ?>
                <option value="<?= $set['id'] ?>" <?= ((int)($selectedSet ?? 0) === (int)$set['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($set['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if (!empty($currentSubject)): ?>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5 fs-7">
                <?= htmlspecialchars($currentSubject['nature'] ?? 'Lecture') ?>
            </span>
        <?php endif; ?>
    </form>

    <div class="text-muted small fw-medium text-nowrap" id="studentCounter">
        <?php $totalCount = (int)($pagination['total'] ?? count($students)); ?>
        <?= number_format($totalCount) ?> <?= $totalCount === 1 ? 'student enrolled' : 'students enrolled' ?>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="studentsTable">
            <thead class="bg-white border-bottom">
                <tr>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="width: 150px; font-size: 11px;">Student ID</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Student name</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Email address</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="width: 120px; font-size: 11px;">Set</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 120px; font-size: 11px;">Year level</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 120px; font-size: 11px;">Status</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="width: 110px; font-size: 11px;">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (!$hasSubjects): ?>
                    <tr id="emptyStudentRow">
                        <td colspan="7" class="p-0">
                            <?php
                            $icon = 'bi-journal-x';
                            $iconColor = 'amber';
                            $title = 'No subjects assigned for this semester';
                            $message = 'You do not have any teaching subjects assigned for ' . (($selectedSemester ?? '1') === '2' ? '2nd Semester' : '1st Semester') . '. Switch semesters or contact your Dean or Administrator.';
                            include __DIR__ . '/../../components/empty-state.php';
                            ?>
                        </td>
                    </tr>
                <?php elseif (empty($students)): ?>
                    <tr id="emptyStudentRow">
                        <td colspan="7" class="p-0">
                            <?php
                            $icon = 'bi-people';
                            $iconColor = 'blue';
                            $title = 'No students enrolled';
                            $message = 'Use "Add new student" or "Select existing student" above to enroll students into this class.';
                            include __DIR__ . '/../../components/empty-state.php';
                            ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <tr id="emptySearchRow" style="display: none;">
                        <td colspan="7" class="p-0">
                            <?php
                            $icon = 'bi-search';
                            $title = 'No matching students found';
                            $message = 'No enrolled students match your search query.';
                            include __DIR__ . '/../../components/empty-state.php';
                            ?>
                        </td>
                    </tr>
                    <?php foreach ($students as $student): ?>
                    <tr class="student-row" data-search="<?= strtolower(htmlspecialchars(($student['student_number'] ?? '') . ' ' . $student['first_name'] . ' ' . $student['last_name'] . ' ' . $student['email'] . ' ' . ($student['set_name'] ?? ''))) ?>">
                        <td class="px-3 fw-semibold font-monospace small text-dark">
                            <?= !empty($student['student_number']) ? htmlspecialchars($student['student_number']) : 'No ID' ?>
                        </td>
                        <td class="px-3 fw-semibold text-dark">
                            <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                        </td>
                        <td class="px-3 text-muted small">
                            <?= htmlspecialchars($student['email']) ?>
                        </td>
                        <td class="px-3 small">
                            <?php $setName = $student['set_name'] ?? ''; ?>
                            <?php if (!empty($setName)): ?>
                                <span class="badge bg-light text-dark border font-monospace"><i class="bi bi-collection me-1"></i><?= htmlspecialchars($setName) ?></span>
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
                        <td class="px-3 text-end text-nowrap">
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-sm btn-action-trigger" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false" title="Actions">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end action-dropdown-menu shadow-sm">
                                    <li>
                                        <button type="button" 
                                                class="dropdown-item"
                                                onclick="viewStudentPass(<?= $student['id'] ?>, <?= $subjectId ?>, <?= (int)($academicTerm['id'] ?? 1) ?>)">
                                            <i class="bi bi-person-badge text-primary"></i> Digital Pass
                                        </button>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="<?= url('/faculty/students/remove') ?>" class="m-0" onsubmit="return confirm('Remove student from this course set?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                                            <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
                                            <input type="hidden" name="academic_term_id" value="<?= htmlspecialchars((string)($academicTerm['id'] ?? 1)) ?>">
                                            <input type="hidden" name="semester" value="<?= htmlspecialchars((string)($selectedSemester ?? '1')) ?>">
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-person-dash"></i> Unenroll student
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="studentsPagination" class="mt-2">
    <?php if (!empty($students)): ?>
        <?php include __DIR__ . '/../../components/pagination.php'; ?>
    <?php endif; ?>
</div>

<!-- Modal: Add New Student -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 6px;">
            <div class="modal-header bg-white border-bottom py-3">
                <h5 class="modal-title h6 fw-semibold text-dark" id="addStudentModalLabel">Add New Student to Roster</h5>
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
                        <label for="add_set_id" class="form-label">Assigned set <span class="text-danger">*</span></label>
                        <select class="form-select" id="add_set_id" name="set_id" required>
                            <option value="">Select set...</option>
                            <?php foreach (($sets ?? []) as $set): ?>
                                <option value="<?= $set['id'] ?>" <?= ((int)($selectedSet ?? 0) === (int)$set['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($set['name']) ?>
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
                <h5 class="modal-title h6 fw-semibold text-dark" id="enrollExistingModalLabel">Select Existing Student</h5>
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
                                    <?= htmlspecialchars($avail['last_name'] . ', ' . $avail['first_name'] . ' (' . (!empty($avail['student_number']) ? $avail['student_number'] : 'No ID') . ')') ?>
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
                        <label for="enroll_set_id" class="form-label">Assigned set <span class="text-danger">*</span></label>
                        <select class="form-select" id="enroll_set_id" name="set_id" required>
                            <option value="">Select set...</option>
                            <?php foreach (($sets ?? []) as $set): ?>
                                <option value="<?= $set['id'] ?>" <?= ((int)($selectedSet ?? 0) === (int)$set['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($set['name']) ?>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('studentSearch');
    const filterForm = document.getElementById('studentsFilterForm');

    let debounceTimer = null;
    let abortCtrl = null;

    function applyClientFilter() {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        const rows = document.querySelectorAll('.student-row');
        const emptySearchRow = document.getElementById('emptySearchRow');
        let visibleCount = 0;

        rows.forEach(function(row) {
            const searchData = row.getAttribute('data-search') || '';
            if (!query || searchData.includes(query)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (emptySearchRow && rows.length > 0) {
            emptySearchRow.style.display = (visibleCount === 0 && query) ? '' : 'none';
        }
    }

    window.fetchStudents = function(pageUrl) {
        if (abortCtrl) {
            abortCtrl.abort();
        }
        abortCtrl = new AbortController();

        let targetUrl;
        if (pageUrl) {
            targetUrl = pageUrl;
        } else {
            const formData = new FormData(filterForm);
            const params = new URLSearchParams();
            for (const [k, v] of formData.entries()) {
                const val = typeof v === 'string' ? v.trim() : v;
                if (val) params.set(k, val);
            }
            targetUrl = filterForm.action + (params.toString() ? '?' + params.toString() : '');
        }

        fetch(targetUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: abortCtrl.signal
        })
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newTable = doc.getElementById('studentsTable');
            const currentTable = document.getElementById('studentsTable');
            if (newTable && currentTable) {
                currentTable.innerHTML = newTable.innerHTML;
            }

            const newCounter = doc.getElementById('studentCounter');
            const currentCounter = document.getElementById('studentCounter');
            if (newCounter && currentCounter) {
                currentCounter.innerHTML = newCounter.innerHTML;
            }

            const newPagination = doc.getElementById('studentsPagination');
            const currentPagination = document.getElementById('studentsPagination');
            if (currentPagination) {
                currentPagination.innerHTML = newPagination ? newPagination.innerHTML : '';
            }

            window.history.replaceState(null, '', targetUrl);
        })
        .catch(err => {
            if (err.name !== 'AbortError') {
                console.error('Fetch error:', err);
            }
        });
    };

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            applyClientFilter();
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() {
                window.fetchStudents();
            }, 300);
        });
    }

    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearTimeout(debounceTimer);
            window.fetchStudents();
        });
    }

    // Intercept pagination clicks for seamless page changes
    document.addEventListener('click', function(e) {
        const link = e.target.closest('#studentsPagination a.page-link');
        if (link && link.getAttribute('href') && !link.getAttribute('href').startsWith('#')) {
            e.preventDefault();
            window.fetchStudents(link.getAttribute('href'));
        }
    });
});

function viewStudentPass(studentId, subjectId, termId) {
    fetch('<?= url('/faculty/students/pass-data') ?>?student_id=' + studentId + '&subject_id=' + subjectId + '&term_id=' + termId)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            openStudentPassModal(data);
        })
        .catch(err => {
            console.error('Failed to load student pass', err);
            alert('Failed to load student pass details.');
        });
}
</script>

<?php include __DIR__ . '/../../components/student-pass-modal.php'; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
