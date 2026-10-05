<?php
$pageTitle = 'Faculty Subject Assignments';
$termDisplay = $termLabel ?? (($academicTerm['academic_year_name'] ?? '2026-2027') . ' · ' . (($selectedSemester ?? '1') === '2' ? '2nd Semester' : '1st Semester'));
$subtitle = 'Designate accredited faculty instructors to course offerings for ' . htmlspecialchars($termDisplay) . '.';

// Calculate Executive Metrics
$totalSubjects = count($subjects);
$assignedSubjectsCount = 0;
$unassignedSubjectsCount = 0;
$totalUnits = 0.0;

foreach ($subjects as $s) {
    $units = (float) ($s['units'] ?? 3.0);
    $totalUnits += $units;
    if (!empty($s['assigned_faculty'])) {
        $assignedSubjectsCount++;
    } else {
        $unassignedSubjectsCount++;
    }
}

$activeFacultyCount = count(array_filter($faculty, fn($f) => ($f['assigned_count'] ?? 0) > 0));
$totalFacultyPool = count($faculty);

// Organize subjects into Year Level groups (1st Year through 4th Year)
$yearLabels = [
    1 => '1st Year',
    2 => '2nd Year',
    3 => '3rd Year',
    4 => '4th Year',
];

$groups = [];
for ($y = 1; $y <= 4; $y++) {
    $groups[$y] = [
        'year_level' => $y,
        'title' => $yearLabels[$y],
        'subjects' => [],
        'assigned_count' => 0,
        'total_units' => 0.0,
    ];
}

$otherSubjects = [];
foreach ($subjects as $subject) {
    $y = (int) ($subject['year_level'] ?? 1);
    $units = (float) ($subject['units'] ?? 3.0);
    $isAssigned = !empty($subject['assigned_faculty']);

    if (isset($groups[$y])) {
        $groups[$y]['subjects'][] = $subject;
        $groups[$y]['total_units'] += $units;
        if ($isAssigned) {
            $groups[$y]['assigned_count']++;
        }
    } else {
        $otherSubjects[] = $subject;
    }
}

// Header Actions (Semester selection tabs)
$headerActions = '
<div class="d-flex align-items-center gap-2">
    <div class="btn-group btn-group-sm" role="group" aria-label="Semester selection">
        <a href="' . url('/dean/faculty-assignments?semester=1') . '" class="btn ' . (($selectedSemester ?? '1') === '1' ? 'btn-primary' : 'btn-outline-secondary') . '">
            1st Semester
        </a>
        <a href="' . url('/dean/faculty-assignments?semester=2') . '" class="btn ' . (($selectedSemester ?? '1') === '2' ? 'btn-primary' : 'btn-outline-secondary') . '">
            2nd Semester
        </a>
    </div>
</div>';

ob_start();
?>

<!-- Metric KPI Cards (Matching Dashboard Standard) -->
<div class="dashboard-stats mb-4">
    <div class="stat-card">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Total courses</span>
                <div class="stat-icon stat-icon-blue"><i class="bi bi-journal-bookmark-fill"></i></div>
            </div>
            <div class="stat-value tabular-nums" id="statTotalCourses"><?= $totalSubjects ?></div>
        </div>
        <span class="stat-subtext">Semester offerings</span>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Assigned courses</span>
                <div class="stat-icon stat-icon-green"><i class="bi bi-check-circle-fill"></i></div>
            </div>
            <div class="stat-value tabular-nums text-success" id="statAssignedCourses"><?= $assignedSubjectsCount ?></div>
        </div>
        <span class="stat-subtext" id="statAssignedCoverage">
            <?= $totalSubjects > 0 ? round(($assignedSubjectsCount / $totalSubjects) * 100) : 0 ?>% institutional coverage
        </span>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Unassigned</span>
                <div class="stat-icon stat-icon-amber"><i class="bi bi-exclamation-triangle-fill"></i></div>
            </div>
            <div class="stat-value tabular-nums <?= $unassignedSubjectsCount > 0 ? 'text-amber' : '' ?>" id="statUnassignedCourses">
                <?= $unassignedSubjectsCount ?>
            </div>
        </div>
        <span class="stat-subtext">Pending instructor</span>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Active instructors</span>
                <div class="stat-icon stat-icon-purple"><i class="bi bi-people-fill"></i></div>
            </div>
            <div class="stat-value tabular-nums">
                <?= $activeFacultyCount ?> <span class="text-muted fs-6 fw-normal">/ <?= $totalFacultyPool ?></span>
            </div>
        </div>
        <span class="stat-subtext">Teaching faculty pool</span>
    </div>
</div>

<!-- Search & Filtering Controls Bar -->
<div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between mb-2">
    <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1" style="max-width: 760px;">
        <div class="position-relative flex-grow-1" style="min-width: 250px;">
            <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 14px;"></i>
            <input type="text" 
                   id="assignmentSearch" 
                   class="form-control ps-5" 
                   placeholder="Search course code, descriptive title, or assigned instructor..." 
                   autocomplete="off"
                   onkeyup="applyFilters()">
        </div>

        <!-- Consolidated Filter Dropdown Popover -->
        <div class="dropdown filter-dropdown">
            <button type="button" 
                    class="btn filter-trigger-btn" 
                    id="assignmentsFilterBtn" 
                    data-bs-toggle="dropdown" 
                    data-bs-auto-close="outside" 
                    aria-expanded="false">
                <i class="bi bi-filter"></i>
                <span>Filters</span>
                <span class="badge bg-primary text-white rounded-pill px-1.5 py-0.5 d-none" id="assignmentsFilterBadge" style="font-size: 10px;">0</span>
            </button>
            <div class="dropdown-menu dropdown-menu-start filter-popover-panel" aria-labelledby="assignmentsFilterBtn">
                <div class="filter-popover-header">
                    <h6 class="filter-popover-title">
                        <i class="bi bi-filter text-primary"></i> Filter Courses
                    </h6>
                    <button type="button" class="filter-popover-reset d-none" id="assignmentsFilterResetBtn" onclick="resetDropdownFilters()">Reset</button>
                </div>
                <div class="filter-popover-body">
                    <div class="filter-field-group">
                        <label class="filter-field-label">Year level</label>
                        <select class="form-select form-select-sm" id="yearLevelFilter" onchange="applyFilters()">
                            <option value="ALL">All Year Levels</option>
                            <option value="1">1st Year Only</option>
                            <option value="2">2nd Year Only</option>
                            <option value="3">3rd Year Only</option>
                            <option value="4">4th Year Only</option>
                        </select>
                    </div>

                    <div class="filter-field-group">
                        <label class="filter-field-label">Assignment status</label>
                        <select class="form-select form-select-sm" id="statusFilter" onchange="applyFilters()">
                            <option value="ALL">All Statuses</option>
                            <option value="ASSIGNED">Assigned Only</option>
                            <option value="UNASSIGNED">Unassigned Only</option>
                        </select>
                    </div>
                </div>
                <div class="filter-popover-footer">
                    <button type="button" class="btn btn-sm btn-light" onclick="bootstrap.Dropdown.getInstance(document.getElementById('assignmentsFilterBtn')).hide()">Close</button>
                    <button type="button" class="btn btn-sm btn-primary px-3" onclick="bootstrap.Dropdown.getInstance(document.getElementById('assignmentsFilterBtn')).hide()">Apply filters</button>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#facultyWorkloadModal">
            <i class="bi bi-person-lines-fill"></i> Faculty Workload
        </button>
        <div class="text-muted small fw-medium text-nowrap ms-1" id="visibleCounter">
            <?= $totalSubjects ?> <?= $totalSubjects === 1 ? 'course' : 'courses' ?>
        </div>
    </div>
</div>

<!-- Active Filter Chips Bar -->
<div class="filter-chip-bar mb-3 d-none" id="assignmentsChipBar"></div>

<!-- Year Level Group Containers -->
<div class="space-y-4" id="groupsContainer">
    <?php foreach ($groups as $y => $group): ?>
        <?php $subList = $group['subjects']; ?>
        <div class="card shadow-sm border-0 mb-4 subject-group-container" 
             id="group-<?= $y ?>"
             data-year="<?= $y ?>"
             style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
            
            <!-- Group Container Header -->
            <div class="card-header bg-light py-2.5 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <h3 class="h6 fw-bold text-dark mb-0">
                        <?= htmlspecialchars($group['title']) ?>
                    </h3>
                </div>
                <div class="small text-muted d-flex align-items-center gap-2">
                    <span class="badge bg-white text-dark border px-2 py-0.5">
                        <span class="group-count" id="count-<?= $y ?>"><?= count($subList) ?></span> <?= count($subList) === 1 ? 'course' : 'courses' ?>
                    </span>
                    <span class="text-secondary">&middot;</span>
                    <span class="badge <?= $group['assigned_count'] === count($subList) && count($subList) > 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' ?> px-2 py-0.5">
                        <span class="group-assigned-count" id="assigned-count-<?= $y ?>"><?= $group['assigned_count'] ?></span> / <?= count($subList) ?> assigned
                    </span>
                    <span class="text-secondary">&middot;</span>
                    <span class="fw-semibold text-dark tabular-nums"><?= number_format($group['total_units'], 1) ?> units</span>
                </div>
            </div>

            <!-- Group Subjects Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-white border-bottom">
                        <tr>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="width: 130px; font-size: 11px;">Subject Code</th>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Descriptive Title &amp; Details</th>
                            <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Assigned Instructor(s)</th>
                            <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-end" style="width: 170px; font-size: 11px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php if (empty($subList)): ?>
                            <tr class="group-empty-row">
                                <td colspan="4" class="p-0">
                                    <?php
                                    $icon = 'bi-journal-x';
                                    $title = 'No course offerings';
                                    $message = 'No course offerings registered for this year level.';
                                    include __DIR__ . '/../../components/empty-state.php';
                                    ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($subList as $subject): ?>
                                <?php 
                                    $units = (float) ($subject['units'] ?? 3.0);
                                    $sType = (string) ($subject['subject_type'] ?? ($subject['nature'] ?? 'Lecture'));
                                    $assignedList = $subject['assigned_faculty'] ?? [];
                                    $isAssigned = !empty($assignedList);
                                    
                                    // Compile instructor search string
                                    $facultySearchTerms = [];
                                    foreach ($assignedList as $inst) {
                                        $facultySearchTerms[] = ($inst['first_name'] ?? '') . ' ' . ($inst['last_name'] ?? '') . ' ' . ($inst['email'] ?? '');
                                    }
                                    $facultySearchString = strtolower(implode(' ', $facultySearchTerms));
                                ?>
                                <tr class="assignment-row"
                                    data-code="<?= strtolower(htmlspecialchars($subject['subject_code'] ?? $subject['code'] ?? '')) ?>"
                                    data-title="<?= strtolower(htmlspecialchars($subject['descriptive_title'] ?? $subject['name'] ?? '')) ?>"
                                    data-faculty="<?= htmlspecialchars($facultySearchString) ?>"
                                    data-status="<?= $isAssigned ? 'ASSIGNED' : 'UNASSIGNED' ?>">
                                    
                                    <td class="py-3 px-4 fw-bold text-dark font-monospace">
                                        <?= htmlspecialchars($subject['subject_code'] ?? $subject['code']) ?>
                                    </td>

                                    <td class="py-3 px-4">
                                        <div class="fw-semibold text-dark mb-0.5">
                                            <?= htmlspecialchars($subject['descriptive_title'] ?? $subject['name']) ?>
                                        </div>
                                        <div class="small text-muted d-flex align-items-center gap-2">
                                            <span><?= number_format($units, 1) ?> Units</span>
                                            <span>&bull;</span>
                                            <span><?= htmlspecialchars($sType) ?></span>
                                        </div>
                                    </td>

                                    <td class="py-3 px-3">
                                        <?php if ($isAssigned): ?>
                                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                                <?php foreach ($assignedList as $inst): ?>
                                                    <?php 
                                                        $firstInitial = strtoupper(substr($inst['first_name'] ?? 'F', 0, 1));
                                                        $lastInitial = strtoupper(substr($inst['last_name'] ?? 'M', 0, 1));
                                                        $initials = $firstInitial . $lastInitial;
                                                        $dept = $inst['dept_abbrev'] ?? null;
                                                    ?>
                                                    <div class="d-inline-flex align-items-center gap-2 py-1 px-2.5 rounded border bg-white shadow-sm" style="border-color: #e2e8f0 !important;">
                                                        <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-inline-flex align-items-center justify-content-center" style="width: 26px; height: 26px; font-size: 10.5px; flex-shrink: 0;">
                                                            <?= $initials ?>
                                                        </div>
                                                        <div class="d-flex flex-column" style="line-height: 1.2;">
                                                            <span class="fw-semibold text-dark small" style="font-size: 12.5px;">
                                                                <?= htmlspecialchars(($inst['first_name'] ?? '') . ' ' . ($inst['last_name'] ?? '')) ?>
                                                            </span>
                                                            <span class="text-muted" style="font-size: 11px;">
                                                                <?= htmlspecialchars($inst['email'] ?? '') ?><?= $dept ? ' &bull; ' . htmlspecialchars($dept) : '' ?>
                                                            </span>
                                                        </div>
                                                        <form method="POST" action="<?= url('/dean/faculty-assignments/remove') ?>" class="ms-1 d-inline" onsubmit="return confirm('Remove <?= htmlspecialchars(addslashes(($inst['first_name'] ?? '') . ' ' . ($inst['last_name'] ?? ''))) ?> from <?= htmlspecialchars(addslashes($subject['subject_code'] ?? $subject['code'])) ?>?');">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="faculty_id" value="<?= (int)$inst['faculty_id'] ?>">
                                                            <input type="hidden" name="subject_id" value="<?= (int)$subject['id'] ?>">
                                                            <input type="hidden" name="academic_term_id" value="<?= htmlspecialchars((string)($academicTerm['id'] ?? 1)) ?>">
                                                            <input type="hidden" name="semester" value="<?= htmlspecialchars((string)($selectedSemester ?? '1')) ?>">
                                                            <button type="submit" class="btn btn-sm btn-link text-danger p-0 border-0" title="Remove instructor assignment" style="line-height: 1;">
                                                                <i class="bi bi-x-circle fs-6"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                                <i class="bi bi-dash-circle me-1"></i> Unassigned
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="py-3 px-3 text-end text-nowrap">
                                        <?php if ($isAssigned): ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-secondary py-1 px-2.5 d-inline-flex align-items-center gap-1"
                                                    onclick="openAssignModal(<?= (int)$subject['id'] ?>)">
                                                <i class="bi bi-person-plus"></i> Add Instructor
                                            </button>
                                        <?php else: ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-primary py-1 px-2.5 d-inline-flex align-items-center gap-1 shadow-sm"
                                                    onclick="openAssignModal(<?= (int)$subject['id'] ?>)">
                                                <i class="bi bi-person-plus-fill"></i> Assign
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Empty Search Fallback State -->
<div id="noResultsState" class="d-none mb-4">
    <?php
    $icon = 'bi-search';
    $title = 'No course allocations match your filter';
    $message = 'Try adjusting your keywords or clearing the year level / status filter.';
    $actionHtml = '<button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">Clear Filters</button>';
    $card = true;
    include __DIR__ . '/../../components/empty-state.php';
    ?>
</div>

<!-- Modal: Assign Instructor to Course -->
<div class="modal fade" id="assignInstructorModal" tabindex="-1" aria-labelledby="assignInstructorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= url('/dean/faculty-assignments/assign') ?>" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="academic_term_id" value="<?= htmlspecialchars((string)($academicTerm['id'] ?? 1)) ?>">
                <input type="hidden" name="semester" value="<?= htmlspecialchars((string)($selectedSemester ?? '1')) ?>">

                <div class="modal-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-plus-fill text-primary fs-4"></i>
                        <div>
                            <h5 class="modal-title" id="assignInstructorModalLabel">Assign Instructor to Course</h5>
                            <small class="text-muted" style="font-size: 12px;"><?= htmlspecialchars($academicTerm['name'] ?? 'Active Term') ?> &bull; <?= htmlspecialchars(semester_label($selectedSemester ?? 1)) ?></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modal_subject_id" class="form-label small fw-semibold">Subject Offering <span class="text-danger">*</span></label>
                        <select class="form-select" id="modal_subject_id" name="subject_id" required data-combobox data-placeholder="Search course offerings..." data-empty-text="No course offerings found">
                            <option value="">Select a course offering...</option>
                            <?php foreach ($subjects as $s): ?>
                                <?php $assignedCount = count($s['assigned_faculty'] ?? []); ?>
                                <option value="<?= $s['id'] ?>">
                                    <?= htmlspecialchars($s['subject_code'] ?? $s['code']) ?> &mdash; <?= htmlspecialchars($s['descriptive_title'] ?? $s['name']) ?> <?= $assignedCount > 0 ? "({$assignedCount} currently assigned)" : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text small">Course offerings available for this term.</div>
                        <div id="modalAssignedWrap" class="mt-2 d-none">
                            <div class="small text-muted mb-1">Currently assigned instructors</div>
                            <div id="modalAssignedList" class="d-flex flex-wrap gap-1"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="modal_faculty_id" class="form-label small fw-semibold">Instructors <span class="text-danger">*</span></label>
                        <select class="form-select" id="modal_faculty_id" name="faculty_ids[]" multiple required data-combobox data-placeholder="Search and select instructors..." data-empty-text="No instructors found">
                            <?php foreach ($faculty as $f): ?>
                                <?php
                                    $deptName = $f['faculty']['department']['dept_abbrev'] ?? ($f['faculty']['department']['name'] ?? null);
                                    $load = (int)($f['assigned_count'] ?? 0);
                                    $fullName = $f['first_name'] . ' ' . $f['last_name'];
                                ?>
                                <option value="<?= $f['id'] ?>" data-chip="<?= htmlspecialchars($fullName) ?>" data-label="<?= htmlspecialchars($fullName . ' (' . $f['email'] . ')' . ($deptName ? " — [{$deptName}]" : '') . ' • ' . $load . ($load === 1 ? ' course' : ' courses')) ?>">
                                    <?= htmlspecialchars($fullName) ?> (<?= htmlspecialchars($f['email']) ?>) <?= $deptName ? "— [" . htmlspecialchars($deptName) . "]" : '' ?> &bull; <?= $load ?> <?= $load === 1 ? 'course' : 'courses' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text small">A course offering can have several instructors (for example, one per section). Select all that apply; instructor workload is shown to prevent overload.</div>
                    </div>

                    <div class="p-3 bg-light rounded border small text-muted">
                        <i class="bi bi-shield-check text-primary me-1"></i>
                        Instructors granted assignment can encode grades for enrolled students in this academic period.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1.5 shadow-sm">
                        <i class="bi bi-check-circle"></i> Confirm Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Faculty Workload Overview -->
<div class="modal fade" id="facultyWorkloadModal" tabindex="-1" aria-labelledby="facultyWorkloadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-info-subtle text-info p-2 rounded">
                        <i class="bi bi-person-lines-fill fs-6"></i>
                    </div>
                    <div>
                        <h5 class="modal-title" id="facultyWorkloadModalLabel">Faculty Teaching Workload Summary</h5>
                        <small class="text-muted" style="font-size: 12px;"><?= htmlspecialchars($termDisplay) ?></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <?php if (empty($faculty)): ?>
                    <div class="text-center py-4 text-muted small">
                        No faculty members found in the institutional roster.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light border-bottom">
                                <tr>
                                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Faculty Member</th>
                                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 11px;">Department</th>
                                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 120px; font-size: 11px;">Course Load</th>
                                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Assigned Offerings</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <?php foreach ($faculty as $f): ?>
                                    <?php 
                                        $fId = (int)$f['id'];
                                        $fLoad = (int)($f['assigned_count'] ?? 0);
                                        $deptName = $f['faculty']['department']['dept_name'] ?? ($f['faculty']['department']['name'] ?? ($f['faculty']['department']['dept_abbrev'] ?? '—'));
                                        
                                        // Collect assigned courses for this faculty in this term
                                        $assignedCourses = [];
                                        foreach ($subjects as $s) {
                                            foreach ($s['assigned_faculty'] ?? [] as $af) {
                                                if ((int)($af['faculty_id'] ?? 0) === $fId) {
                                                    $assignedCourses[] = $s['subject_code'] ?? $s['code'];
                                                }
                                            }
                                        }
                                    ?>
                                    <tr>
                                        <td class="py-3 px-3">
                                            <div class="fw-semibold text-dark small">
                                                <?= htmlspecialchars($f['first_name'] . ' ' . $f['last_name']) ?>
                                            </div>
                                            <div class="text-muted" style="font-size: 11.5px;">
                                                <?= htmlspecialchars($f['email']) ?>
                                            </div>
                                        </td>
                                        <td class="py-3 px-3 small text-muted">
                                            <?= htmlspecialchars($deptName) ?>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <span class="badge <?= $fLoad > 0 ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-light text-secondary border' ?> px-2.5 py-1 fw-bold">
                                                <?= $fLoad ?> <?= $fLoad === 1 ? 'course' : 'courses' ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-3">
                                            <?php if (!empty($assignedCourses)): ?>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <?php foreach ($assignedCourses as $cCode): ?>
                                                        <span class="badge bg-light text-dark border font-monospace px-2 py-0.5" style="font-size: 11px;">
                                                            <?= htmlspecialchars($cCode) ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted small" style="font-size: 11.5px;">None assigned yet</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var assignedBySubject = <?= json_encode(array_reduce($subjects, function ($carry, $s) {
        $carry[(string) $s['id']] = array_map(function ($a) {
            return ['id' => (int) $a['faculty_id'], 'name' => trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? ''))];
        }, $s['assigned_faculty'] ?? []);
        return $carry;
    }, []), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;

    var subjectEl = document.getElementById('modal_subject_id');
    var facultyEl = document.getElementById('modal_faculty_id');
    var wrap = document.getElementById('modalAssignedWrap');
    var listEl = document.getElementById('modalAssignedList');
    if (!subjectEl || !facultyEl) return;

    function syncAssigned() {
        var assigned = assignedBySubject[subjectEl.value] || [];
        var ids = assigned.map(function (a) { return String(a.id); });

        Array.prototype.forEach.call(facultyEl.options, function (o) {
            if (o.value === '') return;
            var base = o.getAttribute('data-label') || o.textContent;
            var taken = ids.indexOf(o.value) !== -1;
            o.disabled = taken;
            if (taken) o.selected = false;
            o.textContent = taken ? base + ' \u2014 already assigned' : base;
        });
        facultyEl.comboboxRefresh && facultyEl.comboboxRefresh();

        listEl.innerHTML = '';
        assigned.forEach(function (a) {
            var b = document.createElement('span');
            b.className = 'badge bg-light text-dark border fw-normal';
            b.textContent = a.name;
            listEl.appendChild(b);
        });
        wrap.classList.toggle('d-none', assigned.length === 0);
    }

    subjectEl.addEventListener('change', function () {
        syncAssigned();
        // Continue straight to the instructors field so focus is never lost
        if (subjectEl.value && facultyEl.comboboxFocus) facultyEl.comboboxFocus();
    });
    syncAssigned();
})();
</script>

<script>
function openAssignModal(subjectId) {
    var modalEl = document.getElementById('assignInstructorModal');
    if (!modalEl) return;
    
    var selectEl = document.getElementById('modal_subject_id');
    if (selectEl && subjectId) {
        selectEl.value = subjectId;
        selectEl.dispatchEvent(new Event('change', { bubbles: true }));
    }
    
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
}

function applyFilters() {
    var searchInput = document.getElementById('assignmentSearch');
    var query = (searchInput ? searchInput.value : '').toLowerCase().trim();
    var yearFilter = document.getElementById('yearLevelFilter').value;
    var statusFilter = document.getElementById('statusFilter').value;
    
    var groupContainers = document.querySelectorAll('.subject-group-container');
    var totalVisible = 0;

    groupContainers.forEach(function(container) {
        var groupYear = container.getAttribute('data-year');
        var yearMatches = (yearFilter === 'ALL' || yearFilter === groupYear);
        
        var rows = container.querySelectorAll('.assignment-row');
        var visibleInGroup = 0;

        rows.forEach(function(row) {
            var code = row.getAttribute('data-code') || '';
            var title = row.getAttribute('data-title') || '';
            var faculty = row.getAttribute('data-faculty') || '';
            var status = row.getAttribute('data-status') || '';
            
            var textMatches = !query || code.includes(query) || title.includes(query) || faculty.includes(query);
            var statusMatches = (statusFilter === 'ALL' || statusFilter === status);

            if (yearMatches && textMatches && statusMatches) {
                row.style.display = '';
                visibleInGroup++;
                totalVisible++;
            } else {
                row.style.display = 'none';
            }
        });

        // Hide container if no visible rows, unless it is an empty year level with no subjects and no search
        if (!yearMatches || (visibleInGroup === 0 && rows.length > 0)) {
            container.style.display = 'none';
        } else {
            container.style.display = '';
        }
        
        var countEl = container.querySelector('.group-count');
        if (countEl) {
            countEl.textContent = visibleInGroup;
        }
    });

    var counterBadge = document.getElementById('visibleCounter');
    if (counterBadge) {
        counterBadge.textContent = totalVisible + (totalVisible === 1 ? ' course' : ' courses');
    }

    var noResults = document.getElementById('noResultsState');
    if (noResults) {
        if (totalVisible === 0) {
            noResults.classList.remove('d-none');
        } else {
            noResults.classList.add('d-none');
        }
    }

    // Update filter badge and reset buttons
    var activeFilterCount = 0;
    if (yearFilter !== 'ALL') activeFilterCount++;
    if (statusFilter !== 'ALL') activeFilterCount++;

    var badge = document.getElementById('assignmentsFilterBadge');
    var triggerBtn = document.getElementById('assignmentsFilterBtn');
    var resetBtn = document.getElementById('assignmentsFilterResetBtn');
    var chipBar = document.getElementById('assignmentsChipBar');

    if (badge && triggerBtn) {
        if (activeFilterCount > 0) {
            badge.textContent = activeFilterCount;
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
        if (activeFilterCount > 0) {
            var chipsHtml = '<span class="small text-muted me-1">Active filters:</span>';
            if (yearFilter !== 'ALL') {
                var yearLabels = { '1': '1st Year', '2': '2nd Year', '3': '3rd Year', '4': '4th Year' };
                chipsHtml += '<span class="filter-chip"><span class="chip-label">Year:</span> ' + (yearLabels[yearFilter] || yearFilter) + ' <button type="button" class="chip-remove" onclick="clearAssignmentFilter(\'year\')">&times;</button></span>';
            }
            if (statusFilter !== 'ALL') {
                var statusLabel = statusFilter === 'ASSIGNED' ? 'Assigned' : 'Unassigned';
                chipsHtml += '<span class="filter-chip"><span class="chip-label">Status:</span> ' + statusLabel + ' <button type="button" class="chip-remove" onclick="clearAssignmentFilter(\'status\')">&times;</button></span>';
            }
            chipsHtml += '<button type="button" class="filter-clear-all-chip" onclick="resetDropdownFilters()">Clear filters</button>';
            chipBar.innerHTML = chipsHtml;
            chipBar.classList.remove('d-none');
        } else {
            chipBar.innerHTML = '';
            chipBar.classList.add('d-none');
        }
    }
}

function resetDropdownFilters() {
    var yearFilter = document.getElementById('yearLevelFilter');
    if (yearFilter) yearFilter.value = 'ALL';
    
    var statusFilter = document.getElementById('statusFilter');
    if (statusFilter) statusFilter.value = 'ALL';
    
    applyFilters();
}

function clearAssignmentFilter(type) {
    if (type === 'year') {
        var yearFilter = document.getElementById('yearLevelFilter');
        if (yearFilter) yearFilter.value = 'ALL';
    }
    if (type === 'status') {
        var statusFilter = document.getElementById('statusFilter');
        if (statusFilter) statusFilter.value = 'ALL';
    }
    applyFilters();
}

function resetFilters() {
    var searchInput = document.getElementById('assignmentSearch');
    if (searchInput) searchInput.value = '';
    resetDropdownFilters();
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
