<?php
$pageTitle = $user['role'] === 'Student' ? 'Edit Student Profile' : 'Edit ' . htmlspecialchars($user['role']) . ' Profile';
$subtitle = 'Update profile information and academic classification for ' . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . '.';
$headerActions = '<div class="d-flex align-items-center gap-2">
    <a href="' . url('/admin/users') . '" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5"><i class="bi bi-arrow-left"></i> Back to Users</a>
</div>';
ob_start();
?>

<!-- Document Form Container -->
<div class="card shadow-sm border-0 mb-5 mx-auto" style="border: 1px solid #e2e8f0 !important; border-radius: 12px; max-width: 820px; background: #ffffff;">
    
    <div class="card-header bg-white px-4 py-3 border-bottom d-flex align-items-center justify-content-between" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
        <div class="d-flex align-items-center gap-2">
            <span class="badge badge-<?= strtolower($user['role']) ?> px-2.5 py-1 text-xs fw-semibold"><?= htmlspecialchars($user['role']) ?></span>
            <span class="text-secondary small">&bull;</span>
            <span class="text-muted small">Account ID: <strong class="text-dark">#<?= (int)$user['id'] ?></strong></span>
        </div>
        <span class="text-muted small"><span class="text-danger">*</span> Required fields</span>
    </div>

    <form method="POST" action="<?= url('/admin/users/' . $user['id']) ?>" id="formalUserEditForm" autocomplete="off">
        <?= csrf_field() ?>

        <div class="card-body p-4 p-md-5">

            <!-- Personal Information -->
            <div class="mb-4 pb-2">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <div class="d-flex align-items-center justify-content-center rounded-2 text-primary" style="width: 28px; height: 28px; background-color: #eff6ff;">
                        <i class="bi bi-person text-primary" style="font-size: 15px;"></i>
                    </div>
                    <div>
                        <h6 class="fw-semibold text-dark mb-0" style="font-size: 14px;">Personal Information</h6>
                        <div class="text-muted" style="font-size: 12px;">Student identity and authorized account details.</div>
                    </div>
                </div>

                <?php if ($user['role'] === 'Student'): ?>
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
                               value="<?= htmlspecialchars($user['student_number'] ?? '') ?>"
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
                               value="<?= htmlspecialchars($user['first_name']) ?>" 
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
                               value="<?= htmlspecialchars($user['last_name']) ?>" 
                               required 
                               autocomplete="family-name">
                    </div>
                </div>

                <!-- Email Address (Readonly) -->
                <div class="mb-1">
                    <label for="email_display" class="form-label fw-semibold text-dark" style="font-size: 13px;">
                        Email Address
                    </label>
                    <input type="email" class="form-control bg-light" id="email_display" value="<?= htmlspecialchars($user['email']) ?>" readonly disabled>
                    <div class="form-text text-muted" style="font-size: 12px;">
                        Permanent login identifier (cannot be modified after creation).
                    </div>
                </div>
            </div>

            <?php if ($user['role'] === 'Student'): ?>
                <!-- Academic Classification -->
                <div class="mt-4 mb-3 pt-3 border-top">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <div class="d-flex align-items-center justify-content-center rounded-2 text-primary" style="width: 28px; height: 28px; background-color: #eff6ff;">
                            <i class="bi bi-mortarboard text-primary" style="font-size: 15px;"></i>
                        </div>
                        <div>
                            <h6 class="fw-semibold text-dark mb-0" style="font-size: 14px;">Academic Classification</h6>
                            <div class="text-muted" style="font-size: 12px;">Enrollment standing, curriculum year level, and cohort section placement.</div>
                        </div>
                    </div>

                    <?php 
                    $currentStatus = $_POST['student_status'] ?? $user['student']['status'] ?? $user['student_detail']['status'] ?? 'Regular';
                    $currentYearLevel = (int)($_POST['year_level'] ?? $user['student']['year_level'] ?? $user['student_detail']['year_level'] ?? 1);
                    $selectedSetId = (string)($_POST['set_id'] ?? $user['student']['set_id'] ?? $user['student_detail']['set_id'] ?? '');
                    ?>

                    <!-- Enrollment Status Selector -->
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

                    <!-- Curriculum Year Level & Class Section -->
                    <div class="row g-3">
                        <div class="col-md-6" id="yearLevelWrapper">
                            <label for="year_level" class="form-label fw-semibold text-dark" style="font-size: 13px;">
                                Year Level <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="year_level" name="year_level" required>
                                <option value="1" <?= $currentYearLevel === 1 ? 'selected' : '' ?>>1st Year (Freshman)</option>
                                <option value="2" <?= $currentYearLevel === 2 ? 'selected' : '' ?>>2nd Year (Sophomore)</option>
                                <option value="3" <?= $currentYearLevel === 3 ? 'selected' : '' ?>>3rd Year (Junior)</option>
                                <option value="4" <?= $currentYearLevel === 4 ? 'selected' : '' ?>>4th Year (Senior)</option>
                            </select>
                            <div class="form-text text-muted" id="yearLevelHelp" style="font-size: 12px;">
                                <?= $currentStatus === 'Regular' ? 'Filters sections matching this academic year.' : 'Academic curriculum standing.' ?>
                            </div>
                        </div>

                        <!-- Regular only: Class Set -->
                        <div class="col-md-6 <?= $currentStatus === 'Irregular' ? 'd-none' : '' ?>" id="setSectionWrapper">
                            <label for="set_id" class="form-label fw-semibold text-dark" style="font-size: 13px;">
                                Assigned Section <span class="text-danger" id="setRequiredMarker">*</span>
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

            <?php elseif (in_array($user['role'], ['Faculty', 'Dean'], true)): ?>
                <!-- Department Affiliation -->
                <div class="mt-4 mb-3 pt-3 border-top">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <div class="d-flex align-items-center justify-content-center rounded-2 text-primary" style="width: 28px; height: 28px; background-color: #eff6ff;">
                            <i class="bi bi-building text-primary" style="font-size: 15px;"></i>
                        </div>
                        <div>
                            <h6 class="fw-semibold text-dark mb-0" style="font-size: 14px;">Department Affiliation</h6>
                            <div class="text-muted" style="font-size: 12px;">Collegiate division and academic department appointment.</div>
                        </div>
                    </div>

                    <div class="mb-1">
                        <label for="department_id" class="form-label fw-semibold text-dark" style="font-size: 13px;">
                            Academic Department <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="department_id" name="department_id" required>
                            <option value="">Select college or academic department...</option>
                            <?php
                            $currentDeptId = $user['faculty']['department_id'] ?? null;
                            foreach ($departments ?? [] as $dept):
                                $deptCode = strtoupper(trim($dept['code'] ?? $dept['dept_abbrev'] ?? ''));
                                $isSelected = ((string)$currentDeptId === (string)$dept['id'])
                                           || (empty($currentDeptId) && $deptCode === 'CITE');
                            ?>
                                <option value="<?= $dept['id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['name']) ?> (<?= htmlspecialchars($deptCode) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-muted" style="font-size: 12px;">The collegiate department this academic officer belongs to.</div>
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
                <i class="bi bi-check2"></i> Save Changes
            </button>
        </div>
    </form>
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

    if (!studentStatusInput || !yearLevelSelect || !setSelect) {
        return;
    }

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
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
