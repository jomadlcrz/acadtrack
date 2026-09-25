<?php
$pageTitle = 'Term Closure & Lifecycle';
$subtitle = 'Manage official semester closure, grade locking, and academic rollover.';
$headerActions = '<a href="' . url('/admin/academic-terms') . '" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1.5"><i class="bi bi-arrow-left"></i> Back to Terms</a>';
ob_start();
?>

<!-- Lifecycle Workflow Card (Adopted from class-scheduling) -->
<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 8px;">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-uppercase fw-semibold mb-2" style="font-size: 11px; letter-spacing: 0.05em;">
                    Academic Term Lifecycle
                </span>
                <h5 class="fw-bold mb-1" style="color: #0f172a;">
                    <?= htmlspecialchars($activeTerm['school_year'] ?? '2026-2027') ?> Operational Sequence
                </h5>
                <p class="text-muted small mb-0">
                    Following standard Philippine college registrar governance: post 1st Semester when grades are complete, then run 2nd Semester.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Status:</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1.5 fw-semibold">
                    <i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i>
                    <?= htmlspecialchars($activeTerm['semester_name'] ?? 'Active Semester') ?> Running
                </span>
            </div>
        </div>

        <!-- Workflow Stepper Progression -->
        <div class="row g-3">
            <?php foreach ($closures as $idx => $term): ?>
                <?php if (($term['school_year'] ?? '') === ($activeTerm['school_year'] ?? '2026-2027')): ?>
                    <div class="col-md-4">
                        <div class="p-3 rounded-3 border h-100 <?= $term['is_closed'] ? 'bg-light border-danger-subtle' : ($term['is_active'] ? 'bg-primary-subtle border-primary' : 'bg-white border-light-subtle') ?>">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-dark" style="font-size: 14px;">
                                    <?= htmlspecialchars($term['semester_name']) ?>
                                </span>
                                <?php if ($term['is_closed']): ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle small">
                                        <i class="bi bi-lock-fill me-1"></i> Closed & Posted
                                    </span>
                                <?php elseif ($term['is_active']): ?>
                                    <span class="badge bg-success text-white small">
                                        <i class="bi bi-play-fill me-1"></i> Active Term
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary small">
                                        Pending
                                    </span>
                                <?php endif; ?>
                            </div>

                            <p class="text-muted small mb-3" style="font-size: 12px; min-height: 36px;">
                                <?php if ($term['is_closed']): ?>
                                    Closed on <?= date('M d, Y', strtotime($term['closed_at'])) ?>.<br>
                                    <span class="text-truncate d-inline-block mw-100" title="<?= htmlspecialchars($term['closure_reason'] ?? '') ?>">
                                        Reason: <?= htmlspecialchars($term['closure_reason'] ?? 'Official closure') ?>
                                    </span>
                                <?php elseif ($term['is_active']): ?>
                                    Currently accepting grades and attendance. Click below to verify and close once finalized.
                                <?php else: ?>
                                    Upcoming semester. Ready for activation after current semester closes.
                                <?php endif; ?>
                            </p>

                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <span class="text-muted small" style="font-size: 11px;">
                                    <?= $term['stats']['approved_sheets_count'] ?? 0 ?> / <?= $term['stats']['sheets_count'] ?? 0 ?> sheets submitted
                                </span>
                                <?php if (!$term['is_closed'] && $term['is_active']): ?>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1"
                                            onclick="openClosurePreview(<?= $term['id'] ?>)">
                                        <i class="bi bi-lock"></i> Close Term
                                    </button>
                                <?php elseif ($term['is_closed']): ?>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"
                                            onclick="openReopenModal(<?= $term['id'] ?>, '<?= htmlspecialchars($term['semester_name']) ?>')">
                                        <i class="bi bi-unlock"></i> Reopen
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <!-- Card 3: School Year Completion / Rollover (Adopted from class-scheduling) -->
            <?php
            $activeYear = $activeTerm['school_year'] ?? '2026-2027';
            $termsInActiveYear = array_filter($closures, fn($t) => ($t['school_year'] ?? '') === $activeYear);
            $allClosed = !empty($termsInActiveYear) && count(array_filter($termsInActiveYear, fn($t) => !$t['is_closed'])) === 0;
            ?>
            <div class="col-md-4">
                <div class="p-3 rounded-3 border h-100 <?= $allClosed ? 'bg-success-subtle border-success' : 'bg-light border-light-subtle' ?>">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-dark" style="font-size: 14px;">
                            SY <?= htmlspecialchars($activeYear) ?> Rollover
                        </span>
                        <?php if ($allClosed): ?>
                            <span class="badge bg-success text-white small">
                                <i class="bi bi-check-circle-fill me-1"></i> Year Completed
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary small">
                                Running
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="text-muted small mb-3" style="font-size: 12px; min-height: 36px;">
                        <?php if ($allClosed): ?>
                            All semesters for <?= htmlspecialchars($activeYear) ?> have been officially closed and posted. You may create the next academic school year.
                        <?php else: ?>
                            Once both 1st and 2nd semesters are closed and posted, the academic school year will be complete.
                        <?php endif; ?>
                    </p>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <span class="text-muted small" style="font-size: 11px;">
                            <?= count($termsInActiveYear) ?> semestral terms
                        </span>
                        <a href="<?= url('/admin/academic-terms') ?>" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                            <i class="bi bi-calendar-plus"></i> New Term
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Term Closures Table & Historical Ledger -->
<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 8px;">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <div>
            <h6 class="fw-bold mb-0 text-dark">All Academic Terms Ledger</h6>
            <span class="text-muted small">Overview of semester lock states, grading period locks, and audit details.</span>
        </div>
        <div class="text-muted small">
            <?= count($closures) ?> total terms recorded
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="closuresTable">
            <thead class="bg-light border-bottom">
                <tr>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px; width: 180px;">School Year</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Semester</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="font-size: 11px; width: 140px;">Term State</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Grading Periods</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Grading Sheets</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="font-size: 11px; width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach ($closures as $term): ?>
                    <tr>
                        <td class="py-3 px-4 fw-bold text-dark">
                            <?= htmlspecialchars($term['school_year']) ?>
                        </td>
                        <td class="py-3 px-4">
                            <span class="fw-semibold text-dark"><?= htmlspecialchars($term['semester_name']) ?></span>
                            <?php if ($term['is_closed'] && !empty($term['closure_reason'])): ?>
                                <div class="text-muted small text-truncate" style="max-width: 260px;" title="<?= htmlspecialchars($term['closure_reason']) ?>">
                                    <i class="bi bi-info-circle me-1"></i><?= htmlspecialchars($term['closure_reason']) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <?php if ($term['is_closed']): ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1">
                                    <i class="bi bi-lock-fill me-1"></i> Closed
                                </span>
                            <?php elseif ($term['is_active']): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">
                                    <i class="bi bi-circle-fill me-1" style="font-size: 7px;"></i> Active
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1">
                                    Open
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-3">
                            <div class="d-flex flex-wrap gap-1">
                                <?php foreach ($term['periods'] as $gp): ?>
                                    <?php $isGpClosed = (bool)($gp['is_closed'] ?? false); ?>
                                    <span class="badge <?= $isGpClosed ? 'bg-light text-danger border border-danger-subtle' : 'bg-light text-success border border-success-subtle' ?>" 
                                          style="font-size: 10px;"
                                          title="<?= htmlspecialchars($gp['name']) ?>: <?= $isGpClosed ? 'Locked' : 'Open' ?>">
                                        <i class="bi <?= $isGpClosed ? 'bi-lock-fill' : 'bi-check' ?>"></i>
                                        <?= htmlspecialchars($gp['name']) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td class="py-3 px-3">
                            <div class="small fw-semibold text-dark">
                                <?= $term['stats']['approved_sheets_count'] ?? 0 ?> / <?= $term['stats']['sheets_count'] ?? 0 ?> approved
                            </div>
                            <?php if (($term['stats']['pending_sheets_count'] ?? 0) > 0): ?>
                                <div class="text-danger small" style="font-size: 11px;">
                                    <?= $term['stats']['pending_sheets_count'] ?> pending review
                                </div>
                            <?php else: ?>
                                <div class="text-success small" style="font-size: 11px;">
                                    All completed
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-4 text-end">
                            <?php if (!$term['is_closed']): ?>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1"
                                        onclick="openClosurePreview(<?= $term['id'] ?>)">
                                    <i class="bi bi-lock"></i> Close Term
                                </button>
                            <?php else: ?>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"
                                        onclick="openReopenModal(<?= $term['id'] ?>, '<?= htmlspecialchars($term['semester_name']) ?>')">
                                    <i class="bi bi-unlock"></i> Reopen
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal 1: Pre-Closure Audit Preview & Confirmation (Adopted from class-scheduling) -->
<div class="modal fade" id="closurePreviewModal" tabindex="-1" aria-labelledby="closurePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="closureConfirmForm" method="POST" action="">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-2 bg-danger-subtle text-danger rounded-circle">
                            <i class="bi bi-lock fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="closurePreviewModalLabel">Post & Close Academic Term</h5>
                            <span class="text-muted small" id="previewTermHeader">1st Semester, 2026-2027</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <!-- Loading Spinner -->
                    <div id="previewLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading audit preview...</span>
                        </div>
                        <p class="text-muted small mt-2 mb-0">Auditing grading sheets and term enrollment...</p>
                    </div>

                    <!-- Dynamic Content -->
                    <div id="previewContent" style="display: none;">
                        <!-- Warning Notice -->
                        <div class="alert alert-warning border-0 d-flex gap-3 mb-4" style="background-color: #fffbeb; color: #92400e;">
                            <i class="bi bi-exclamation-triangle-fill fs-5 mt-0.5 text-warning"></i>
                            <div>
                                <strong class="d-block mb-1">Permanent Record Lock Notice</strong>
                                <span class="small">
                                    Closing this term marks all student grades as official and locks all 4 exam periods (Prelim, Midterm, Semi-Final, Final). Instructors will no longer be permitted to modify scores or record attendance.
                                </span>
                            </div>
                        </div>

                        <!-- Stats Audit Summary -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="p-3 bg-light rounded text-center">
                                    <div class="text-muted small">Subjects</div>
                                    <div class="fs-4 fw-bold text-dark" id="statSubjects">0</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 bg-light rounded text-center">
                                    <div class="text-muted small">Enrolled Students</div>
                                    <div class="fs-4 fw-bold text-dark" id="statStudents">0</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 bg-light rounded text-center">
                                    <div class="text-muted small">Approved Sheets</div>
                                    <div class="fs-4 fw-bold text-success" id="statApproved">0</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 bg-light rounded text-center">
                                    <div class="text-muted small">Pending Sheets</div>
                                    <div class="fs-4 fw-bold text-danger" id="statPending">0</div>
                                </div>
                            </div>
                        </div>

                        <!-- Pending Grading Sheets Alert / List -->
                        <div id="pendingSheetsSection" class="mb-4" style="display: none;">
                            <div class="fw-semibold text-danger small mb-2 d-flex align-items-center gap-1">
                                <i class="bi bi-exclamation-circle-fill"></i> Unsubmitted / Incomplete Grading Sheets:
                            </div>
                            <div class="table-responsive border rounded" style="max-height: 160px; overflow-y: auto;">
                                <table class="table table-sm table-striped mb-0" style="font-size: 12px;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Subject</th>
                                            <th>Period</th>
                                            <th>Instructor</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pendingSheetsBody"></tbody>
                                </table>
                            </div>
                            <div class="form-text text-muted mt-1">
                                You may still close the term if authorized by the College Dean.
                            </div>
                        </div>

                        <!-- Reason Input -->
                        <div class="mb-3">
                            <label for="closure_reason" class="form-label fw-semibold">Closure / Posting Reason <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="closure_reason" 
                                   name="closure_reason" 
                                   required 
                                   placeholder="e.g., 1st Semester official grades approved and finalized by Dean">
                            <div class="form-text">Stored in audit trail and official institutional records.</div>
                        </div>

                        <!-- Next Semester Auto-Activation Option -->
                        <div class="form-check p-3 bg-light rounded" id="nextSemesterOption" style="display: none;">
                            <input class="form-check-input" type="checkbox" name="activate_next_semester" value="1" id="activateNextCheckbox" checked>
                            <label class="form-check-label small fw-semibold" for="activateNextCheckbox">
                                Automatically activate the next semester (<span id="nextSemesterName">2nd Semester</span>) upon closure
                            </label>
                            <div class="form-text text-muted small ms-0 mt-0.5">
                                Sets the upcoming semester as the new active working term for course enrollment and scheduling.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-3 px-4 border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger d-inline-flex align-items-center gap-1.5" id="btnConfirmClose">
                        <i class="bi bi-lock-fill"></i> Confirm & Close Term
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Audited Reopen Modal -->
<div class="modal fade" id="reopenModal" tabindex="-1" aria-labelledby="reopenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="reopenForm" method="POST" action="">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-2 bg-secondary-subtle text-dark rounded-circle">
                            <i class="bi bi-unlock fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="reopenModalLabel">Reopen Academic Term</h5>
                            <span class="text-muted small" id="reopenTermHeader">Term</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        Reopening unlocks the term for official grade corrections or administrative adjustments. This action is permanently logged to the audit trail.
                    </div>

                    <div class="mb-3">
                        <label for="reopen_reason" class="form-label fw-semibold">Administrative Reopening Justification <span class="text-danger">*</span></label>
                        <textarea class="form-control" 
                                  id="reopen_reason" 
                                  name="reopen_reason" 
                                  rows="3" 
                                  required 
                                  placeholder="Specify the reason (e.g., Dean-authorized correction for completion grade of Student 2026-0001)..."></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light py-3 px-4 border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-unlock-fill"></i> Reopen Term
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openClosurePreview(termId) {
    const modal = new bootstrap.Modal(document.getElementById('closurePreviewModal'));
    const loading = document.getElementById('previewLoading');
    const content = document.getElementById('previewContent');
    const form = document.getElementById('closureConfirmForm');

    form.action = '<?= url('/admin/academic-terms') ?>/' + termId + '/close';
    loading.style.display = 'block';
    content.style.display = 'none';
    modal.show();

    fetch('<?= url('/admin/academic-terms') ?>/' + termId + '/closure-preview')
        .then(res => res.json())
        .then(data => {
            loading.style.display = 'none';
            content.style.display = 'block';

            document.getElementById('previewTermHeader').textContent = data.term.semester_name + ', ' + data.term.school_year;
            document.getElementById('statSubjects').textContent = data.counts.subjects;
            document.getElementById('statStudents').textContent = data.counts.students;
            document.getElementById('statApproved').textContent = data.counts.completed_sheets;
            document.getElementById('statPending').textContent = data.counts.pending_sheets;
            document.getElementById('closure_reason').value = data.default_reason || '';

            // Pending sheets section
            const pendingSection = document.getElementById('pendingSheetsSection');
            const pendingBody = document.getElementById('pendingSheetsBody');
            pendingBody.innerHTML = '';

            if (data.pending_sheets && data.pending_sheets.length > 0) {
                pendingSection.style.display = 'block';
                data.pending_sheets.forEach(sheet => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="fw-semibold text-dark">${sheet.subject_code} - ${sheet.descriptive_title}</td>
                        <td>${sheet.period_name}</td>
                        <td>${sheet.faculty_name}</td>
                        <td><span class="badge bg-warning text-dark">${sheet.status}</span></td>
                    `;
                    pendingBody.appendChild(row);
                });
            } else {
                pendingSection.style.display = 'none';
            }

            // Next semester option
            const nextOption = document.getElementById('nextSemesterOption');
            if (data.next_term) {
                nextOption.style.display = 'block';
                document.getElementById('nextSemesterName').textContent = (data.next_term.semester == 2 ? '2nd Semester' : 'Summer');
            } else {
                nextOption.style.display = 'none';
            }
        })
        .catch(err => {
            loading.style.display = 'none';
            alert('Failed to load pre-closure audit preview.');
            modal.hide();
        });
}

function openReopenModal(termId, termName) {
    const modal = new bootstrap.Modal(document.getElementById('reopenModal'));
    const form = document.getElementById('reopenForm');
    form.action = '<?= url('/admin/academic-terms') ?>/' + termId + '/reopen';
    document.getElementById('reopenTermHeader').textContent = termName;
    document.getElementById('reopen_reason').value = '';
    modal.show();
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
