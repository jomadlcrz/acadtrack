<?php
$activeRole = $selectedRole ?? $_POST['role'] ?? $_GET['role'] ?? 'Student';
$pageTitle = $activeRole === 'Student' ? 'Register Student' : 'Add ' . htmlspecialchars($activeRole) . ' Account';
$subtitle = $activeRole === 'Student' 
    ? 'Create a new student profile and assign academic classification.' 
    : 'Create an authorized personnel account for faculty or administrative staff.';
$headerActions = '<div class="d-flex align-items-center gap-2">
    <a href="' . url('/admin/users/import-template') . '" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5"><i class="bi bi-download"></i> Download Template</a>
    <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#importExcelModal"><i class="bi bi-file-earmark-excel"></i> Import Spreadsheet</button>
    <a href="' . url('/admin/users') . '" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5"><i class="bi bi-arrow-left"></i> Back to list</a>
</div>';
ob_start();
?>

<!-- Document Form Container -->
<div class="card shadow-sm border-0 mb-5 mx-auto" style="border: 1px solid #e2e8f0 !important; border-radius: 12px; max-width: 820px; background: #ffffff;">
    
    <div class="card-header bg-white px-4 py-3 border-bottom d-flex align-items-center justify-content-between" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
        <div class="d-flex align-items-center gap-2">
            <span class="badge badge-<?= strtolower($activeRole) ?> px-2.5 py-1 text-xs fw-semibold"><?= htmlspecialchars($activeRole) ?></span>
            <span class="text-secondary small">&bull;</span>
            <span class="text-muted small">Academic Term: <strong class="text-dark"><?= htmlspecialchars($academicTerm['name'] ?? 'Active Term') ?></strong></span>
        </div>
        <span class="text-muted small"><span class="text-danger">*</span> Required fields</span>
    </div>

    <form method="POST" action="<?= url('/admin/users') ?>" id="formalUserForm" autocomplete="off">
        <?= csrf_field() ?>
        <input type="hidden" name="role" value="<?= htmlspecialchars($activeRole) ?>">
        <input type="hidden" name="force_password_change" value="1">

        <div class="card-body p-4 p-md-5">

            <!-- Personal Information -->
            <div class="mb-4 pb-2">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <div class="d-flex align-items-center justify-content-center text-primary" style="width: 28px; height: 28px;">
                        <i class="bi bi-person text-primary" style="font-size: 15px;"></i>
                    </div>
                    <div>
                        <h6 class="fw-semibold text-dark mb-0" style="font-size: 14px;">Personal Information</h6>
                        <div class="text-muted" style="font-size: 12px;">Student identity and institutional contact details.</div>
                    </div>
                </div>

                <?php if ($activeRole === 'Student'): ?>
                    <!-- Student Identification Number -->
                    <div class="mb-3">
                        <label for="student_number" class="form-label fw-semibold text-dark" style="font-size: 13px;">
                            Student ID <span class="text-muted fw-normal small">(Optional)</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="student_number" 
                               name="student_number" 
                               placeholder="e.g. 2026-0001" 
                               value="<?= htmlspecialchars($_POST['student_number'] ?? '') ?>"
                               maxlength="50">
                        <div class="form-text text-muted" style="font-size: 12px;">
                            Leave blank if the student number has not yet been issued by Admissions.
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Full Legal Name -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label fw-semibold text-dark" style="font-size: 13px;">
                            First Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="first_name" 
                               name="first_name" 
                               placeholder="First name" 
                               value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" 
                               required 
                               autocomplete="given-name">
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label fw-semibold text-dark" style="font-size: 13px;">
                            Last Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="last_name" 
                               name="last_name" 
                               placeholder="Last name" 
                               value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" 
                               required 
                               autocomplete="family-name">
                    </div>
                </div>

                <!-- Email Address -->
                <div class="mb-1">
                    <label for="email" class="form-label fw-semibold text-dark" style="font-size: 13px;">
                        Email Address <span class="text-danger">*</span>
                    </label>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           placeholder="student@gwc.edu.ph" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                           required 
                           autocomplete="email">
                    <div class="form-text text-muted" style="font-size: 12px;">
                        Account activation instructions and portal credentials will be sent to this email.
                    </div>
                </div>
            </div>

            <?php if ($activeRole === 'Student'): ?>
                <!-- Academic Classification -->
                <div class="mt-4 mb-3 pt-3 border-top">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <div class="d-flex align-items-center justify-content-center text-primary" style="width: 28px; height: 28px;">
                            <i class="bi bi-mortarboard text-primary" style="font-size: 15px;"></i>
                        </div>
                        <div>
                            <h6 class="fw-semibold text-dark mb-0" style="font-size: 14px;">Academic Classification</h6>
                            <div class="text-muted" style="font-size: 12px;">Enrollment standing, curriculum year level, and cohort section placement.</div>
                        </div>
                    </div>

                    <?php 
                    $currentStatus = $_POST['student_status'] ?? $selectedStatus ?? 'Regular';
                    $currentYearLevel = (int)($_POST['year_level'] ?? $selectedYearLevel ?? 1);
                    $selectedSetId = (string)($_POST['set_id'] ?? '');
                    ?>

                    <!-- Enrollment Status Selector (Adopted from class-scheduling) -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark mb-2" style="font-size: 13px;">
                            Enrollment Status <span class="text-danger">*</span>
                        </label>
                        <input type="hidden" name="student_status" id="student_status" value="<?= htmlspecialchars($currentStatus) ?>">
                        
                        <div class="row g-3" role="tablist" id="enrollmentStatusTabs">
                            <!-- Regular Student Option -->
                            <div class="col-sm-6">
                                <button type="button" 
                                        class="btn w-100 p-3 text-start border formal-status-card <?= $currentStatus === 'Regular' ? 'active-formal-card' : '' ?>" 
                                        id="status-regular-tab" 
                                        data-status="Regular"
                                        role="tab"
                                        aria-selected="<?= $currentStatus === 'Regular' ? 'true' : 'false' ?>">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="status-icon-box rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" 
                                             id="iconBoxRegular"
                                             style="width: 36px; height: 36px; <?= $currentStatus === 'Regular' ? 'background-color: #1e3a8a; color: #ffffff;' : 'background-color: #f1f5f9; color: #64748b;' ?>">
                                            <i class="bi bi-mortarboard-fill" style="font-size: 17px;"></i>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <span class="fw-semibold text-dark" style="font-size: 14px;">Regular</span>
                                                <i class="bi bi-check-circle-fill text-primary status-check-icon <?= $currentStatus === 'Regular' ? '' : 'd-none' ?>" id="checkRegular" style="font-size: 15px;"></i>
                                            </div>
                                            <div class="text-muted small" style="font-size: 12px; line-height: 1.45;">
                                                Enrolls under standard curriculum load and assigned to a cohort section block.
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            </div>
                            <!-- Irregular Student Option -->
                            <div class="col-sm-6">
                                <button type="button" 
                                        class="btn w-100 p-3 text-start border formal-status-card <?= $currentStatus === 'Irregular' ? 'active-formal-card' : '' ?>" 
                                        id="status-irregular-tab" 
                                        data-status="Irregular"
                                        role="tab"
                                        aria-selected="<?= $currentStatus === 'Irregular' ? 'true' : 'false' ?>">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="status-icon-box rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" 
                                             id="iconBoxIrregular"
                                             style="width: 36px; height: 36px; <?= $currentStatus === 'Irregular' ? 'background-color: #1e3a8a; color: #ffffff;' : 'background-color: #f1f5f9; color: #64748b;' ?>">
                                            <i class="bi bi-shuffle" style="font-size: 17px;"></i>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <span class="fw-semibold text-dark" style="font-size: 14px;">Irregular</span>
                                                <i class="bi bi-check-circle-fill text-primary status-check-icon <?= $currentStatus === 'Irregular' ? '' : 'd-none' ?>" id="checkIrregular" style="font-size: 15px;"></i>
                                            </div>
                                            <div class="text-muted small" style="font-size: 12px; line-height: 1.45;">
                                                Custom schedule across sections. Subjects are loaded individually during enrollment.
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Year Level & Class Section -->
                    <div class="row g-3">
                        <div class="col-md-6" id="yearLevelWrapper">
                            <label for="year_level" class="form-label fw-semibold text-dark" style="font-size: 13px;">
                                Year level <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="year_level" name="year_level" required>
                                <option value="1" <?= $currentYearLevel === 1 ? 'selected' : '' ?>>1st Year</option>
                                <option value="2" <?= $currentYearLevel === 2 ? 'selected' : '' ?>>2nd Year</option>
                                <option value="3" <?= $currentYearLevel === 3 ? 'selected' : '' ?>>3rd Year</option>
                                <option value="4" <?= $currentYearLevel === 4 ? 'selected' : '' ?>>4th Year</option>
                            </select>
                            <div class="form-text text-muted" id="yearLevelHelp" style="font-size: 12px;">
                                <?= $currentStatus === 'Regular' ? 'Filters sections matching this academic year.' : 'Academic curriculum standing.' ?>
                            </div>
                        </div>

                        <!-- Regular only: Class Set -->
                        <div class="col-md-6 <?= $currentStatus === 'Irregular' ? 'd-none' : '' ?>" id="setSectionWrapper">
                            <label for="set_id" class="form-label fw-semibold text-dark" style="font-size: 13px;">
                                Assigned section <span class="text-danger" id="setRequiredMarker">*</span>
                            </label>
                            <select class="form-select" id="set_id" name="set_id" <?= $currentStatus === 'Regular' ? 'required' : '' ?>>
                                <option value="">Select section...</option>
                                <?php foreach ($sets ?? [] as $set): ?>
                                    <option value="<?= $set['id'] ?>" 
                                            data-year-level="<?= (int)($set['year_level'] ?? 1) ?>" 
                                            <?= ((string)$selectedSetId === (string)$set['id']) ? 'selected' : '' ?>>
                                        Section <?= htmlspecialchars($set['name']) ?> (Year <?= $set['year_level'] ?? 1 ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-muted" id="setHelpText" style="font-size: 12px;">
                                Cohort block assigned for the active term.
                            </div>
                            <div id="noSetsAlert" class="alert alert-warning py-2 px-3 mt-2 mb-0 small d-none" style="font-size: 12px;">
                                <i class="bi bi-exclamation-triangle me-1"></i> No active sections found for Year <span id="noSetsYearSpan">1</span>.
                            </div>
                        </div>

                        <!-- Irregular student multiple-section notice -->
                        <div class="col-12 <?= $currentStatus === 'Irregular' ? '' : 'd-none' ?>" id="irregularNotice">
                            <div class="p-3 rounded-2 border text-muted small" style="background-color: #f8fafc; border-color: #e2e8f0 !important; font-size: 12.5px; line-height: 1.55;">
                                <div class="fw-semibold text-dark mb-1">
                                    <i class="bi bi-info-circle text-primary me-1"></i> Modular Course Scheduling
                                </div>
                                Irregular students enroll across multiple course schedules. Section blocks are not assigned at initial registration and will be scheduled individually during subject loading.
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif (in_array($activeRole, ['Faculty', 'Dean'], true)): ?>
                <!-- Department Affiliation -->
                <div class="mt-4 mb-3 pt-3 border-top">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <div class="d-flex align-items-center justify-content-center text-primary" style="width: 28px; height: 28px;">
                            <i class="bi bi-building text-primary" style="font-size: 15px;"></i>
                        </div>
                        <div>
                            <h6 class="fw-semibold text-dark mb-0" style="font-size: 14px;">Department Affiliation</h6>
                            <div class="text-muted" style="font-size: 12px;">Academic department appointment.</div>
                        </div>
                    </div>

                    <div class="mb-1">
                        <label for="department_id" class="form-label fw-semibold text-dark" style="font-size: 13px;">
                            Department <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="department_id" name="department_id" required>
                            <option value="">Select department...</option>
                            <?php foreach ($departments ?? [] as $dept): ?>
                                <?php 
                                $deptCode = strtoupper(trim($dept['code'] ?? $dept['dept_abbrev'] ?? ''));
                                $isSelected = (isset($_POST['department_id']) && (string)$_POST['department_id'] === (string)$dept['id'])
                                           || (!isset($_POST['department_id']) && $deptCode === 'CITE');
                                ?>
                                <option value="<?= $dept['id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['name']) ?> (<?= htmlspecialchars($deptCode) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-muted" style="font-size: 12px;">The department this academic officer belongs to.</div>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <!-- Footer Actions -->
        <div class="card-footer bg-light py-3 px-4 border-top d-flex justify-content-between align-items-center" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
            <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 fw-semibold" style="background-color: #1e3a8a; border-color: #1e3a8a;">
                <i class="bi bi-person-plus-fill"></i> <?= $activeRole === 'Student' ? 'Register Student' : 'Create Account' ?>
            </button>
        </div>
    </form>
</div>

<!-- ================= MODAL: BATCH EXCEL / CSV IMPORT ================= -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow" style="border: 1px solid #cbd5e1 !important; border-radius: 10px;">
            <div class="modal-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="modal-title h6 fw-bold text-dark mb-0" id="importExcelModalLabel">
                        Batch Student Registration
                    </h5>
                    <small class="text-muted" style="font-size: 12px;">Upload a student roster spreadsheet (.xlsx, .xls, or .csv) to register multiple accounts.</small>
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
    border-radius: 10px;
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
    var iconBoxRegular = document.getElementById('iconBoxRegular');
    var iconBoxIrregular = document.getElementById('iconBoxIrregular');
    var checkRegular = document.getElementById('checkRegular');
    var checkIrregular = document.getElementById('checkIrregular');
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
                if (iconBoxRegular) {
                    iconBoxRegular.style.backgroundColor = '#1e3a8a';
                    iconBoxRegular.style.color = '#ffffff';
                }
                if (checkRegular) checkRegular.classList.remove('d-none');

                statusIrregularBtn.classList.remove('active-formal-card');
                statusIrregularBtn.setAttribute('aria-selected', 'false');
                if (iconBoxIrregular) {
                    iconBoxIrregular.style.backgroundColor = '#f1f5f9';
                    iconBoxIrregular.style.color = '#64748b';
                }
                if (checkIrregular) checkIrregular.classList.add('d-none');

                if (setSectionWrapper) setSectionWrapper.classList.remove('d-none');
                if (irregularNotice) irregularNotice.classList.add('d-none');
                if (yearLevelHelp) yearLevelHelp.textContent = 'Filters sections matching this academic year.';
                setSelect.required = true;
                filterSetsByYearLevel(false);
            } else {
                statusIrregularBtn.classList.add('active-formal-card');
                statusIrregularBtn.setAttribute('aria-selected', 'true');
                if (iconBoxIrregular) {
                    iconBoxIrregular.style.backgroundColor = '#1e3a8a';
                    iconBoxIrregular.style.color = '#ffffff';
                }
                if (checkIrregular) checkIrregular.classList.remove('d-none');

                statusRegularBtn.classList.remove('active-formal-card');
                statusRegularBtn.setAttribute('aria-selected', 'false');
                if (iconBoxRegular) {
                    iconBoxRegular.style.backgroundColor = '#f1f5f9';
                    iconBoxRegular.style.color = '#64748b';
                }
                if (checkRegular) checkRegular.classList.add('d-none');

                if (setSectionWrapper) setSectionWrapper.classList.add('d-none');
                if (irregularNotice) irregularNotice.classList.remove('d-none');
                if (yearLevelHelp) yearLevelHelp.textContent = 'Academic curriculum standing.';
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

        var ext = file.name.split('.').pop().toLowerCase();
        if (ext === 'csv') {
            var reader = new FileReader();
            reader.onload = function(e) {
                parseCsvText(e.target.result);
            };
            reader.readAsText(file);
        } else if (ext === 'xlsx' || ext === 'xls') {
            if (typeof XLSX === 'undefined') {
                statusAlert.className = 'alert alert-danger py-2 px-3 small mb-3';
                statusAlert.textContent = 'Spreadsheet parser library is unavailable. Please upload a .csv file instead.';
                return;
            }
            var reader = new FileReader();
            reader.onload = function(e) {
                try {
                    var data = new Uint8Array(e.target.result);
                    var workbook = XLSX.read(data, { type: 'array' });
                    var firstSheetName = workbook.SheetNames[0];
                    var worksheet = workbook.Sheets[firstSheetName];
                    var jsonRows = XLSX.utils.sheet_to_json(worksheet, { defval: '' });
                    parseJsonRows(jsonRows);
                } catch (err) {
                    statusAlert.className = 'alert alert-danger py-2 px-3 small mb-3';
                    statusAlert.textContent = 'Failed to parse Excel workbook: ' + err.message;
                }
            };
            reader.readAsArrayBuffer(file);
        } else {
            statusAlert.className = 'alert alert-danger py-2 px-3 small mb-3';
            statusAlert.textContent = 'Unsupported file format. Please upload a .xlsx, .xls, or .csv file.';
        }
    }

    function parseCsvText(text) {
        var lines = text.split(/\r\n|\n/).filter(function(l) { return l.trim().length > 0; });
        if (lines.length < 2) {
            statusAlert.className = 'alert alert-warning py-2 px-3 small mb-3';
            statusAlert.textContent = 'The uploaded CSV file does not contain any student data rows.';
            return;
        }

        var headerRow = parseCsvLine(lines[0]);
        var colMap = mapHeaders(headerRow);
        var rows = [];

        for (var i = 1; i < lines.length; i++) {
            var cols = parseCsvLine(lines[i]);
            if (cols.length === 0 || cols.every(function(c) { return !c.trim(); })) continue;

            rows.push(extractStudentFromCols(cols, colMap));
        }

        renderPreview(rows);
    }

    function parseJsonRows(rows) {
        if (!rows || rows.length === 0) {
            statusAlert.className = 'alert alert-warning py-2 px-3 small mb-3';
            statusAlert.textContent = 'The Excel sheet has no data rows.';
            return;
        }

        var headerKeys = Object.keys(rows[0]);
        var colMap = mapHeaders(headerKeys);
        var students = [];

        for (var i = 0; i < rows.length; i++) {
            var r = rows[i];
            var cols = headerKeys.map(function(k) { return r[k]; });
            students.push(extractStudentFromCols(cols, colMap));
        }

        renderPreview(students);
    }

    function parseCsvLine(text) {
        var p = '', row = [''], i = 0, r = 0, q = false;
        for (i = 0; i < text.length; i++) {
            var c = text.charAt(i);
            if (c === '"') {
                if (q && text.charAt(i + 1) === '"') {
                    row[r] += '"';
                    i++;
                } else {
                    q = !q;
                }
            } else if (c === ',' && !q) {
                r++;
                row[r] = '';
            } else {
                row[r] += c;
            }
        }
        return row;
    }

    function mapHeaders(headers) {
        var map = {
            student_number: -1,
            first_name: -1,
            last_name: -1,
            email: -1,
            student_status: -1,
            year_level: -1,
            section: -1
        };

        headers.forEach(function(h, idx) {
            var clean = h.toString().toLowerCase().trim().replace(/[^a-z0-9]/g, '');
            if (clean.includes('studentno') || clean.includes('studentid') || clean.includes('studentnumber')) map.student_number = idx;
            else if (clean.includes('firstname') || clean.includes('givenname')) map.first_name = idx;
            else if (clean.includes('lastname') || clean.includes('surname') || clean.includes('familyname')) map.last_name = idx;
            else if (clean.includes('email')) map.email = idx;
            else if (clean.includes('status') || clean.includes('classification')) map.student_status = idx;
            else if (clean.includes('year') || clean.includes('level')) map.year_level = idx;
            else if (clean.includes('section') || clean.includes('set') || clean.includes('cohort')) map.section = idx;
        });

        // Fallback positional if header match failed
        if (map.first_name === -1 && headers.length >= 2) map.first_name = 1;
        if (map.last_name === -1 && headers.length >= 3) map.last_name = 2;
        if (map.email === -1 && headers.length >= 4) map.email = 3;

        return map;
    }

    function extractStudentFromCols(cols, colMap) {
        var getVal = function(key, fallbackIdx) {
            var idx = colMap[key] !== -1 ? colMap[key] : fallbackIdx;
            return (cols[idx] !== undefined ? String(cols[idx]).trim() : '');
        };

        var studentNumber = getVal('student_number', 0);
        var firstName = getVal('first_name', 1);
        var lastName = getVal('last_name', 2);
        var email = getVal('email', 3);
        var status = getVal('student_status', 4);
        var yearLevel = getVal('year_level', 5);
        var section = getVal('section', 6);

        // Normalize status
        if (!status || status.toLowerCase().startsWith('reg')) {
            status = 'Regular';
        } else {
            status = 'Irregular';
        }

        // Normalize year level
        var yInt = parseInt(yearLevel, 10);
        if (isNaN(yInt) || yInt < 1 || yInt > 4) {
            yInt = 1;
        }

        return {
            student_number: studentNumber,
            first_name: firstName,
            last_name: lastName,
            email: email,
            student_status: status,
            year_level: yInt,
            section: status === 'Regular' ? section : ''
        };
    }

    function renderPreview(students) {
        parsedStudents = students;
        previewTableBody.innerHTML = '';

        var validCount = 0;
        var invalidCount = 0;

        students.forEach(function(s, idx) {
            var isValid = Boolean(s.first_name && s.last_name && s.email && s.email.includes('@'));
            if (isValid) validCount++;
            else invalidCount++;

            var tr = document.createElement('tr');
            tr.innerHTML = 
                '<td class="py-1.5 px-2.5 text-muted">' + (idx + 1) + '</td>' +
                '<td class="py-1.5 px-2.5 font-monospace">' + (s.student_number || '<span class="text-muted">-</span>') + '</td>' +
                '<td class="py-1.5 px-2.5 fw-medium">' + s.last_name + ', ' + s.first_name + '</td>' +
                '<td class="py-1.5 px-2.5">' + s.email + '</td>' +
                '<td class="py-1.5 px-2.5"><span class="badge ' + (s.student_status === 'Regular' ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning-emphasis') + '">' + s.student_status + '</span></td>' +
                '<td class="py-1.5 px-2.5">Yr ' + s.year_level + '</td>' +
                '<td class="py-1.5 px-2.5">' + (s.student_status === 'Regular' ? (s.section || 'Auto') : '<span class="text-muted">None (Irreg)</span>') + '</td>' +
                '<td class="py-1.5 px-2.5 text-center">' + 
                    (isValid 
                        ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5"><i class="bi bi-check-lg"></i> Ready</span>' 
                        : '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-1.5 py-0.5"><i class="bi bi-exclamation-triangle"></i> Invalid</span>') +
                '</td>';
            previewTableBody.appendChild(tr);
        });

        previewContainer.classList.remove('d-none');
        previewSummary.innerHTML = 'Found <strong>' + students.length + '</strong> student record(s) &bull; ' + validCount + ' ready to import, ' + invalidCount + ' invalid.';

        if (validCount > 0) {
            btnSubmitImport.disabled = false;
            previewBadge.className = 'badge bg-success-subtle text-success';
            previewBadge.textContent = validCount + ' Valid Records';
            statusAlert.className = 'alert alert-success py-2 px-3 small mb-3';
            statusAlert.innerHTML = '<i class="bi bi-check-circle me-1"></i> Spreadsheet parsed successfully. Click <strong>Register Verified Records</strong> to proceed.';
        } else {
            btnSubmitImport.disabled = true;
            previewBadge.className = 'badge bg-danger-subtle text-danger';
            previewBadge.textContent = 'No Valid Records';
            statusAlert.className = 'alert alert-danger py-2 px-3 small mb-3';
            statusAlert.textContent = 'No valid student records found. Ensure rows have First Name, Last Name, and a valid Email Address.';
        }
    }

    if (btnSubmitImport) {
        btnSubmitImport.addEventListener('click', function() {
            if (parsedStudents.length === 0) return;

            btnSubmitImport.disabled = true;
            btnSubmitImport.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Registering records...';

            fetch('<?= url("/admin/users/import") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    _csrf_token: '<?= csrf_token() ?>',
                    students: parsedStudents
                })
            })
            .then(function(res) {
                if (res.redirected) {
                    window.location.href = res.url;
                    return;
                }
                return res.json().catch(function() {
                    window.location.href = '<?= url("/admin/users") ?>';
                });
            })
            .then(function(data) {
                if (data && data.success) {
                    window.location.href = '<?= url("/admin/users") ?>';
                } else if (data && data.error) {
                    btnSubmitImport.disabled = false;
                    btnSubmitImport.innerHTML = '<i class="bi bi-cloud-arrow-up"></i> Register Verified Records';
                    statusAlert.className = 'alert alert-danger py-2 px-3 small mb-3';
                    statusAlert.textContent = data.error;
                }
            })
            .catch(function(err) {
                console.error(err);
                window.location.href = '<?= url("/admin/users") ?>';
            });
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
