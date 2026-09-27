<?php
$pageTitle = $user['role'] === 'Student' ? 'Student Registration Record &mdash; Modification' : 'Official Personnel Profile &mdash; Modification';
$subtitle = 'Official institutional admission record amendment for ' . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . '.';
$headerActions = '<div class="d-flex align-items-center gap-2">
    <a href="' . url('/admin/users') . '" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5"><i class="bi bi-arrow-left"></i> Return to Users Directory</a>
</div>';
ob_start();
?>

<!-- Document Form Container -->
<div class="card shadow-sm border-0 mb-5 mx-auto" style="border: 1px solid #cbd5e1 !important; border-top: 4px solid #1e3a8a !important; border-radius: 8px; max-width: 860px; background: #ffffff;">
    
    <!-- Formal School Form Header -->
    <div class="text-center py-4 px-4 border-bottom bg-white">
        <div class="text-uppercase fw-bold mb-0" style="color: #1e3a8a; font-size: 13.5px; letter-spacing: 0.08em;">
            Golden West Colleges, Inc.
        </div>
        <div class="text-secondary small fw-medium text-uppercase mb-1" style="font-size: 11px; letter-spacing: 0.06em;">
            Office of the College Registrar &bull; Admissions and Student Records Division
        </div>
        <h1 class="h5 fw-bold text-dark text-uppercase mb-1" style="letter-spacing: 0.03em;">
            <?= $user['role'] === 'Student' ? 'Official Student Record Amendment' : 'Institutional Personnel Record Amendment' ?>
        </h1>
        <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 gap-md-3 text-muted mt-2" style="font-size: 11.5px;">
            <span><strong class="text-secondary">Document Ref:</strong> GWC-REG-AMD-01</span>
            <span class="d-none d-sm-inline">&bull;</span>
            <span><strong class="text-secondary">Account ID:</strong> <?= (int)$user['id'] ?></span>
            <span class="d-none d-sm-inline">&bull;</span>
            <span><strong class="text-secondary">Official Role:</strong> <span class="badge badge-<?= strtolower($user['role']) ?> px-2 py-0.5 text-xs"><?= htmlspecialchars($user['role']) ?></span></span>
        </div>
    </div>

    <form method="POST" action="<?= url('/admin/users/' . $user['id']) ?>" id="formalUserEditForm" autocomplete="off">
        <?= csrf_field() ?>

        <div class="card-body p-4 p-md-5">

            <!-- ================= PERSONAL & IDENTITY ================= -->
            <div class="formal-section mb-4 pb-3">
                <div class="mb-3 pb-2 border-bottom">
                    <div class="fw-bold text-uppercase" style="font-size: 11.5px; letter-spacing: 0.06em; color: #1e3a8a;">
                        <?= $user['role'] === 'Student' ? 'Student Personal Identification' : 'Personal Identification &amp; Credentials' ?>
                    </div>
                    <div class="text-muted" style="font-size: 12px;">Official demographic information and institutional login identity.</div>
                </div>

                <?php if ($user['role'] === 'Student'): ?>
                    <!-- Student Identification Number -->
                    <div class="mb-3">
                        <label for="student_number" class="form-label fw-semibold" style="font-size: 13px;">
                            Student ID Number <span class="text-muted fw-normal small">(Optional / Pending Official Issuance)</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="student_number" 
                               name="student_number" 
                               placeholder="e.g., 2026-0001" 
                               value="<?= htmlspecialchars($user['student_number'] ?? '') ?>"
                               maxlength="50">
                        <div class="form-text text-muted" style="font-size: 12px;">
                            Official registrar-assigned identification code. Leave blank if student ID issuance is currently pending at Admissions.
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Full Legal Name -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label fw-semibold" style="font-size: 13px;">
                            First Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="first_name" 
                               name="first_name" 
                               value="<?= htmlspecialchars($user['first_name']) ?>" 
                               required 
                               autocomplete="given-name">
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label fw-semibold" style="font-size: 13px;">
                            Last Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="last_name" 
                               name="last_name" 
                               value="<?= htmlspecialchars($user['last_name']) ?>" 
                               required 
                               autocomplete="family-name">
                    </div>
                </div>

                <!-- Institutional Email Address (Readonly) -->
                <div class="mb-1">
                    <label for="email_display" class="form-label fw-semibold" style="font-size: 13px;">
                        Email Address
                    </label>
                    <input type="email" class="form-control bg-light" id="email_display" value="<?= htmlspecialchars($user['email']) ?>" readonly disabled>
                    <div class="form-text text-muted" style="font-size: 12px;">
                        Permanent institutional account identifier (cannot be altered after authentication issuance).
                    </div>
                </div>
            </div>

            <?php if ($user['role'] === 'Student'): ?>
                <!-- ================= ACADEMIC ADMISSION & PLACEMENT ================= -->
                <div class="formal-section mb-4 pb-2">
                    <div class="mb-3 pb-2 border-bottom">
                        <div class="fw-bold text-uppercase" style="font-size: 11.5px; letter-spacing: 0.06em; color: #1e3a8a;">
                            Academic Admission &amp; Enrollment Classification
                        </div>
                        <div class="text-muted" style="font-size: 12px;">Official curriculum status, educational level, and designated class section block.</div>
                    </div>

                    <?php 
                    $currentStatus = $_POST['student_status'] ?? $user['student']['status'] ?? $user['student_detail']['status'] ?? 'Regular';
                    $currentYearLevel = (int)($_POST['year_level'] ?? $user['student']['year_level'] ?? $user['student_detail']['year_level'] ?? 1);
                    $selectedSetId = (string)($_POST['set_id'] ?? $user['student']['set_id'] ?? $user['student_detail']['set_id'] ?? '');
                    ?>

                    <!-- Enrollment Classification Selector -->
                    <div class="mb-3">
                        <label class="form-label d-block fw-semibold" style="font-size: 13px;">
                            Enrollment Classification <span class="text-danger">*</span>
                        </label>
                        <input type="hidden" name="student_status" id="student_status" value="<?= htmlspecialchars($currentStatus) ?>">
                        
                        <div class="row g-2" role="tablist" id="enrollmentStatusTabs">
                            <!-- Regular Student Option -->
                            <div class="col-sm-6">
                                <button type="button" 
                                        class="btn w-100 p-3 text-start border formal-status-card <?= $currentStatus === 'Regular' ? 'active-formal-card' : 'bg-white' ?>" 
                                        id="status-regular-tab" 
                                        data-status="Regular"
                                        role="tab"
                                        aria-selected="<?= $currentStatus === 'Regular' ? 'true' : 'false' ?>">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="fw-bold text-dark" style="font-size: 13.5px;">Regular Student</span>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5" style="font-size: 11px;">Standard Load</span>
                                    </div>
                                    <div class="text-muted small" style="font-size: 12px; line-height: 1.45;">
                                        Student is enrolled under the full prescribed curriculum load and assigned to a dedicated class section cohort.
                                    </div>
                                </button>
                            </div>
                            <!-- Irregular Student Option -->
                            <div class="col-sm-6">
                                <button type="button" 
                                        class="btn w-100 p-3 text-start border formal-status-card <?= $currentStatus === 'Irregular' ? 'active-formal-card' : 'bg-white' ?>" 
                                        id="status-irregular-tab" 
                                        data-status="Irregular"
                                        role="tab"
                                        aria-selected="<?= $currentStatus === 'Irregular' ? 'true' : 'false' ?>">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="fw-bold text-dark" style="font-size: 13.5px;">Irregular Student</span>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-0.5" style="font-size: 11px;">Modular Schedule</span>
                                    </div>
                                    <div class="text-muted small" style="font-size: 12px; line-height: 1.45;">
                                        Student takes coursework across multiple section schedules. Subject sets are assigned per course upon term enrollment.
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Curriculum Year Level & Class Section -->
                    <div class="row g-3">
                        <div class="col-md-6" id="yearLevelWrapper">
                            <label for="year_level" class="form-label fw-semibold" style="font-size: 13px;">
                                Year Level <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="year_level" name="year_level" required>
                                <option value="1" <?= $currentYearLevel === 1 ? 'selected' : '' ?>>First Year (Freshman)</option>
                                <option value="2" <?= $currentYearLevel === 2 ? 'selected' : '' ?>>Second Year (Sophomore)</option>
                                <option value="3" <?= $currentYearLevel === 3 ? 'selected' : '' ?>>Third Year (Junior)</option>
                                <option value="4" <?= $currentYearLevel === 4 ? 'selected' : '' ?>>Fourth Year (Senior)</option>
                            </select>
                            <div class="form-text text-muted" id="yearLevelHelp" style="font-size: 12px;">
                                <?= $currentStatus === 'Regular' ? 'Filters available section cohorts matching the academic year.' : 'Official curriculum standing.' ?>
                            </div>
                        </div>

                        <!-- Regular only: Class Set -->
                        <div class="col-md-6 <?= $currentStatus === 'Irregular' ? 'd-none' : '' ?>" id="setSectionWrapper">
                            <label for="set_id" class="form-label fw-semibold" style="font-size: 13px;">
                                Assigned Class Section <span class="text-danger" id="setRequiredMarker">*</span>
                            </label>
                            <select class="form-select" id="set_id" name="set_id" <?= $currentStatus === 'Regular' ? 'required' : '' ?>>
                                <option value="">Select class section...</option>
                                <?php foreach ($sets ?? [] as $set): ?>
                                    <option value="<?= $set['id'] ?>" 
                                            data-year-level="<?= (int)($set['year_level'] ?? 1) ?>" 
                                            <?= ((string)$selectedSetId === (string)$set['id']) ? 'selected' : '' ?>>
                                        Section <?= htmlspecialchars($set['name']) ?> (Year <?= $set['year_level'] ?? 1 ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-muted" id="setHelpText" style="font-size: 12px;">
                                Active term curriculum cohort assignment.
                            </div>
                            <div id="noSetsAlert" class="alert alert-warning py-2 px-3 mt-2 mb-0 small d-none" style="font-size: 12px;">
                                <i class="bi bi-exclamation-triangle me-1"></i> No active section cohorts registered for Year <span id="noSetsYearSpan">1</span>.
                            </div>
                        </div>

                        <!-- Irregular student multiple-section notice -->
                        <div class="col-12 <?= $currentStatus === 'Irregular' ? '' : 'd-none' ?>" id="irregularNotice">
                            <div class="p-3 rounded-2 border text-muted small" style="background-color: #f8fafc !important; border-color: #cbd5e1 !important; font-size: 12.5px; line-height: 1.55;">
                                <div class="fw-bold text-dark mb-1">
                                    <i class="bi bi-info-circle text-primary me-1"></i> Registrar Advisory &mdash; Modular Course Load
                                </div>
                                Irregular students enroll across multiple course schedules. Class sections and subject schedules are assigned individually per course during academic term subject loading, so no single cohort block is assigned at initial registration.
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif (in_array($user['role'], ['Faculty', 'Dean'], true)): ?>
                <!-- ================= DEPARTMENT AFFILIATION ================= -->
                <div class="formal-section mb-4 pb-2">
                    <div class="mb-3 pb-2 border-bottom">
                        <div class="fw-bold text-uppercase" style="font-size: 11.5px; letter-spacing: 0.06em; color: #1e3a8a;">
                            Collegiate Department Affiliation
                        </div>
                        <div class="text-muted" style="font-size: 12px;">Official college division and academic department appointment.</div>
                    </div>

                    <div class="mb-1">
                        <label for="department_id" class="form-label fw-semibold" style="font-size: 13px;">
                            Academic College / Department <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="department_id" name="department_id" required>
                            <option value="">Select college or academic department...</option>
                            <?php
                            $currentDeptId = $user['faculty']['department_id'] ?? null;
                            foreach ($departments ?? [] as $dept):
                            ?>
                                <option value="<?= $dept['id'] ?>" <?= ((string)$currentDeptId === (string)$dept['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['name']) ?> (<?= htmlspecialchars($dept['code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-muted" style="font-size: 12px;">The collegiate division to which this academic officer is officially commissioned.</div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ================= REGISTRAR CERTIFICATION ================= -->
            <div class="p-3 rounded-2 border bg-light bg-opacity-50 mt-3" style="border-color: #cbd5e1 !important;">
                <div class="d-flex align-items-start gap-2.5">
                    <i class="bi bi-shield-check text-primary mt-0.5" style="font-size: 16px;"></i>
                    <div style="font-size: 12px; line-height: 1.5; color: #475569;">
                        <strong class="text-dark">Registrar Official Certification:</strong>
                        Changes made to this student demographic profile or academic cohort status are immediately committed to the institutional registry and active session logs.
                    </div>
                </div>
            </div>

        </div>

        <!-- Document Footer Actions -->
        <div class="card-footer bg-light py-3 px-4 border-top d-flex justify-content-between align-items-center" style="border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
            <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary">
                Cancel and Return
            </a>
            <div class="d-flex align-items-center gap-2">
                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3.5 fw-semibold" style="background-color: #1e3a8a; border-color: #1e3a8a;">
                    <i class="bi bi-check2-circle"></i> Commit Official Changes
                </button>
            </div>
        </div>
    </form>
</div>

<style>
.formal-status-card {
    border-radius: 8px;
    border-color: #cbd5e1 !important;
    background-color: #ffffff !important;
    transition: all 0.15s ease-in-out;
    cursor: pointer;
}
.formal-status-card:hover {
    border-color: #94a3b8 !important;
    background-color: #f8fafc !important;
}
.formal-status-card.active-formal-card {
    background-color: #eff6ff !important;
    border-color: #1e3a8a !important;
    box-shadow: 0 0 0 1.5px #1e3a8a, 0 1px 3px 0 rgba(0, 0, 0, 0.05);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var studentStatusInput = document.getElementById('student_status');
    var statusRegularBtn = document.getElementById('status-regular-tab');
    var statusIrregularBtn = document.getElementById('status-irregular-tab');
    var yearLevelSelect = document.getElementById('year_level');
    var yearLevelHelp = document.getElementById('yearLevelHelp');
    var setSectionWrapper = document.getElementById('setSectionWrapper');
    var setSelect = document.getElementById('set_id');
    var irregularNotice = document.getElementById('irregularNotice');
    var noSetsAlert = document.getElementById('noSetsAlert');
    var noSetsYearSpan = document.getElementById('noSetsYearSpan');

    if (!studentStatusInput || !yearLevelSelect || !setSelect) {
        return;
    }

    function setEnrollmentStatus(status) {
        studentStatusInput.value = status;
        if (status === 'Regular') {
            statusRegularBtn.classList.add('active-formal-card');
            statusRegularBtn.setAttribute('aria-selected', 'true');
            statusIrregularBtn.classList.remove('active-formal-card');
            statusIrregularBtn.setAttribute('aria-selected', 'false');

            if (setSectionWrapper) setSectionWrapper.classList.remove('d-none');
            if (irregularNotice) irregularNotice.classList.add('d-none');
            if (yearLevelHelp) yearLevelHelp.textContent = 'Filters available section cohorts matching the academic year.';
            setSelect.required = true;
            filterSetsByYearLevel(false);
        } else {
            statusIrregularBtn.classList.add('active-formal-card');
            statusIrregularBtn.setAttribute('aria-selected', 'true');
            statusRegularBtn.classList.remove('active-formal-card');
            statusRegularBtn.setAttribute('aria-selected', 'false');

            if (setSectionWrapper) setSectionWrapper.classList.add('d-none');
            if (irregularNotice) irregularNotice.classList.remove('d-none');
            if (yearLevelHelp) yearLevelHelp.textContent = 'Official curriculum standing.';
            setSelect.value = '';
            setSelect.required = false;
        }
    }

    if (statusRegularBtn) {
        statusRegularBtn.addEventListener('click', function() {
            setEnrollmentStatus('Regular');
        });
    }

    if (statusIrregularBtn) {
        statusIrregularBtn.addEventListener('click', function() {
            setEnrollmentStatus('Irregular');
        });
    }

    function filterSetsByYearLevel(clearValue) {
        var selectedYear = yearLevelSelect.value;
        var availableCount = 0;
        var isCurrentMatch = false;

        if (clearValue) {
            setSelect.value = '';
        }

        for (var i = 0; i < setSelect.options.length; i++) {
            var opt = setSelect.options[i];
            var optYear = opt.getAttribute('data-year-level');
            if (!optYear) {
                opt.hidden = false;
                opt.disabled = false;
                continue;
            }

            if (optYear === selectedYear) {
                opt.hidden = false;
                opt.disabled = false;
                availableCount++;
                if (opt.value === setSelect.value) {
                    isCurrentMatch = true;
                }
            } else {
                opt.hidden = true;
                opt.disabled = true;
            }
        }

        if (setSelect.value && !isCurrentMatch) {
            setSelect.value = '';
        }

        if (noSetsAlert) {
            if (availableCount === 0) {
                if (noSetsYearSpan) noSetsYearSpan.textContent = selectedYear;
                noSetsAlert.classList.remove('d-none');
            } else {
                noSetsAlert.classList.add('d-none');
            }
        }
    }

    yearLevelSelect.addEventListener('change', function() {
        filterSetsByYearLevel(true);
    });

    setSelect.addEventListener('change', function() {
        var selectedOpt = setSelect.options[setSelect.selectedIndex];
        if (selectedOpt && selectedOpt.getAttribute('data-year-level')) {
            var setYear = selectedOpt.getAttribute('data-year-level');
            if (yearLevelSelect.value !== setYear) {
                yearLevelSelect.value = setYear;
                filterSetsByYearLevel(false);
            }
        }
    });

    setEnrollmentStatus(studentStatusInput.value || 'Regular');
    filterSetsByYearLevel(false);
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
