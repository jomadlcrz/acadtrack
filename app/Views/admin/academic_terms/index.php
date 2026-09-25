<?php
$pageTitle = 'Academic Terms';
$subtitle = 'Create and manage the academic years and semesters used across scheduling, grades, and lifecycle operations.';
$activeTab = $activeTab ?? 'school-years';
$schoolYears = $schoolYears ?? [];
$currentYearName = $currentYearName ?? '—';
$ongoingCount = $ongoingCount ?? 0;
$semesters = $semesters ?? [
    ['semester_number' => 1, 'display_name' => '1st Semester', 'status' => 'Active'],
    ['semester_number' => 2, 'display_name' => '2nd Semester', 'status' => 'Active'],
];
$closures = $closures ?? [];
$terms = $terms ?? [];

$headerActions = '
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#helpModal">
            <i class="bi bi-question-circle"></i> Help
        </button>
        <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#createTermModal">
            <i class="bi bi-plus-lg"></i> Create Academic Term
        </button>
    </div>
';

ob_start();
?>

<!-- Module Navigation Tabs (Adopted from class-scheduling AcademicTermsModuleNav) -->
<div class="mb-4">
    <ul class="nav nav-pills p-1 bg-light rounded-3 d-inline-flex border" id="academicTermsTabs" role="tablist" style="border-color: #e2e8f0 !important;">
        <li class="nav-item" role="presentation">
            <a class="nav-link py-2 px-3.5 fw-medium d-inline-flex align-items-center gap-2 rounded-2 <?= $activeTab === 'school-years' ? 'active shadow-sm' : 'text-secondary' ?>" 
               id="tab-school-years-btn" 
               href="<?= url('/admin/academic-terms?tab=school-years') ?>" 
               role="tab" 
               aria-selected="<?= $activeTab === 'school-years' ? 'true' : 'false' ?>">
                <i class="bi bi-calendar3"></i> School Years
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link py-2 px-3.5 fw-medium d-inline-flex align-items-center gap-2 rounded-2 <?= $activeTab === 'semesters' ? 'active shadow-sm' : 'text-secondary' ?>" 
               id="tab-semesters-btn" 
               href="<?= url('/admin/academic-terms?tab=semesters') ?>" 
               role="tab" 
               aria-selected="<?= $activeTab === 'semesters' ? 'true' : 'false' ?>">
                <i class="bi bi-calendar-check"></i> Semesters
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link py-2 px-3.5 fw-medium d-inline-flex align-items-center gap-2 rounded-2 <?= $activeTab === 'closure' ? 'active shadow-sm' : 'text-secondary' ?>" 
               id="tab-closure-btn" 
               href="<?= url('/admin/academic-terms?tab=closure') ?>" 
               role="tab" 
               aria-selected="<?= $activeTab === 'closure' ? 'true' : 'false' ?>">
                <i class="bi bi-shield-lock"></i> Term Closure
            </a>
        </li>
    </ul>
</div>

<div class="tab-content" id="academicTermsTabsContent">

    <!-- ========================================== -->
    <!-- TAB 1: SCHOOL YEARS (Matching Reference)   -->
    <!-- ========================================== -->
    <div class="tab-pane fade <?= $activeTab === 'school-years' ? 'show active' : '' ?>" id="tab-school-years" role="tabpanel">
        <!-- Stat Cards (Adopted from class-scheduling AcademicTermsStatCard) -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-4">
                <div class="card border-0 shadow-sm p-3 rounded-3" style="border: 1px solid #e2e8f0 !important; background: #ffffff;">
                    <div class="text-uppercase fw-semibold text-muted small" style="font-size: 11px; letter-spacing: 0.05em;">
                        School years
                    </div>
                    <div class="mt-2 d-flex align-items-baseline gap-2">
                        <span class="fs-4 fw-bold text-dark font-monospace"><?= count($schoolYears) ?></span>
                        <span class="text-muted small">total registered</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-4">
                <div class="card border-0 shadow-sm p-3 rounded-3" style="border: 1px solid #e2e8f0 !important; background: #ffffff;">
                    <div class="text-uppercase fw-semibold text-muted small" style="font-size: 11px; letter-spacing: 0.05em;">
                        Current year
                    </div>
                    <div class="mt-2 d-flex align-items-center gap-2">
                        <span class="fs-4 fw-bold text-dark font-monospace"><?= htmlspecialchars($currentYearName) ?></span>
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle py-1 px-2 small">
                            <i class="bi bi-star-fill text-warning me-1"></i> Current
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-4">
                <div class="card border-0 shadow-sm p-3 rounded-3" style="border: 1px solid #e2e8f0 !important; background: #ffffff;">
                    <div class="text-uppercase fw-semibold text-muted small" style="font-size: 11px; letter-spacing: 0.05em;">
                        Ongoing
                    </div>
                    <div class="mt-2 d-flex align-items-baseline gap-2">
                        <span class="fs-4 fw-bold text-success font-monospace"><?= $ongoingCount ?></span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2 small">
                            Active calendar
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Input -->
        <div class="mb-3">
            <div class="input-group" style="max-width: 360px;">
                <span class="input-group-text bg-white border-end-0 text-muted">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control border-start-0 ps-0" id="sySearch" placeholder="Search school year…">
            </div>
        </div>

        <!-- School Years Table (Matching SchoolYearTable from class-scheduling) -->
        <div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">School Year</th>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-center" style="width: 180px; font-size: 11px;">Calendar Status</th>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-center" style="width: 140px; font-size: 11px;">Institutional</th>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-center" style="width: 180px; font-size: 11px;">Created At</th>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="width: 180px; font-size: 11px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" id="syTableBody">
                        <?php if (empty($schoolYears)): ?>
                            <tr>
                                <td colspan="5" class="py-5 text-center text-muted">
                                    <i class="bi bi-calendar-x fs-1 text-muted d-block mb-2"></i>
                                    No school years found. Click "Create School Year" to add one.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($schoolYears as $sy): ?>
                                <?php
                                $isCurrent = (int) ($sy['is_active'] ?? 0) === 1;
                                $status = $sy['calendar_status'] ?? 'Ongoing';
                                $statusTone = match ($status) {
                                    'Ongoing' => 'badge bg-success-subtle text-success border border-success-subtle',
                                    'Ended' => 'badge bg-secondary-subtle text-secondary border',
                                    'Upcoming' => 'badge bg-info-subtle text-info-emphasis border border-info-subtle',
                                    default => 'badge bg-light text-dark border',
                                };
                                $createdAt = !empty($sy['created_at']) ? date('M d, Y, g:i A', strtotime($sy['created_at'])) : '—';
                                ?>
                                <tr class="sy-row" data-search="<?= strtolower(htmlspecialchars($sy['school_year'])) ?>">
                                    <td class="py-3 px-4">
                                        <span class="font-monospace fw-bold text-dark fs-6">
                                            <?= htmlspecialchars($sy['school_year']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="<?= $statusTone ?> px-2.5 py-1 fw-medium">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 7px;"></i>
                                            <?= htmlspecialchars($status) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <?php if ($isCurrent): ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle py-1 px-2.5">
                                                ★ Current
                                            </span>
                                        <?php elseif ($status === 'Ended'): ?>
                                            <span class="badge bg-secondary-subtle text-secondary border py-1 px-2">
                                                Ended
                                            </span>
                                        <?php else: ?>
                                            <form method="POST" action="<?= url('/admin/academic-terms/school-years/' . $sy['id'] . '/toggle-active') ?>" class="m-0 d-inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-primary py-0.5 px-2 small" title="Set as current active school year">
                                                    Set Current
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-center text-muted small">
                                        <?= $createdAt ?>
                                    </td>
                                    <td class="py-3 px-4 text-end">
                                        <?php if ($status === 'Ended'): ?>
                                            <button type="button" class="btn btn-sm btn-light border py-1 px-2.5 text-muted small" disabled title="Ended school years are permanent historical records and cannot be modified">
                                                <i class="bi bi-lock-fill"></i> Locked
                                            </button>
                                        <?php else: ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-secondary py-1 px-2.5 d-inline-flex align-items-center gap-1"
                                                    onclick="openEditSchoolYearModal(<?= $sy['id'] ?>, '<?= htmlspecialchars($sy['school_year']) ?>')">
                                                <i class="bi bi-pencil"></i> Edit
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
    </div>

    <!-- ========================================== -->
    <!-- TAB 2: SEMESTERS (Matching Reference)      -->
    <!-- ========================================== -->
    <div class="tab-pane fade <?= $activeTab === 'semesters' ? 'show active' : '' ?>" id="tab-semesters" role="tabpanel">
        <div class="alert alert-info border-0 rounded-3 mb-4 d-flex align-items-center gap-2.5" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0 !important;">
            <i class="bi bi-info-circle fs-5"></i>
            <div>
                These are the global semester definitions (<strong>1st Semester</strong> and <strong>2nd Semester</strong>) shared across all school years.
            </div>
        </div>

        <div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="width: 180px; font-size: 11px;">Semester Number</th>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Display Name</th>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-center" style="width: 140px; font-size: 11px;">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($semesters as $sem): ?>
                            <tr>
                                <td class="py-3.5 px-4 font-monospace fw-bold text-dark fs-6">
                                    <?= $sem['semester_number'] ?>
                                </td>
                                <td class="py-3.5 px-4 fw-semibold text-dark">
                                    <?= htmlspecialchars($sem['display_name']) ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2.5">
                                        <i class="bi bi-check2 me-1"></i> Active
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 3: TERM CLOSURE (Matching Reference)   -->
    <!-- ========================================== -->
    <div class="tab-pane fade <?= $activeTab === 'closure' ? 'show active' : '' ?>" id="tab-closure" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h6 class="fw-bold mb-1 text-dark">Term Closure & Period Lifecycle</h6>
                <p class="text-muted small mb-0">
                    Declare a semester finished after grades are finalized. This locks records permanently against destructive changes while preserving transcripts and reports.
                </p>
            </div>
            <a href="<?= url('/admin/academic-terms/closure') ?>" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                <i class="bi bi-box-arrow-up-right"></i> Open Fullscreen Lifecycle
            </a>
        </div>

        <!-- Term Closure Table (Matching TermClosureTable from class-scheduling) -->
        <div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="width: 150px; font-size: 11px;">School Year</th>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 11px;">Semester</th>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-center" style="width: 120px; font-size: 11px;">Status</th>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Closed Reason</th>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-center" style="width: 140px; font-size: 11px;">Closed At</th>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="width: 220px; font-size: 11px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php if (empty($closures)): ?>
                            <tr>
                                <td colspan="6" class="py-5 text-center text-muted">
                                    No academic terms available for closure.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($closures as $closure): ?>
                                <?php
                                $isClosed = (int) ($closure['is_closed'] ?? 0) === 1;
                                $isEnded = !empty($closure['is_ended']);
                                $reopenable = !empty($closure['reopenable']);

                                if ($isEnded) {
                                    $statusBadge = '<span class="badge bg-secondary text-white py-1 px-2.5"><i class="bi bi-lock-fill me-1"></i> Ended</span>';
                                } elseif ($isClosed) {
                                    $statusBadge = '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle py-1 px-2.5"><i class="bi bi-lock-fill me-1"></i> Closed</span>';
                                } else {
                                    $statusBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2.5"><i class="bi bi-check2 me-1"></i> Open</span>';
                                }

                                $reasonDisplay = !empty($closure['closure_reason']) 
                                    ? htmlspecialchars($closure['closure_reason']) 
                                    : ($isEnded ? 'School year ended' : '—');
                                ?>
                                <tr>
                                    <td class="py-3 px-4 font-monospace fw-bold text-dark">
                                        <?= htmlspecialchars($closure['school_year']) ?>
                                    </td>
                                    <td class="py-3 px-4 fw-medium text-secondary">
                                        <?= htmlspecialchars($closure['semester_name']) ?>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <?= $statusBadge ?>
                                    </td>
                                    <td class="py-3 px-4 text-muted small">
                                        <?= $reasonDisplay ?>
                                    </td>
                                    <td class="py-3 px-4 text-center text-muted small">
                                        <?= !empty($closure['closed_at']) ? date('M d, Y', strtotime($closure['closed_at'])) : ($isEnded ? 'Calendar End' : '—') ?>
                                    </td>
                                    <td class="py-3 px-4 text-end">
                                        <div class="d-inline-flex align-items-center gap-1.5">
                                            <a href="<?= url('/admin/academic-terms/closure') ?>" class="btn btn-sm btn-outline-secondary py-1 px-2.5 small" title="View details">
                                                View details
                                            </a>
                                            <?php if ($isEnded): ?>
                                                <span class="badge bg-light text-muted border py-1.5 px-2 small" title="School year has ended. Historical records are permanent.">
                                                    <i class="bi bi-shield-lock"></i> Permanent
                                                </span>
                                            <?php elseif (!$isClosed): ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-warning py-1 px-2.5 d-inline-flex align-items-center gap-1 small"
                                                        onclick="openClosurePreview(<?= $closure['id'] ?>)">
                                                    <i class="bi bi-lock"></i> Post
                                                </button>
                                            <?php elseif ($reopenable): ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-secondary py-1 px-2.5 d-inline-flex align-items-center gap-1 small"
                                                        onclick="openReopenModal(<?= $closure['id'] ?>, '<?= htmlspecialchars($closure['semester_name']) ?>')">
                                                    <i class="bi bi-unlock"></i> Reopen
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- ========================================== -->
<!-- MODALS                                     -->
<!-- ========================================== -->

<!-- 1. Create School Year Modal (Matching reference auto-format) -->
<div class="modal fade" id="createTermModal" tabindex="-1" aria-labelledby="createTermModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="<?= url('/admin/academic-terms') ?>">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title h6 fw-bold mb-0" id="createTermModalLabel">Create Academic Term / School Year</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 space-y-3">
                    <div class="mb-3">
                        <label for="school_year" class="form-label small fw-semibold text-dark">School Year <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control font-monospace" 
                               id="school_year" 
                               name="school_year" 
                               placeholder="e.g. 2026-2027 or type 2026" 
                               required 
                               pattern="^(\d{4}|\d{4}-\d{4})$">
                        <div class="form-text small text-muted">Format: YYYY-YYYY (typing 4 digits like 2026 will auto-complete to 2026-2027)</div>
                    </div>

                    <div class="mb-3">
                        <label for="semester" class="form-label small fw-semibold text-dark">Semester <span class="text-danger">*</span></label>
                        <select class="form-select" id="semester" name="semester" required>
                            <option value="1">1st Semester</option>
                            <option value="2">2nd Semester</option>
                            <option value="3">Summer</option>
                        </select>
                    </div>

                    <div class="form-check form-switch pt-2">
                        <input class="form-check-input" type="checkbox" id="set_active" name="set_active" value="1">
                        <label class="form-check-label small fw-medium" for="set_active">Set as current active institutional term</label>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4 bg-light d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-check2"></i> Save Term
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 2. Edit School Year Modal -->
<div class="modal fade" id="editSchoolYearModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="editSchoolYearForm" action="">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title h6 fw-bold mb-0">Edit School Year</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="edit_school_year" class="form-label small fw-semibold text-dark">School Year <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control font-monospace" 
                               id="edit_school_year" 
                               name="school_year" 
                               required 
                               pattern="^(\d{4}|\d{4}-\d{4})$">
                        <div class="form-text small text-muted">Format: YYYY-YYYY (consecutive years)</div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4 bg-light d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-check2"></i> Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 3. Help Modal (Matching reference Help dialog) -->
<div class="modal fade" id="helpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title h6 fw-bold mb-0">About Academic Terms & Lifecycle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 space-y-3 font-body text-secondary" style="font-size: 14px; line-height: 1.6;">
                <p>
                    <strong>School Years:</strong> Each row represents an academic year (e.g. 2026-2027). Calendar status — <em>Ongoing</em>, <em>Ended</em>, or <em>Upcoming</em> — is computed automatically from today's date.
                </p>
                <p>
                    <strong>Semesters:</strong> There are two global institutional semesters (1st and 2nd). These are shared across all school years.
                </p>
                <p>
                    <strong>Lifecycle & Immutability:</strong> School years and terms are <strong>never archived or deleted</strong>, because they represent students' permanent transcripts of records. When a semester completes and all grades are verified, the Registrar <strong>Closes / Posts</strong> the term to freeze data.
                </p>
            </div>
            <div class="modal-footer border-top py-2.5 px-4 bg-light">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Got it</button>
            </div>
        </div>
    </div>
</div>

<!-- 4. Term Closure Pre-Closure Impact Modal -->
<div class="modal fade" id="closurePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="closureForm" action="">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title h6 fw-bold mb-0" id="previewTermTitle">Post Academic Term</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="previewLoading" class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                        <span class="text-muted">Loading pre-closure audit checks…</span>
                    </div>
                    <div id="previewContent" style="display: none;">
                        <div class="alert alert-warning border-0 rounded-3 mb-4 d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill fs-5 text-warning"></i>
                            <div>
                                <strong>Posting this term will freeze all grade sheets and attendance.</strong> Faculty will no longer be able to modify scores.
                            </div>
                        </div>

                        <div class="row g-3 mb-4 text-center">
                            <div class="col-4">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="fs-4 fw-bold text-dark font-monospace" id="previewTotalSheets">0</div>
                                    <div class="small text-muted">Total Sheets</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="fs-4 fw-bold text-success font-monospace" id="previewApprovedSheets">0</div>
                                    <div class="small text-muted">Finalized</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="fs-4 fw-bold text-warning-emphasis font-monospace" id="previewPendingSheets">0</div>
                                    <div class="small text-muted">Pending Review</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="closure_reason" class="form-label small fw-semibold text-dark">
                                Closure / Posting Reason (Audit Trail)
                            </label>
                            <input type="text" class="form-control" id="closure_reason" name="closure_reason" placeholder="e.g. Grades finalized and verified for graduation ranking">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4 bg-light d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark fw-semibold d-inline-flex align-items-center gap-1.5" id="btnConfirmClose">
                        <i class="bi bi-lock-fill"></i> Confirm & Post Term
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 5. Term Closure Reopen Modal -->
<div class="modal fade" id="reopenModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="reopenForm" action="">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title h6 fw-bold mb-0" id="reopenModalTitle">Reopen Academic Term</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-secondary small mb-3">
                        Reopening unlocks the term for faculty grade sheet adjustments. An audit reason is mandatory.
                    </p>
                    <div class="mb-3">
                        <label for="reopen_reason" class="form-label small fw-semibold text-dark">
                            Mandatory Reason for Reopening <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="reopen_reason" name="reopen_reason" rows="3" required placeholder="e.g. Registrar resolution regarding grade appeal for CS301"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4 bg-light d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-unlock"></i> Reopen Term
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Search Filter for School Years
    const sySearch = document.getElementById('sySearch');
    const syRows = document.querySelectorAll('.sy-row');
    if (sySearch) {
        sySearch.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            syRows.forEach(row => {
                const text = row.getAttribute('data-search') || '';
                row.style.display = (!query || text.includes(query)) ? '' : 'none';
            });
        });
    }

    // 2. Auto-format School Year just like reference (typing 2026 -> 2026-2027)
    const syInput = document.getElementById('school_year');
    if (syInput) {
        syInput.addEventListener('input', function(e) {
            if (e.inputType && e.inputType.startsWith('delete')) {
                return;
            }
            const raw = this.value.trim();
            if (/^\d{4}$/.test(raw)) {
                const yr = parseInt(raw, 10);
                this.value = `${yr}-${yr + 1}`;
            }
        });

        syInput.addEventListener('blur', function() {
            let raw = this.value.trim().replace(/[–—/]/g, '-').replace(/\s*-\s*/g, '-');
            if (/^\d{4}$/.test(raw)) {
                const yr = parseInt(raw, 10);
                raw = `${yr}-${yr + 1}`;
            } else if (/^(\d{4})-(\d{2})$/.test(raw)) {
                const match = raw.match(/^(\d{4})-(\d{2})$/);
                const century = match[1].substring(0, 2);
                raw = `${match[1]}-${century}${match[2]}`;
            }
            this.value = raw;
        });
    }

    const editSyInput = document.getElementById('edit_school_year');
    if (editSyInput) {
        editSyInput.addEventListener('input', function(e) {
            if (e.inputType && e.inputType.startsWith('delete')) {
                return;
            }
            const raw = this.value.trim();
            if (/^\d{4}$/.test(raw)) {
                const yr = parseInt(raw, 10);
                this.value = `${yr}-${yr + 1}`;
            }
        });
    }
});

// Edit School Year Modal Trigger
function openEditSchoolYearModal(id, currentName) {
    const form = document.getElementById('editSchoolYearForm');
    const input = document.getElementById('edit_school_year');
    if (form && input) {
        form.action = `<?= url('/admin/academic-terms/school-years/') ?>${id}/update`;
        input.value = currentName;
        const modal = new bootstrap.Modal(document.getElementById('editSchoolYearModal'));
        modal.show();
    }
}

// Term Closure Preview Trigger
function openClosurePreview(termId) {
    const previewModalEl = document.getElementById('closurePreviewModal');
    const previewModal = new bootstrap.Modal(previewModalEl);
    previewModal.show();

    const loadingEl = document.getElementById('previewLoading');
    const contentEl = document.getElementById('previewContent');
    const formEl = document.getElementById('closureForm');
    formEl.action = `<?= url('/admin/academic-terms/') ?>${termId}/close`;

    loadingEl.style.display = 'block';
    contentEl.style.display = 'none';

    fetch(`<?= url('/admin/academic-terms/') ?>${termId}/closure-preview`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.preview) {
                const p = data.preview;
                document.getElementById('previewTermTitle').textContent = `Post ${p.term.semester_name}, ${p.term.school_year}`;
                document.getElementById('previewTotalSheets').textContent = p.counts.total_sheets;
                document.getElementById('previewApprovedSheets').textContent = p.counts.approved_sheets;
                document.getElementById('previewPendingSheets').textContent = p.counts.pending_sheets;
                document.getElementById('closure_reason').value = p.default_reason || '';

                loadingEl.style.display = 'none';
                contentEl.style.display = 'block';
            }
        })
        .catch(() => {
            alert('Failed to load closure preview audit.');
            previewModal.hide();
        });
}

// Term Reopen Trigger
function openReopenModal(termId, semesterName) {
    const modalEl = document.getElementById('reopenModal');
    const formEl = document.getElementById('reopenForm');
    formEl.action = `<?= url('/admin/academic-terms/') ?>${termId}/reopen`;
    document.getElementById('reopenModalTitle').textContent = `Reopen ${semesterName}`;
    document.getElementById('reopen_reason').value = '';
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
