<?php
$activeRole = $selectedRole ?? $_POST['role'] ?? $_GET['role'] ?? 'Student';
$pageTitle = $activeRole === 'Student' ? 'Student Registration Record' : 'Official User Registration';
$subtitle = 'Official institutional admission, credentialing, and academic placement registry.';
$headerActions = '<div class="d-flex align-items-center gap-2">
    <a href="' . url('/admin/users/import-template') . '" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5"><i class="bi bi-download"></i> Official Template</a>
    <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#importExcelModal"><i class="bi bi-file-earmark-excel"></i> Import Excel / CSV</button>
    <a href="' . url('/admin/users') . '" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5"><i class="bi bi-arrow-left"></i> Users Directory</a>
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
            <?= $activeRole === 'Student' ? 'Student Admission &amp; Registration Record' : 'Institutional Personnel Registration Form' ?>
        </h1>
        <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 gap-md-3 text-muted mt-2" style="font-size: 11.5px;">
            <span><strong class="text-secondary">Document Ref:</strong> GWC-REG-FORM-01</span>
            <span class="d-none d-sm-inline">&bull;</span>
            <span><strong class="text-secondary">Term:</strong> <?= htmlspecialchars($academicTerm['name'] ?? 'Active Academic Term') ?></span>
            <span class="d-none d-sm-inline">&bull;</span>
            <span><strong class="text-secondary">Account Role:</strong> <span class="badge badge-<?= strtolower($activeRole) ?> px-2 py-0.5 text-xs"><?= htmlspecialchars($activeRole) ?></span></span>
        </div>
    </div>

    <form method="POST" action="<?= url('/admin/users') ?>" id="formalUserForm" autocomplete="off">
        <?= csrf_field() ?>
        <input type="hidden" name="role" value="<?= htmlspecialchars($activeRole) ?>">
        <input type="hidden" name="force_password_change" value="1">

        <div class="card-body p-4 p-md-5">

            <!-- ================= PERSONAL & IDENTITY ================= -->
            <div class="formal-section mb-4 pb-3">
                <div class="mb-3 pb-2 border-bottom">
                    <div class="fw-bold text-uppercase" style="font-size: 11.5px; letter-spacing: 0.06em; color: #1e3a8a;">
                        <?= $activeRole === 'Student' ? 'Student Personal Identification' : 'Personal Identification &amp; Credentials' ?>
                    </div>
                    <div class="text-muted" style="font-size: 12px;">Official demographic data and authorized institutional account identifier.</div>
                </div>

                <?php if ($activeRole === 'Student'): ?>
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
                               value="<?= htmlspecialchars($_POST['student_number'] ?? '') ?>"
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
                               placeholder="e.g., Juan" 
                               value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" 
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
                               placeholder="e.g., Dela Cruz" 
                               value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" 
                               required 
                               autocomplete="family-name">
                    </div>
                </div>

                <!-- Email Address -->
                <div class="mb-1">
                    <label for="email" class="form-label fw-semibold" style="font-size: 13px;">
                        Email Address <span class="text-danger">*</span>
                    </label>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           placeholder="e.g., student.name@gwc.edu.ph" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                           required 
                           autocomplete="email">
                    <div class="form-text text-muted" style="font-size: 12px;">
                        Authorized institutional communication account. System-generated authentication credentials and temporary security password will be officially dispatched to this address.
                    </div>
                </div>
            </div>

            <?php if ($activeRole === 'Student'): ?>
                <!-- ================= ACADEMIC ADMISSION & PLACEMENT ================= -->
                <div class="formal-section mb-4 pb-2">
                    <div class="mb-3 pb-2 border-bottom">
                        <div class="fw-bold text-uppercase" style="font-size: 11.5px; letter-spacing: 0.06em; color: #1e3a8a;">
                            Academic Admission &amp; Enrollment Classification
                        </div>
                        <div class="text-muted" style="font-size: 12px;">Official curriculum status, educational level, and designated class section block.</div>
                    </div>

                    <?php 
                    $currentStatus = $_POST['student_status'] ?? $selectedStatus ?? 'Regular';
                    $currentYearLevel = (int)($_POST['year_level'] ?? $selectedYearLevel ?? 1);
                    $selectedSetId = (string)($_POST['set_id'] ?? '');
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

            <?php elseif (in_array($activeRole, ['Faculty', 'Dean'], true)): ?>
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
                            <?php foreach ($departments ?? [] as $dept): ?>
                                <option value="<?= $dept['id'] ?>" <?= ((string)($_POST['department_id'] ?? '') === (string)$dept['id']) ? 'selected' : '' ?>>
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
                        By submitting this registration, the administrator/registrar certifies that the student demographic data and academic placement indicated above have been verified in compliance with Golden West Colleges institutional standards.
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
                    <i class="bi bi-check2-circle"></i> Register Official <?= htmlspecialchars($activeRole) ?> Record
                </button>
            </div>
        </div>
    </form>
</div>

<!-- ================= MODAL: BATCH EXCEL / CSV IMPORT ================= -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow" style="border: 1px solid #cbd5e1 !important; border-radius: 8px;">
            <div class="modal-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="modal-title h6 fw-bold text-dark mb-0" id="importExcelModalLabel">
                        Batch Student Registration &mdash; Excel / CSV Import
                    </h5>
                    <small class="text-muted" style="font-size: 12px;">Upload official student roster spreadsheet (.xlsx, .xls, or .csv)</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Step 1: Template and File Picker -->
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 pb-3 border-bottom">
                    <div>
                        <div class="fw-semibold text-dark small">Spreadsheet Template Format</div>
                        <div class="text-muted" style="font-size: 12px;">Required columns: Student Number, First Name, Last Name, Email, Enrollment Status, Year Level, Section</div>
                    </div>
                    <a href="<?= url('/admin/users/import-template') ?>" class="btn btn-outline-secondary btn-sm text-nowrap d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-download"></i> Download Template (.csv)
                    </a>
                </div>

                <!-- Dropzone / File Selector -->
                <div id="importDropzone" class="border border-2 border-dashed rounded-3 p-4 text-center mb-3" style="border-color: #cbd5e1 !important; background-color: #f8fafc; cursor: pointer; transition: all 0.2s ease;">
                    <i class="bi bi-file-earmark-excel text-primary d-block mb-2" style="font-size: 32px;"></i>
                    <div class="fw-semibold text-dark mb-1">Click to select or drag and drop your spreadsheet here</div>
                    <div class="text-muted small">Supports Excel (.xlsx, .xls) and Comma-Separated Values (.csv)</div>
                    <input type="file" id="excelFileInput" accept=".xlsx,.xls,.csv" class="d-none">
                </div>

                <!-- Progress / Error Banner -->
                <div id="importStatusAlert" class="alert d-none py-2 px-3 small mb-3"></div>

                <!-- Step 2: Parsed Rows Preview Table -->
                <div id="previewContainer" class="d-none">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="fw-semibold text-dark small" id="previewSummary">
                            Verification Preview: 0 records found
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary" id="previewBadge">Ready to import</span>
                    </div>

                    <div class="table-responsive border rounded-2" style="max-height: 280px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12px;">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th class="py-2 px-2.5">#</th>
                                    <th class="py-2 px-2.5">Student ID</th>
                                    <th class="py-2 px-2.5">Full Name</th>
                                    <th class="py-2 px-2.5">Email Address</th>
                                    <th class="py-2 px-2.5">Classification</th>
                                    <th class="py-2 px-2.5">Year</th>
                                    <th class="py-2 px-2.5">Section</th>
                                    <th class="py-2 px-2.5 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="previewTableBody">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light py-2.5 px-4 border-top d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="btnSubmitImport" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5" disabled style="background-color: #1e3a8a; border-color: #1e3a8a;">
                    <i class="bi bi-cloud-arrow-up"></i> Register Verified Records
                </button>
            </div>
        </div>
    </div>
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

<!-- Load SheetJS for local Excel parsing -->
<script src="<?= url('/assets/js/xlsx.full.min.js') ?>"></script>

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

    if (studentStatusInput && yearLevelSelect && setSelect) {
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
    }

    // ================= EXCEL / SPREADSHEET IMPORT LOGIC =================
    var dropzone = document.getElementById('importDropzone');
    var fileInput = document.getElementById('excelFileInput');
    var previewContainer = document.getElementById('previewContainer');
    var previewTableBody = document.getElementById('previewTableBody');
    var previewSummary = document.getElementById('previewSummary');
    var previewBadge = document.getElementById('previewBadge');
    var btnSubmitImport = document.getElementById('btnSubmitImport');
    var statusAlert = document.getElementById('importStatusAlert');
    var parsedStudents = [];

    if (dropzone && fileInput) {
        dropzone.addEventListener('click', function() {
            fileInput.click();
        });

        dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropzone.style.borderColor = '#1e3a8a';
            dropzone.style.backgroundColor = '#eff6ff';
        });

        dropzone.addEventListener('dragleave', function() {
            dropzone.style.borderColor = '#cbd5e1';
            dropzone.style.backgroundColor = '#f8fafc';
        });

        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropzone.style.borderColor = '#cbd5e1';
            dropzone.style.backgroundColor = '#f8fafc';
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                processFile(e.dataTransfer.files[0]);
            }
        });

        fileInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                processFile(e.target.files[0]);
            }
        });
    }

    function processFile(file) {
        statusAlert.className = 'alert alert-info py-2 px-3 small mb-3';
        statusAlert.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Processing file: ' + file.name + '...';
        statusAlert.classList.remove('d-none');

        var reader = new FileReader();
        reader.onload = function(e) {
            try {
                var data = new Uint8Array(e.target.result);
                var workbook = XLSX.read(data, { type: 'array' });
                var firstSheetName = workbook.SheetNames[0];
                var worksheet = workbook.Sheets[firstSheetName];
                var rawRows = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

                if (!rawRows || rawRows.length < 2) {
                    showError('Spreadsheet contains no data rows.');
                    return;
                }

                // Map header columns
                var headerRow = rawRows[0].map(function(h) { return String(h || '').trim().toLowerCase(); });
                var colMap = {
                    studentNumber: findCol(headerRow, ['student number', 'student id', 'id', 'student_number']),
                    firstName: findCol(headerRow, ['first name', 'given name', 'firstname', 'first_name']),
                    lastName: findCol(headerRow, ['last name', 'surname', 'lastname', 'family name', 'last_name']),
                    email: findCol(headerRow, ['email', 'email address', 'e-mail']),
                    status: findCol(headerRow, ['enrollment status', 'status', 'classification']),
                    yearLevel: findCol(headerRow, ['year level', 'year', 'year_level', 'level']),
                    section: findCol(headerRow, ['section', 'class section', 'set', 'class set'])
                };

                parsedStudents = [];
                var validCount = 0;
                var errorCount = 0;

                for (var r = 1; r < rawRows.length; r++) {
                    var row = rawRows[r];
                    if (!row || row.length === 0 || row.every(function(cell) { return cell === '' || cell == null; })) {
                        continue;
                    }

                    var student = {
                        student_number: colMap.studentNumber !== -1 ? String(row[colMap.studentNumber] || '').trim() : '',
                        first_name: colMap.firstName !== -1 ? String(row[colMap.firstName] || '').trim() : '',
                        last_name: colMap.lastName !== -1 ? String(row[colMap.lastName] || '').trim() : '',
                        email: colMap.email !== -1 ? String(row[colMap.email] || '').trim() : '',
                        student_status: colMap.status !== -1 ? String(row[colMap.status] || 'Regular').trim() : 'Regular',
                        year_level: colMap.yearLevel !== -1 ? String(row[colMap.yearLevel] || '1').trim() : '1',
                        section: colMap.section !== -1 ? String(row[colMap.section] || '').trim() : ''
                    };

                    // Normalization
                    var normStatus = student.student_status.toLowerCase();
                    student.student_status = (normStatus.indexOf('irreg') !== -1) ? 'Irregular' : 'Regular';
                    var numYear = parseInt(student.year_level, 10);
                    student.year_level = (isNaN(numYear) || numYear < 1 || numYear > 4) ? 1 : numYear;

                    var errors = [];
                    if (!student.first_name) errors.push('Missing first name');
                    if (!student.last_name) errors.push('Missing last name');
                    if (!student.email || student.email.indexOf('@') === -1) errors.push('Invalid email');

                    student._valid = errors.length === 0;
                    student._errors = errors;
                    if (student._valid) validCount++; else errorCount++;

                    parsedStudents.push(student);
                }

                if (parsedStudents.length === 0) {
                    showError('No valid student rows found in file.');
                    return;
                }

                renderPreview(parsedStudents, validCount, errorCount);
            } catch (err) {
                showError('Failed to parse spreadsheet: ' + err.message);
            }
        };
        reader.readAsArrayBuffer(file);
    }

    function findCol(headers, aliases) {
        for (var i = 0; i < headers.length; i++) {
            var h = headers[i];
            for (var a = 0; a < aliases.length; a++) {
                if (h === aliases[a] || h.indexOf(aliases[a]) !== -1) {
                    return i;
                }
            }
        }
        return -1;
    }

    function renderPreview(students, validCount, errorCount) {
        previewTableBody.innerHTML = '';
        students.forEach(function(s, idx) {
            var tr = document.createElement('tr');
            tr.className = s._valid ? '' : 'table-danger';
            tr.innerHTML = 
                '<td class="py-1.5 px-2.5 text-muted">' + (idx + 1) + '</td>' +
                '<td class="py-1.5 px-2.5 font-monospace">' + (s.student_number || '<span class="text-muted">—</span>') + '</td>' +
                '<td class="py-1.5 px-2.5 fw-medium">' + s.last_name + ', ' + s.first_name + '</td>' +
                '<td class="py-1.5 px-2.5">' + s.email + '</td>' +
                '<td class="py-1.5 px-2.5"><span class="badge ' + (s.student_status === 'Regular' ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning-emphasis') + '">' + s.student_status + '</span></td>' +
                '<td class="py-1.5 px-2.5">Yr ' + s.year_level + '</td>' +
                '<td class="py-1.5 px-2.5">' + (s.student_status === 'Regular' ? (s.section || 'Auto') : '<span class="text-muted">None (Irreg)</span>') + '</td>' +
                '<td class="py-1.5 px-2.5 text-center">' + 
                    (s._valid 
                        ? '<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check"></i> Ready</span>' 
                        : '<span class="badge bg-danger-subtle text-danger border border-danger-subtle" title="' + s._errors.join(', ') + '"><i class="bi bi-x"></i> Invalid</span>') + 
                '</td>';
            previewTableBody.appendChild(tr);
        });

        previewSummary.textContent = 'Found ' + students.length + ' records (' + validCount + ' valid, ' + errorCount + ' with issues)';
        previewBadge.textContent = validCount > 0 ? validCount + ' ready to register' : 'No valid records';
        previewBadge.className = 'badge ' + (validCount > 0 ? 'bg-primary text-white' : 'bg-danger text-white');

        statusAlert.className = 'alert alert-success py-2 px-3 small mb-3';
        statusAlert.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Parsed ' + students.length + ' student records. Review the preview below before submitting.';

        previewContainer.classList.remove('d-none');
        btnSubmitImport.disabled = (validCount === 0);
        btnSubmitImport.innerHTML = '<i class="bi bi-cloud-arrow-up"></i> Register ' + validCount + ' Verified Students';
    }

    function showError(msg) {
        statusAlert.className = 'alert alert-danger py-2 px-3 small mb-3';
        statusAlert.innerHTML = '<i class="bi bi-exclamation-octagon me-1"></i> ' + msg;
        statusAlert.classList.remove('d-none');
        btnSubmitImport.disabled = true;
    }

    if (btnSubmitImport) {
        btnSubmitImport.addEventListener('click', function() {
            var validToSubmit = parsedStudents.filter(function(s) { return s._valid; });
            if (validToSubmit.length === 0) return;

            btnSubmitImport.disabled = true;
            btnSubmitImport.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Submitting Registration...';

            var csrfToken = document.querySelector('input[name="_token"]')?.value || '';

            fetch('<?= url("/admin/users/import") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({
                    _token: csrfToken,
                    students: validToSubmit
                })
            })
            .then(function(res) {
                return res.json().then(function(data) {
                    return { ok: res.ok, data: data };
                });
            })
            .then(function(result) {
                if (result.ok && result.data.success) {
                    statusAlert.className = 'alert alert-success py-2 px-3 small mb-3';
                    statusAlert.innerHTML = '<i class="bi bi-check2-circle me-1"></i> ' + result.data.message;
                    setTimeout(function() {
                        window.location.href = '<?= url("/admin/users") ?>';
                    }, 1200);
                } else {
                    var errorMsg = result.data.message || 'Import failed.';
                    if (result.data.errors && result.data.errors.length > 0) {
                        errorMsg += '<ul class="mb-0 mt-1 ps-3">' + result.data.errors.map(function(e) {
                            return '<li>' + (e.name ? e.name + ': ' : '') + e.message + '</li>';
                        }).join('') + '</ul>';
                    }
                    showError(errorMsg);
                    btnSubmitImport.disabled = false;
                    btnSubmitImport.innerHTML = '<i class="bi bi-cloud-arrow-up"></i> Retry Registration';
                }
            })
            .catch(function(err) {
                showError('Network error: ' + err.message);
                btnSubmitImport.disabled = false;
                btnSubmitImport.innerHTML = '<i class="bi bi-cloud-arrow-up"></i> Retry Registration';
            });
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
