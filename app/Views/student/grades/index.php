<?php
$pageTitle = 'My Academic Grades';
$subtitle = 'Official enrolled classes and grade report across academic terms.';
$headerActions = '<a href="' . url('/student/evaluation') . '" class="btn btn-primary d-inline-flex align-items-center gap-2"><i class="bi bi-award"></i> View whole evaluation</a>';
ob_start();
?>

<!-- Semester Switcher Toolbar -->
<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="card-body py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small fw-semibold">Academic term:</span>
            <span class="fw-semibold text-dark small"><?= htmlspecialchars($academicTerm['academic_year_name'] ?? '2026-2027') ?></span>
        </div>
        <div class="btn-group btn-group-sm" role="group" aria-label="Semester selection">
            <a href="<?= url('/student/grades?semester=1') ?>" class="btn <?= ($selectedSemester ?? '1') === '1' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                1st Semester
            </a>
            <a href="<?= url('/student/grades?semester=2') ?>" class="btn <?= ($selectedSemester ?? '1') === '2' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                2nd Semester
            </a>
        </div>
    </div>
</div>

<!-- List of Classes Card -->
<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h3 class="h6 mb-0 fw-semibold text-dark d-flex align-items-center gap-2">
            <i class="bi bi-list-task"></i>
            <span>List of Classes</span>
            <span class="badge bg-secondary-subtle text-secondary rounded-pill font-monospace" style="font-size: 11px;" id="classCountBadge">
                <?= count($summary) ?>
            </span>
        </h3>
        <span class="badge badge-student">Active enrollment</span>
    </div>

    <!-- Table Controls Toolbar -->
    <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-2">
            <label for="classEntriesSelect" class="text-secondary small mb-0">Show</label>
            <select id="classEntriesSelect" class="form-select form-select-sm" style="width: 80px;">
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="all">All</option>
            </select>
            <span class="text-secondary small">entries</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <label for="classSearchInput" class="text-secondary small mb-0">Search:</label>
            <input type="search" id="classSearchInput" class="form-control form-control-sm" placeholder="Search class or instructor..." style="width: 240px;">
        </div>
    </div>

    <?php if (empty($summary)): ?>
        <?php
        $icon = 'bi-journal-x';
        $iconColor = 'blue';
        $title = 'No classes available yet';
        $message = 'No enrolled classes or official grades recorded for the selected semester.';
        include __DIR__ . '/../../components/empty-state.php';
        ?>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="classesTable">
                <thead class="bg-white border-bottom">
                    <tr>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Subject code</th>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Descriptive title</th>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Instructor</th>
                        <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="font-size: 11px;">Status</th>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-center" style="font-size: 11px; width: 140px;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($summary as $code => $classItem): ?>
                        <?php
                        $instructorName = (string) ($classItem['instructor'] ?? 'TBA');
                        $subjectName = (string) ($classItem['subject_name'] ?? '');
                        $subjectCode = (string) ($classItem['subject_code'] ?? $code);
                        $status = (string) ($classItem['status'] ?? 'In progress');
                        $jsonData = htmlspecialchars(json_encode($classItem), ENT_QUOTES, 'UTF-8');
                        $searchTarget = htmlspecialchars(strtolower($subjectCode . ' ' . $subjectName . ' ' . $instructorName));
                        ?>
                        <tr class="class-row" data-search-target="<?= $searchTarget ?>">
                            <td class="px-4 fw-semibold font-monospace text-dark">
                                <div class="d-flex align-items-center gap-1.5">
                                    <i class="bi bi-house-door-fill text-muted" aria-hidden="true"></i>
                                    <span><?= htmlspecialchars($subjectCode) ?></span>
                                </div>
                            </td>
                            <td class="px-4">
                                <div class="fw-semibold text-dark small">
                                    <?= htmlspecialchars($subjectName) ?>
                                </div>
                                <div class="text-secondary" style="font-size: 11px;">
                                    <?= (int) ($classItem['units'] ?? 3) ?> units • <?= htmlspecialchars((string) ($classItem['nature'] ?? 'Lecture')) ?>
                                </div>
                            </td>
                            <td class="px-4 text-secondary small">
                                <?= htmlspecialchars($instructorName) ?>
                            </td>
                            <td class="px-3 text-center">
                                <?php if ($status === 'Passed'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Passed</span>
                                <?php elseif ($status === 'Failed'): ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Failed</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">In progress</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 text-center">
                                <button type="button" class="btn btn-sm btn-primary view-grade-btn d-inline-flex align-items-center gap-1.5" data-class="<?= $jsonData ?>">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>View grades</span>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Table Footer / Pagination -->
        <div class="p-3 bg-white border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing <span id="classShowingStart" class="fw-semibold text-dark">1</span> to <span id="classShowingEnd" class="fw-semibold text-dark"><?= count($summary) ?></span> of <span id="classTotalCount" class="fw-semibold text-dark"><?= count($summary) ?></span> entries
            </div>
            <nav aria-label="Class pagination">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item">
                        <button class="page-link" id="classPrevBtn" type="button" aria-label="Previous">Previous</button>
                    </li>
                    <li class="page-item active" aria-current="page">
                        <span class="page-link" id="classCurrentPage">1</span>
                    </li>
                    <li class="page-item">
                        <button class="page-link" id="classNextBtn" type="button" aria-label="Next">Next</button>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- Class Grade Record Modal -->
<div class="modal fade" id="classGradeModal" tabindex="-1" aria-labelledby="classGradeModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="classGradeModalTitle">
                    <i class="bi bi-journal-check text-primary"></i>
                    <span>Class Grade Record</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Course Overview Header -->
                <div class="p-3 bg-light rounded mb-3 border">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-primary font-monospace" id="modalSubjectCode">CC103</span>
                                <h6 class="mb-0 fw-bold text-dark" id="modalSubjectTitle">Intermediate Programming</h6>
                            </div>
                            <div class="text-muted small">
                                Instructor: <strong class="text-dark" id="modalInstructor">TBA</strong>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-secondary-subtle text-secondary me-1" id="modalUnits">3 Units</span>
                            <span class="badge bg-secondary-subtle text-secondary" id="modalNature">Lecture</span>
                        </div>
                    </div>
                </div>

                <!-- Periodic Grades Table -->
                <div class="table-responsive mb-3 border rounded">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light border-bottom">
                            <tr>
                                <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Grading period</th>
                                <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="font-size: 11px;">Score / Rating</th>
                                <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="font-size: 11px;">Publication status</th>
                            </tr>
                        </thead>
                        <tbody id="modalPeriodsBody" class="divide-y">
                            <!-- Injected dynamically by student-grades.js -->
                        </tbody>
                    </table>
                </div>

                <!-- Overall Grade & Standing Summary -->
                <div class="p-3 bg-light rounded border d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <div class="text-muted small">Overall computed average:</div>
                        <div class="h4 mb-0 fw-bold font-monospace text-dark" id="modalComputedAverage">—</div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small mb-1">Academic standing:</div>
                        <span id="modalStatusBadge" class="badge bg-secondary text-white px-2.5 py-1.5">In progress</span>
                    </div>
                </div>
                <div class="mt-2 text-center">
                    <p id="modalStatusNotice" class="text-muted small mb-0">
                        Grades are displayed only after official approval and finalization by academic administration.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <a href="<?= url('/student/evaluation') ?>" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5">
                    <i class="bi bi-award"></i>
                    <span>View whole evaluation</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('js/pages/student-grades.js') ?>"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
