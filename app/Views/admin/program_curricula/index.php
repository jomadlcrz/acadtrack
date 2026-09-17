<?php
$pageTitle = 'Program Curricula';
$subtitle = 'Manage degree programs, curriculum frameworks, and subject sequences.';

$headerActions = '<a href="' . url('/admin/program-curricula/new') . '" class="btn btn-primary d-inline-flex align-items-center gap-1.5"><i class="bi bi-plus-lg"></i> New Curriculum</a>';


$subjectTypesList = [
    'GenEd Core',
    'Major with Lab',
    'Major without Lab',
    'GenEd Elective',
    'Physical Education',
    'National Service Training Program',
    'Mandated Rizal',
    'Research/Thesis',
    'Institutional Requirement',
    'Practicum/OJT',
];

$yearLevelOptions = [
    1 => 'First Year',
    2 => 'Second Year',
    3 => 'Third Year',
    4 => 'Fourth Year',
    5 => 'Fifth Year',
];

$semesterOptions = [
    1 => '1st Semester',
    2 => '2nd Semester',
    3 => 'Summer',
];

ob_start();
?>

<!-- Print-only header -->
<div class="d-none d-print-block mb-4">
    <h2 class="h4 mb-1"><?= htmlspecialchars($selectedProgram ? $selectedProgram->program_name : 'Program Curriculum') ?></h2>
    <div class="text-muted small">
        Program Code: <?= htmlspecialchars($selectedProgram->program_abbrev ?? '') ?> | 
        Total Units: <?= $totalUnits ?> | 
        Printed on <?= date('F j, Y') ?>
    </div>
    <hr>
</div>

<?php if (empty($programs) || $programs->isEmpty()): ?>
    <?php
    $icon = 'bi-book';
    $iconColor = 'blue';
    $title = 'No program curricula yet';
    $message = 'Create the first program and its curriculum to start organizing subjects, units, and academic pathways.';
    $actionHtml = '<a href="' . url('/admin/program-curricula/new') . '" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Create First Curriculum</a>';
    $card = true;
    include __DIR__ . '/../../components/empty-state.php';
    ?>
<?php else: ?>

    <!-- Program Selector & Header Card -->
    <div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
        <div class="p-4 border-bottom bg-white">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="programSelect" class="form-label text-uppercase text-secondary fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">
                        Selected Program
                    </label>
                    <select id="programSelect" class="form-select fw-medium" onchange="window.location.href = '<?= url('/admin/program-curricula') ?>?program=' + encodeURIComponent(this.value);">
                        <?php foreach ($programs as $p): ?>
                            <option value="<?= htmlspecialchars($p->program_abbrev) ?>" <?= $p->program_abbrev === $selectedAbbrev ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p->program_abbrev) ?> — <?= htmlspecialchars($p->program_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-8 d-flex flex-wrap justify-content-md-end align-items-center gap-2">
                    <div class="position-relative flex-grow-1 flex-sm-grow-0" style="min-width: 220px;">
                        <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 12px; font-size: 13px;"></i>
                        <input type="text" id="curriculumSubjectSearch" class="form-control form-control-sm ps-4" placeholder="Search subjects..." autocomplete="off">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1.5" onclick="window.print();">
                        <i class="bi bi-printer"></i> Print
                    </button>
                    <?php if ($selectedProgram): ?>
                        <a href="<?= url('/admin/program-curricula/' . $selectedProgram->id . '/export-csv') ?>" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1.5">
                            <i class="bi bi-download"></i> CSV / Excel
                        </a>
                        <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                            <i class="bi bi-plus-lg"></i> Add Subject
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($selectedProgram): ?>
            <!-- Program Metadata Banner -->
            <div class="px-4 py-3 bg-light border-bottom">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h2 class="h5 fw-bold text-dark mb-0">
                                <?= htmlspecialchars($selectedProgram->program_name) ?> (<?= htmlspecialchars($selectedProgram->program_abbrev) ?>)
                            </h2>
                        </div>
                        <div class="text-secondary small mt-1">
                            <?= htmlspecialchars($selectedProgram->department->name ?? 'Academic Department') ?>
                            <?php if (!empty($selectedProgram->department->code)): ?>
                                (<?= htmlspecialchars($selectedProgram->department->code) ?>)
                            <?php endif; ?>
                        </div>
                        <div class="d-flex flex-wrap gap-3 mt-1 small text-secondary">
                            <span><span class="text-muted">Type:</span> <span class="fw-semibold text-dark"><?= htmlspecialchars($selectedProgram->program_type ?? "Bachelor's Degree") ?></span></span>
                            <span><span class="text-muted">Length:</span> <span class="fw-semibold text-dark"><?= htmlspecialchars($selectedProgram->program_length ?? '4 Years') ?></span></span>
                        </div>
                        <?php if (!empty($selectedProgram->description)): ?>
                            <p class="small text-muted mb-0 mt-1"><?= htmlspecialchars($selectedProgram->description) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-white border rounded px-3 py-1.5 small text-nowrap">
                            <span class="text-muted">Subjects:</span> <span class="fw-bold text-dark" id="statSubjectsCount"><?= $totalSubjects ?></span>
                        </div>
                        <div class="bg-white border rounded px-3 py-1.5 small text-nowrap">
                            <span class="text-muted">Total Units:</span> <span class="fw-bold text-dark"><?= number_format($totalUnits, 1) ?></span>
                        </div>
                        <span class="badge text-uppercase <?= ($selectedProgram->status ?? 'active') === 'active' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' ?>">
                            <?= htmlspecialchars($selectedProgram->status ?? 'active') ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Subjects Grouped by Year and Semester -->
    <?php if (empty($groupedSubjects)): ?>
        <?php
        $icon = 'bi-journal-x';
        $title = 'No subjects found';
        $message = 'No subjects found in this curriculum. Click "Add Subject" or "New Curriculum" to add subjects.';
        $card = true;
        include __DIR__ . '/../../components/empty-state.php';
        ?>
    <?php else: ?>
        <div class="space-y-4" id="curriculumGroupsContainer">
            <?php foreach ($groupedSubjects as $group): ?>
                <div class="card shadow-sm border-0 mb-4 curriculum-group-card" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
                    <div class="card-header bg-light py-2.5 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <h3 class="h6 fw-bold text-dark mb-0"><?= htmlspecialchars($group['title']) ?></h3>
                        <div class="small text-muted">
                            <span class="group-count"><?= count($group['subjects']) ?></span> <?= count($group['subjects']) === 1 ? 'subject' : 'subjects' ?> · 
                            <span class="fw-semibold text-dark"><?= number_format($group['total_units'], 1) ?> units</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-white border-bottom">
                                <tr>
                                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 11px;">Subject Code</th>
                                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Descriptive Title</th>
                                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 90px; font-size: 11px;">Units</th>
                                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="width: 190px; font-size: 11px;">Subject Type</th>
                                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="width: 200px; font-size: 11px;">Pre-Requisite</th>
                                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="width: 80px; font-size: 11px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <?php foreach ($group['subjects'] as $sub): ?>
                                    <tr class="curriculum-subject-row" data-search="<?= strtolower(htmlspecialchars($sub->subject_code . ' ' . $sub->descriptive_title . ' ' . $sub->subject_type)) ?>">
                                        <td class="py-3 px-4 fw-bold text-dark font-monospace">
                                            <?= htmlspecialchars($sub->subject_code) ?>
                                        </td>
                                        <td class="py-3 px-4 fw-medium text-dark">
                                            <?= htmlspecialchars($sub->descriptive_title) ?>
                                        </td>
                                        <td class="py-3 px-3 text-center tabular-nums fw-semibold text-secondary">
                                            <?= number_format((float) $sub->units, 1) ?>
                                        </td>
                                        <td class="py-3 px-3 small text-secondary">
                                            <?= htmlspecialchars($sub->subject_type) ?>
                                        </td>
                                        <td class="py-3 px-4 small">
                                            <?php if (!empty($sub->prerequisites)): ?>
                                                <?php foreach (explode(',', $sub->prerequisites) as $pr): ?>
                                                    <span class="badge bg-light text-secondary border me-1">
                                                        <?= htmlspecialchars(trim($pr)) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-4 text-end">
                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-sm btn-action-trigger" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false" title="Actions">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end action-dropdown-menu shadow-sm">
                                                    <li>
                                                        <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editSubjectModal<?= $sub->id ?>">
                                                            <i class="bi bi-pencil text-muted"></i> Edit subject
                                                        </button>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="<?= url('/admin/program-curricula/subjects/' . $sub->id . '/archive') ?>" class="m-0" onsubmit="return confirm('Archive subject <?= htmlspecialchars(addslashes($sub->subject_code)) ?> (<?= htmlspecialchars(addslashes($sub->descriptive_title)) ?>)?');">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="program" value="<?= htmlspecialchars($selectedAbbrev) ?>">
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="bi bi-archive"></i> Archive subject
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Subject Modal -->
                                    <div class="modal fade" id="editSubjectModal<?= $sub->id ?>" tabindex="-1" aria-labelledby="editSubjectModalLabel<?= $sub->id ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <form method="POST" action="<?= url('/admin/program-curricula/subjects/' . $sub->id) ?>">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="program" value="<?= htmlspecialchars($selectedAbbrev) ?>">
                                                    <div class="modal-header border-bottom py-3 px-4">
                                                        <h5 class="modal-title h6 fw-semibold mb-0" id="editSubjectModalLabel<?= $sub->id ?>">
                                                            Edit Subject: <span class="font-monospace text-primary"><?= htmlspecialchars($sub->subject_code) ?></span>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-4 text-start">
                                                        <div class="row g-3">
                                                            <div class="col-12 col-md-5">
                                                                <label for="subCode_<?= $sub->id ?>" class="form-label small fw-semibold text-dark">Subject Code <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control font-monospace fw-bold" id="subCode_<?= $sub->id ?>" name="subject_code" value="<?= htmlspecialchars($sub->subject_code) ?>" required style="text-transform: uppercase;">
                                                            </div>
                                                            <div class="col-12 col-md-7">
                                                                <label for="subUnits_<?= $sub->id ?>" class="form-label small fw-semibold text-dark">Credit Units <span class="text-danger">*</span></label>
                                                                <input type="number" step="0.5" min="0" max="30" class="form-control" id="subUnits_<?= $sub->id ?>" name="units" value="<?= htmlspecialchars((string) $sub->units) ?>" required>
                                                            </div>
                                                            <div class="col-12">
                                                                <label for="subTitle_<?= $sub->id ?>" class="form-label small fw-semibold text-dark">Descriptive Title <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control" id="subTitle_<?= $sub->id ?>" name="descriptive_title" value="<?= htmlspecialchars($sub->descriptive_title) ?>" required>
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <label for="subYear_<?= $sub->id ?>" class="form-label small fw-semibold text-dark">Year Level <span class="text-danger">*</span></label>
                                                                <select class="form-select" id="subYear_<?= $sub->id ?>" name="year_level" required>
                                                                    <?php
                                                                    $currentYl = match(true) {
                                                                        is_numeric($sub->year_level) => (int) $sub->year_level,
                                                                        str_contains(strtolower((string) $sub->year_level), 'first') => 1,
                                                                        str_contains(strtolower((string) $sub->year_level), 'second') => 2,
                                                                        str_contains(strtolower((string) $sub->year_level), 'third') => 3,
                                                                        str_contains(strtolower((string) $sub->year_level), 'fourth') => 4,
                                                                        str_contains(strtolower((string) $sub->year_level), 'fifth') => 5,
                                                                        default => 1,
                                                                    };
                                                                    ?>
                                                                    <?php foreach ($yearLevelOptions as $ylNum => $ylName): ?>
                                                                        <option value="<?= $ylNum ?>" <?= $currentYl === $ylNum ? 'selected' : '' ?>><?= $ylName ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <label for="subSem_<?= $sub->id ?>" class="form-label small fw-semibold text-dark">Semester <span class="text-danger">*</span></label>
                                                                <select class="form-select" id="subSem_<?= $sub->id ?>" name="semester" required>
                                                                    <?php foreach ($semesterOptions as $sNum => $sName): ?>
                                                                        <option value="<?= $sNum ?>" <?= (int) $sub->semester === $sNum ? 'selected' : '' ?>><?= $sName ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-12">
                                                                <label for="subType_<?= $sub->id ?>" class="form-label small fw-semibold text-dark">Subject Type</label>
                                                                <select class="form-select" id="subType_<?= $sub->id ?>" name="subject_type">
                                                                    <?php
                                                                    $typeFound = false;
                                                                    foreach ($subjectTypesList as $st) {
                                                                        $isSelected = ($sub->subject_type === $st);
                                                                        if ($isSelected) $typeFound = true;
                                                                        echo '<option value="' . htmlspecialchars($st) . '" ' . ($isSelected ? 'selected' : '') . '>' . htmlspecialchars($st) . '</option>';
                                                                    }
                                                                    if (!$typeFound && !empty($sub->subject_type)) {
                                                                        echo '<option value="' . htmlspecialchars($sub->subject_type) . '" selected>' . htmlspecialchars($sub->subject_type) . '</option>';
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-12">
                                                                <label for="subPrereq_<?= $sub->id ?>" class="form-label small fw-semibold text-dark">Pre-Requisites</label>
                                                                <input type="text" class="form-control font-monospace" id="subPrereq_<?= $sub->id ?>" name="prerequisites" value="<?= htmlspecialchars($sub->prerequisites ?? '') ?>" placeholder="e.g. IT101, IT102" style="text-transform: uppercase;">
                                                                <div class="form-text text-muted" style="font-size: 11px;">Separate multiple subject codes with commas.</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light py-2.5 px-4 border-top">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1.5">
                                                            <i class="bi bi-check2"></i> Save Changes
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Dynamic Search Fallback Card -->
        <div id="noCurriculumSearchResults" class="d-none mb-4">
            <?php
            $icon = 'bi-search';
            $title = 'No matching subjects found';
            $message = 'No curriculum subjects match your search keywords.';
            $actionHtml = '<button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetCurriculumSearch()"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset Search</button>';
            $card = true;
            include __DIR__ . '/../../components/empty-state.php';
            ?>
        </div>
    <?php endif; ?>

<?php endif; ?>

<!-- Add Subject Modal -->
<?php if ($selectedProgram): ?>
<div class="modal fade" id="addSubjectModal" tabindex="-1" aria-labelledby="addSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="<?= url('/admin/program-curricula/subjects') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="program_id" value="<?= $selectedProgram->id ?>">
                <input type="hidden" name="program" value="<?= htmlspecialchars($selectedAbbrev) ?>">
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title h6 fw-semibold mb-0" id="addSubjectModalLabel">
                        Add Subject to <span class="text-primary"><?= htmlspecialchars($selectedProgram->program_abbrev) ?></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-start">
                    <div class="row g-3">
                        <div class="col-12 col-md-5">
                            <label for="newSubCode" class="form-label small fw-semibold text-dark">Subject Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control font-monospace fw-bold" id="newSubCode" name="subject_code" placeholder="e.g. IT201" required style="text-transform: uppercase;">
                        </div>
                        <div class="col-12 col-md-7">
                            <label for="newSubUnits" class="form-label small fw-semibold text-dark">Credit Units <span class="text-danger">*</span></label>
                            <input type="number" step="0.5" min="0" max="30" class="form-control" id="newSubUnits" name="units" value="3.0" required>
                        </div>
                        <div class="col-12">
                            <label for="newSubTitle" class="form-label small fw-semibold text-dark">Descriptive Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="newSubTitle" name="descriptive_title" placeholder="e.g. Data Structures and Algorithms" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="newSubYear" class="form-label small fw-semibold text-dark">Year Level <span class="text-danger">*</span></label>
                            <select class="form-select" id="newSubYear" name="year_level" required>
                                <?php foreach ($yearLevelOptions as $ylNum => $ylName): ?>
                                    <option value="<?= $ylNum ?>"><?= $ylName ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="newSubSem" class="form-label small fw-semibold text-dark">Semester <span class="text-danger">*</span></label>
                            <select class="form-select" id="newSubSem" name="semester" required>
                                <?php foreach ($semesterOptions as $sNum => $sName): ?>
                                    <option value="<?= $sNum ?>"><?= $sName ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="newSubType" class="form-label small fw-semibold text-dark">Subject Type</label>
                            <select class="form-select" id="newSubType" name="subject_type">
                                <?php foreach ($subjectTypesList as $st): ?>
                                    <option value="<?= htmlspecialchars($st) ?>" <?= $st === 'Major with Lab' ? 'selected' : '' ?>><?= htmlspecialchars($st) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="newSubPrereq" class="form-label small fw-semibold text-dark">Pre-Requisites</label>
                            <input type="text" class="form-control font-monospace" id="newSubPrereq" name="prerequisites" placeholder="e.g. IT101, IT102" style="text-transform: uppercase;">
                            <div class="form-text text-muted" style="font-size: 11px;">Separate multiple subject codes with commas (optional).</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5 px-4 border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-plus-lg"></i> Add Subject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function resetCurriculumSearch() {
    const input = document.getElementById('curriculumSubjectSearch');
    if (input) {
        input.value = '';
        input.dispatchEvent(new Event('input'));
        input.focus();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('curriculumSubjectSearch');
    const groupCards = document.querySelectorAll('.curriculum-group-card');
    const noResults = document.getElementById('noCurriculumSearchResults');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const q = this.value.trim().toLowerCase();
            let totalVisible = 0;

            groupCards.forEach(function(card) {
                const rows = card.querySelectorAll('.curriculum-subject-row');
                let visibleRows = 0;

                rows.forEach(function(row) {
                    const searchData = row.getAttribute('data-search') || '';
                    if (!q || searchData.includes(q)) {
                        row.style.display = '';
                        visibleRows++;
                        totalVisible++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Hide card if no rows match
                card.style.display = visibleRows > 0 ? '' : 'none';
                const countBadge = card.querySelector('.group-count');
                if (countBadge) {
                    countBadge.textContent = visibleRows;
                }
            });

            if (noResults) {
                if (totalVisible === 0) {
                    noResults.classList.remove('d-none');
                } else {
                    noResults.classList.add('d-none');
                }
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
