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

<?php
$activeFilterCount = 0;
if (!empty($selectedSet)) { $activeFilterCount++; }
if (($selectedSemester ?? '1') !== '1') { $activeFilterCount++; }
$hasActiveFilters = $activeFilterCount > 0 || !empty($currentSearch);

$activeSetName = '';
if (!empty($selectedSet)) {
    foreach ($sets ?? [] as $s) {
        if ((int)$s['id'] === (int)$selectedSet) {
            $activeSetName = $s['name'];
            break;
        }
    }
}
?>

<!-- Search, Filter & Statistics Bar -->
<div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between mb-2">
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

        <!-- Consolidated Filter Dropdown Popover -->
        <div class="dropdown filter-dropdown">
            <button type="button" 
                    class="btn filter-trigger-btn <?= $activeFilterCount > 0 ? 'has-filters' : '' ?>" 
                    id="facultyStudentsFilterBtn" 
                    data-bs-toggle="dropdown" 
                    data-bs-auto-close="outside" 
                    aria-expanded="false"
                    <?= !$hasSubjects ? 'disabled' : '' ?>>
                <i class="bi bi-filter"></i>
                <span>Filters</span>
                <span class="badge bg-primary text-white rounded-pill px-1.5 py-0.5 <?= $activeFilterCount > 0 ? '' : 'd-none' ?>" id="facultyStudentsFilterBadge" style="font-size: 10px;"><?= $activeFilterCount ?></span>
            </button>
            <div class="dropdown-menu dropdown-menu-start filter-popover-panel" aria-labelledby="facultyStudentsFilterBtn">
                <div class="filter-popover-header">
                    <h6 class="filter-popover-title">
                        <i class="bi bi-filter text-primary"></i> Filter Roster
                    </h6>
                    <button type="button" class="filter-popover-reset <?= $activeFilterCount > 0 ? '' : 'd-none' ?>" id="facultyStudentsResetBtn" onclick="resetFacultyStudentFilters()">Reset</button>
                </div>
                <div class="filter-popover-body">
                    <div class="filter-field-group">
                        <label class="filter-field-label">Semester</label>
                        <select id="semester_select" name="semester" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="1" <?= ($selectedSemester ?? '1') === '1' ? 'selected' : '' ?>>1st Semester</option>
                            <option value="2" <?= ($selectedSemester ?? '1') === '2' ? 'selected' : '' ?>>2nd Semester</option>
                        </select>
                    </div>

                    <div class="filter-field-group">
                        <label class="filter-field-label">Section / Set</label>
                        <select id="set_id_filter" name="set_id" class="form-select form-select-sm" onchange="updateFacultyStudentFilters(); window.fetchStudents();" <?= !$hasSubjects ? 'disabled' : '' ?>>
                            <option value="">All Sets</option>
                            <?php foreach (($sets ?? []) as $set): ?>
                                <option value="<?= $set['id'] ?>" <?= ((int)($selectedSet ?? 0) === (int)$set['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($set['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="filter-popover-footer">
                    <button type="button" class="btn btn-sm btn-light" onclick="bootstrap.Dropdown.getInstance(document.getElementById('facultyStudentsFilterBtn')).hide()">Close</button>
                    <button type="button" class="btn btn-sm btn-primary px-3" onclick="bootstrap.Dropdown.getInstance(document.getElementById('facultyStudentsFilterBtn')).hide()">Apply filters</button>
                </div>
            </div>
        </div>

        <?php if ($hasActiveFilters): ?>
            <a href="<?= url('/faculty/students' . ($subjectId ? '?subject_id=' . $subjectId : '')) ?>" class="btn btn-outline-secondary btn-sm" title="Clear all filters">
                <i class="bi bi-x-circle me-1"></i> Clear
            </a>
        <?php endif; ?>
    </form>

    <div class="text-muted small fw-medium text-nowrap" id="studentCounter">
        <?php $totalCount = (int)($pagination['total'] ?? count($students)); ?>
        <?= number_format($totalCount) ?> <?= $totalCount === 1 ? 'student enrolled' : 'students enrolled' ?>
    </div>
</div>

<!-- Active Filter Chips Bar -->
<div class="filter-chip-bar mb-3 <?= $activeFilterCount > 0 ? '' : 'd-none' ?>" id="facultyStudentsChipBar">
    <?php if ($activeFilterCount > 0): ?>
        <span class="small text-muted me-1">Active filters:</span>
        <?php if (($selectedSemester ?? '1') === '2'): ?>
            <span class="filter-chip">
                <span class="chip-label">Semester:</span> 2nd Semester
                <button type="button" class="chip-remove" onclick="clearFacultyStudentFilter('semester')">&times;</button>
            </span>
        <?php endif; ?>
        <?php if (!empty($activeSetName)): ?>
            <span class="filter-chip">
                <span class="chip-label">Section:</span> <?= htmlspecialchars($activeSetName) ?>
                <button type="button" class="chip-remove" onclick="clearFacultyStudentFilter('set')">&times;</button>
            </span>
        <?php endif; ?>
        <button type="button" class="filter-clear-all-chip" onclick="resetFacultyStudentFilters()">Clear filters</button>
    <?php endif; ?>
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
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStudentModalLabel">Add New Student to Roster</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= url('/faculty/students/add') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
                <input type="hidden" name="academic_term_id" value="<?= htmlspecialchars((string)($academicTerm['id'] ?? 1)) ?>">
                <input type="hidden" name="semester" value="<?= htmlspecialchars((string)($selectedSemester ?? '1')) ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="student_number" class="form-label">Student ID number <span class="text-muted">(Optional)</span></label>
                        <input type="text" class="form-control" id="student_number" name="student_number" placeholder="e.g. 2026-0045">
                        <div class="form-text">Optional because a student's ID may be issued late. Leave blank until it is released; once entered it must be unique.</div>
                    </div>
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
                <div class="modal-footer">
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
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="enrollExistingModalLabel">Select Existing Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= url('/faculty/students/enroll') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
                <input type="hidden" name="academic_term_id" value="<?= htmlspecialchars((string)($academicTerm['id'] ?? 1)) ?>">
                <input type="hidden" name="semester" value="<?= htmlspecialchars((string)($selectedSemester ?? '1')) ?>">
                <div class="modal-body">
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
                <div class="modal-footer">
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

function updateFacultyStudentFilters() {
    const setSelect = document.getElementById('set_id_filter');
    const semSelect = document.getElementById('semester_select');
    const badge = document.getElementById('facultyStudentsFilterBadge');
    const triggerBtn = document.getElementById('facultyStudentsFilterBtn');
    const resetBtn = document.getElementById('facultyStudentsResetBtn');
    const chipBar = document.getElementById('facultyStudentsChipBar');

    const setVal = setSelect ? setSelect.value : '';
    const semVal = semSelect ? semSelect.value : '1';

    let count = 0;
    if (setVal) count++;
    if (semVal !== '1') count++;

    if (badge && triggerBtn) {
        if (count > 0) {
            badge.textContent = count;
            badge.classList.remove('d-none');
            triggerBtn.classList.add('has-filters');
            if (resetBtn) resetBtn.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
            triggerBtn.classList.remove('has-filters');
            if (resetBtn) resetBtn.classList.add('d-none');
        }
    }

    if (chipBar) {
        if (count > 0) {
            let chipsHtml = '<span class="small text-muted me-1">Active filters:</span>';
            if (semVal !== '1') {
                chipsHtml += '<span class="filter-chip"><span class="chip-label">Semester:</span> 2nd Semester <button type="button" class="chip-remove" onclick="clearFacultyStudentFilter(\'semester\')">&times;</button></span>';
            }
            if (setVal) {
                const setText = setSelect.options[setSelect.selectedIndex]?.text || setVal;
                chipsHtml += '<span class="filter-chip"><span class="chip-label">Section:</span> ' + setText + ' <button type="button" class="chip-remove" onclick="clearFacultyStudentFilter(\'set\')">&times;</button></span>';
            }
            chipsHtml += '<button type="button" class="filter-clear-all-chip" onclick="resetFacultyStudentFilters()">Clear filters</button>';
            chipBar.innerHTML = chipsHtml;
            chipBar.classList.remove('d-none');
        } else {
            chipBar.innerHTML = '';
            chipBar.classList.add('d-none');
        }
    }
}

function clearFacultyStudentFilter(type) {
    if (type === 'semester') {
        const semSelect = document.getElementById('semester_select');
        if (semSelect) {
            semSelect.value = '1';
            document.getElementById('studentsFilterForm').submit();
            return;
        }
    }
    if (type === 'set') {
        const setSelect = document.getElementById('set_id_filter');
        if (setSelect) {
            setSelect.value = '';
            updateFacultyStudentFilters();
            window.fetchStudents();
        }
    }
}

function resetFacultyStudentFilters() {
    const semSelect = document.getElementById('semester_select');
    const setSelect = document.getElementById('set_id_filter');
    const needSubmit = semSelect && semSelect.value !== '1';
    if (semSelect) semSelect.value = '1';
    if (setSelect) setSelect.value = '';
    if (needSubmit) {
        document.getElementById('studentsFilterForm').submit();
    } else {
        updateFacultyStudentFilters();
        window.fetchStudents();
    }
}
</script>

<?php include __DIR__ . '/../../components/student-pass-modal.php'; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
