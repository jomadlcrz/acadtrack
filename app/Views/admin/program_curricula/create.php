<?php
$pageTitle = 'Create Program Curriculum';
$subtitle = 'Build a multi-year academic curriculum pathway with subjects, units, and prerequisites.';
$headerActions = '<a href="' . url('/admin/program-curricula') . '" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1.5"><i class="bi bi-arrow-left"></i> Back to Curricula</a>';
ob_start();
?>

<!-- Stepper Progress Bar -->
<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-around">
            <div class="step-indicator active d-flex align-items-center gap-2" id="stepIndicator1">
                <span class="step-number rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 14px; background-color: #2f4a86 !important;">1</span>
                <div>
                    <div class="fw-bold small text-dark">Program Details</div>
                    <div class="text-muted text-xs">Degree &amp; College</div>
                </div>
            </div>
            <div class="text-muted"><i class="bi bi-chevron-right"></i></div>
            <div class="step-indicator text-muted d-flex align-items-center gap-2" id="stepIndicator2">
                <span class="step-number rounded-circle bg-light border text-secondary d-inline-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 14px;">2</span>
                <div>
                    <div class="fw-bold small">Curriculum Subjects</div>
                    <div class="text-muted text-xs">Courses &amp; Units</div>
                </div>
            </div>
            <div class="text-muted"><i class="bi bi-chevron-right"></i></div>
            <div class="step-indicator text-muted d-flex align-items-center gap-2" id="stepIndicator3">
                <span class="step-number rounded-circle bg-light border text-secondary d-inline-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 14px;">3</span>
                <div>
                    <div class="fw-bold small">Review &amp; Save</div>
                    <div class="text-muted text-xs">Final Validation</div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="curriculumWizardForm" method="POST" action="<?= url('/admin/program-curricula') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="subjects_json" id="subjectsJsonInput" value="[]">

    <!-- STEP 1: Program Information -->
    <div class="card shadow-sm border-0 mb-4 wizard-step" id="wizardStep1" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
        <div class="card-header bg-white py-3 px-4 border-bottom">
            <h3 class="h6 fw-bold text-dark mb-0">Step 1: Program Information</h3>
            <small class="text-muted">Define the degree program, department, and academic classification.</small>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold text-dark">Program Mode</label>
                    <select class="form-select" id="program_mode" name="program_mode" onchange="toggleProgramMode(this.value)">
                        <option value="new">Create New Program</option>
                        <option value="existing">Add Curriculum to Existing Program</option>
                    </select>
                </div>

                <div class="col-12 col-md-8" id="existingProgramField" style="display: none;">
                    <label class="form-label small fw-semibold text-dark">Select Existing Program <span class="text-danger">*</span></label>
                    <select class="form-select" id="existing_program_id" name="existing_program_id" onchange="populateFromExisting(this.value)">
                        <option value="">-- Choose Program --</option>
                        <?php foreach ($existingPrograms as $ep): ?>
                            <option value="<?= $ep->id ?>" data-abbrev="<?= htmlspecialchars($ep->program_abbrev) ?>" data-name="<?= htmlspecialchars($ep->program_name) ?>" data-dept="<?= $ep->department_id ?>" data-type="<?= htmlspecialchars($ep->program_type) ?>" data-length="<?= htmlspecialchars($ep->program_length) ?>">
                                <?= htmlspecialchars($ep->program_abbrev) ?> — <?= htmlspecialchars($ep->program_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-8 new-program-col">
                    <label class="form-label small fw-semibold text-dark">Program Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="program_name" name="program_name" placeholder="e.g. Bachelor of Science in Information Technology">
                </div>

                <div class="col-12 col-md-4 new-program-col">
                    <label class="form-label small fw-semibold text-dark">Abbreviation / Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control font-monospace" id="program_abbrev" name="program_abbrev" placeholder="e.g. BSIT" style="text-transform: uppercase;">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-dark">Managing Department <span class="text-danger">*</span></label>
                    <select class="form-select" id="department_id" name="department_id">
                        <option value="">-- Select Department --</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept->id ?>"><?= htmlspecialchars($dept->code) ?> — <?= htmlspecialchars($dept->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold text-dark">Program Type</label>
                    <select class="form-select" id="program_type" name="program_type">
                        <option value="Bachelor's Degree">Bachelor's Degree</option>
                        <option value="Associate Degree">Associate Degree</option>
                        <option value="Master's Degree">Master's Degree</option>
                        <option value="Certificate">Certificate</option>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold text-dark">Program Length</label>
                    <select class="form-select" id="program_length" name="program_length">
                        <option value="4 Years">4 Years</option>
                        <option value="2 Years">2 Years</option>
                        <option value="3 Years">3 Years</option>
                        <option value="5 Years">5 Years</option>
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-dark">Curriculum Version <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="version" name="version" value="2026-2027" placeholder="e.g. 2026-2027">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-dark">Initial Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active" selected>Active</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label small fw-semibold text-dark">Program Description</label>
                    <textarea class="form-control" id="description" name="description" rows="2" placeholder="Brief academic overview and program outcomes."></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer bg-light py-3 px-4 d-flex justify-content-end">
            <button type="button" class="btn text-white px-4" style="background-color: #2f4a86; border-color: #2f4a86;" onclick="goToStep(2)">
                Continue to Subjects <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </div>
    </div>

    <!-- STEP 2: Curriculum Subjects -->
    <div class="card shadow-sm border-0 mb-4 wizard-step" id="wizardStep2" style="display: none; border: 1px solid #e2e8f0 !important; border-radius: 6px;">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h3 class="h6 fw-bold text-dark mb-0">Step 2: Curriculum Subjects</h3>
                <small class="text-muted">Specify the courses taught across year levels and semesters.</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addSampleSubjects()">
                    <i class="bi bi-magic me-1"></i> Add Sample BSIT Courses
                </button>
                <button type="button" class="btn btn-sm text-white" style="background-color: #2f4a86; border-color: #2f4a86;" onclick="addSubjectRow()">
                    <i class="bi bi-plus-lg me-1"></i> Add Subject
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="subjectsTable">
                    <thead class="bg-light">
                        <tr style="font-size: 11px;" class="text-uppercase text-secondary">
                            <th style="width: 140px;" class="py-2.5 px-3">Year Level</th>
                            <th style="width: 140px;" class="py-2.5 px-3">Semester</th>
                            <th style="width: 130px;" class="py-2.5 px-3">Subject Code</th>
                            <th class="py-2.5 px-3">Descriptive Title</th>
                            <th style="width: 90px;" class="py-2.5 px-2 text-center">Units</th>
                            <th style="width: 180px;" class="py-2.5 px-3">Subject Type</th>
                            <th style="width: 160px;" class="py-2.5 px-3">Pre-Requisite</th>
                            <th style="width: 60px;" class="py-2.5 px-2 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="subjectsTbody">
                        <!-- Dynamic rows inserted by JS -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light py-3 px-4 d-flex justify-content-between align-items-center">
            <button type="button" class="btn btn-outline-secondary" onclick="goToStep(1)">
                <i class="bi bi-arrow-left me-1"></i> Back
            </button>
            <div class="fw-semibold small text-secondary">
                <span id="step2SubjectCount">0</span> subjects · <span id="step2UnitCount">0.0</span> total units
            </div>
            <button type="button" class="btn text-white px-4" style="background-color: #2f4a86; border-color: #2f4a86;" onclick="goToStep(3)">
                Review &amp; Finalize <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </div>
    </div>

    <!-- STEP 3: Review & Save -->
    <div class="card shadow-sm border-0 mb-4 wizard-step" id="wizardStep3" style="display: none; border: 1px solid #e2e8f0 !important; border-radius: 6px;">
        <div class="card-header bg-white py-3 px-4 border-bottom">
            <h3 class="h6 fw-bold text-dark mb-0">Step 3: Review &amp; Finalize</h3>
            <small class="text-muted">Verify the curriculum configuration before saving to the institutional catalog.</small>
        </div>
        <div class="card-body p-4">
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                    <div class="p-3 bg-light rounded border">
                        <h4 class="small fw-bold text-uppercase text-secondary mb-3">Program Summary</h4>
                        <div class="row g-2 small">
                            <div class="col-5 text-muted">Program:</div>
                            <div class="col-7 fw-bold text-dark" id="reviewProgramName">—</div>
                            <div class="col-5 text-muted">Abbreviation:</div>
                            <div class="col-7 font-monospace fw-bold text-primary" id="reviewProgramAbbrev">—</div>
                            <div class="col-5 text-muted">Department:</div>
                            <div class="col-7 text-dark" id="reviewDepartment">—</div>
                            <div class="col-5 text-muted">Curriculum Version:</div>
                            <div class="col-7 text-dark" id="reviewVersion">—</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="p-3 bg-light rounded border">
                        <h4 class="small fw-bold text-uppercase text-secondary mb-3">Curriculum Metrics</h4>
                        <div class="row g-2 small">
                            <div class="col-6 text-muted">Total Subjects:</div>
                            <div class="col-6 fw-bold text-dark" id="reviewTotalSubjects">0</div>
                            <div class="col-6 text-muted">Total Units:</div>
                            <div class="col-6 fw-bold text-dark" id="reviewTotalUnits">0.0</div>
                            <div class="col-6 text-muted">Status:</div>
                            <div class="col-6" id="reviewStatus"><span class="badge bg-success">Active</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <h5 class="h6 fw-bold text-dark mb-2">Subject Breakdown by Term</h5>
            <div class="table-responsive border rounded mb-4">
                <table class="table table-sm table-hover mb-0 align-middle" id="reviewSummaryTable">
                    <thead class="bg-light">
                        <tr class="small text-secondary text-uppercase">
                            <th class="py-2 px-3">Year Level</th>
                            <th class="py-2 px-3">Semester</th>
                            <th class="py-2 px-3 text-center">Subjects</th>
                            <th class="py-2 px-3 text-center">Total Units</th>
                        </tr>
                    </thead>
                    <tbody id="reviewSummaryTbody">
                        <!-- Dynamic group review summary -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light py-3 px-4 d-flex justify-content-between">
            <button type="button" class="btn btn-outline-secondary" onclick="goToStep(2)">
                <i class="bi bi-arrow-left me-1"></i> Back to Subjects
            </button>
            <button type="button" id="submitCurriculumBtn" class="btn text-white px-4 d-inline-flex align-items-center gap-1.5" style="background-color: #2f4a86; border-color: #2f4a86;" onclick="submitCurriculum()">
                <i class="bi bi-check-circle-fill"></i> Save Program Curriculum
            </button>
        </div>
    </div>
</form>

<script>
const years = ["First Year", "Second Year", "Third Year", "Fourth Year"];
const semesters = ["1st Semester", "2nd Semester", "Summer"];
const subjectTypes = [
    "GenEd Core",
    "Major with Lab",
    "Major without Lab",
    "GenEd Elective",
    "Physical Education",
    "National Service Training Program",
    "Mandated Rizal",
    "Research/Thesis"
];

let subjectsList = [];

function toggleProgramMode(mode) {
    const existingField = document.getElementById('existingProgramField');
    const newCols = document.querySelectorAll('.new-program-col');
    if (mode === 'existing') {
        existingField.style.display = '';
        newCols.forEach(el => el.style.display = 'none');
    } else {
        existingField.style.display = 'none';
        newCols.forEach(el => el.style.display = '');
    }
}

function populateFromExisting(progId) {
    const select = document.getElementById('existing_program_id');
    const opt = select.options[select.selectedIndex];
    if (opt && opt.value) {
        document.getElementById('program_name').value = opt.getAttribute('data-name') || '';
        document.getElementById('program_abbrev').value = opt.getAttribute('data-abbrev') || '';
        if (opt.getAttribute('data-dept')) {
            document.getElementById('department_id').value = opt.getAttribute('data-dept');
        }
    }
}

function addSubjectRow(data = {}) {
    const defaultData = {
        year_level: data.year_level || "First Year",
        semester: data.semester || "1st Semester",
        subject_code: data.subject_code || "",
        descriptive_title: data.descriptive_title || "",
        units: data.units !== undefined ? data.units : 3.0,
        subject_type: data.subject_type || "GenEd Core",
        prerequisites: data.prerequisites || ""
    };

    subjectsList.push(defaultData);
    renderSubjectsTable();
}

function removeSubjectRow(index) {
    subjectsList.splice(index, 1);
    renderSubjectsTable();
}

function updateSubjectField(index, field, value) {
    if (subjectsList[index]) {
        subjectsList[index][field] = value;
        updateTotals();
    }
}

function renderSubjectsTable() {
    const tbody = document.getElementById('subjectsTbody');
    tbody.innerHTML = '';

    if (subjectsList.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted small">No subjects added yet. Click "+ Add Subject" or "Add Sample BSIT Courses".</td></tr>`;
        updateTotals();
        return;
    }

    subjectsList.forEach((sub, idx) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="p-2">
                <select class="form-select form-select-sm" onchange="updateSubjectField(${idx}, 'year_level', this.value)">
                    ${years.map(y => `<option value="${y}" ${sub.year_level === y ? 'selected' : ''}>${y}</option>`).join('')}
                </select>
            </td>
            <td class="p-2">
                <select class="form-select form-select-sm" onchange="updateSubjectField(${idx}, 'semester', this.value)">
                    ${semesters.map(s => `<option value="${s}" ${sub.semester === s ? 'selected' : ''}>${s}</option>`).join('')}
                </select>
            </td>
            <td class="p-2">
                <input type="text" class="form-control form-control-sm font-monospace" style="text-transform: uppercase;" value="${escapeHtml(sub.subject_code)}" placeholder="e.g. IT101" oninput="updateSubjectField(${idx}, 'subject_code', this.value.toUpperCase())">
            </td>
            <td class="p-2">
                <input type="text" class="form-control form-control-sm" value="${escapeHtml(sub.descriptive_title)}" placeholder="Subject Title" oninput="updateSubjectField(${idx}, 'descriptive_title', this.value)">
            </td>
            <td class="p-2">
                <input type="number" step="0.5" min="0" class="form-control form-control-sm text-center" value="${sub.units}" oninput="updateSubjectField(${idx}, 'units', parseFloat(this.value) || 0)">
            </td>
            <td class="p-2">
                <select class="form-select form-select-sm" onchange="updateSubjectField(${idx}, 'subject_type', this.value)">
                    ${subjectTypes.map(st => `<option value="${st}" ${sub.subject_type === st ? 'selected' : ''}>${st}</option>`).join('')}
                </select>
            </td>
            <td class="p-2">
                <input type="text" class="form-control form-control-sm" value="${escapeHtml(sub.prerequisites)}" placeholder="e.g. IT101" oninput="updateSubjectField(${idx}, 'prerequisites', this.value)">
            </td>
            <td class="p-2 text-center">
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeSubjectRow(${idx})" title="Remove"><i class="bi bi-trash"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    updateTotals();
}

function updateTotals() {
    const totalUnits = subjectsList.reduce((sum, s) => sum + (parseFloat(s.units) || 0), 0);
    const count = subjectsList.length;

    document.getElementById('step2SubjectCount').textContent = count;
    document.getElementById('step2UnitCount').textContent = totalUnits.toFixed(1);
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function addSampleSubjects() {
    const sample = [
        { year_level: "First Year", semester: "1st Semester", subject_code: "IT101", descriptive_title: "Introduction to Computing", units: 3.0, subject_type: "GenEd Core", prerequisites: "" },
        { year_level: "First Year", semester: "1st Semester", subject_code: "IT102", descriptive_title: "Computer Programming 1", units: 3.0, subject_type: "Major with Lab", prerequisites: "" },
        { year_level: "First Year", semester: "2nd Semester", subject_code: "IT103", descriptive_title: "Computer Programming 2", units: 3.0, subject_type: "Major with Lab", prerequisites: "IT102" },
        { year_level: "First Year", semester: "2nd Semester", subject_code: "IT104", descriptive_title: "Data Structures and Algorithms", units: 3.0, subject_type: "Major with Lab", prerequisites: "IT102" },
        { year_level: "Second Year", semester: "1st Semester", subject_code: "IT201", descriptive_title: "Object-Oriented Programming", units: 3.0, subject_type: "Major with Lab", prerequisites: "IT104" },
        { year_level: "Second Year", semester: "1st Semester", subject_code: "IT202", descriptive_title: "Information Management", units: 3.0, subject_type: "Major with Lab", prerequisites: "IT104" },
    ];
    sample.forEach(s => subjectsList.push(s));
    renderSubjectsTable();
}

function goToStep(stepNumber) {
    if (stepNumber === 2) {
        const mode = document.getElementById('program_mode').value;
        if (mode === 'new') {
            const name = document.getElementById('program_name').value.trim();
            const abbrev = document.getElementById('program_abbrev').value.trim();
            if (!name || !abbrev) {
                alert('Please enter Program Name and Abbreviation before proceeding.');
                return;
            }
        } else {
            const ep = document.getElementById('existing_program_id').value;
            if (!ep) {
                alert('Please select an existing program.');
                return;
            }
        }
    }

    if (stepNumber === 3) {
        if (subjectsList.length === 0) {
            alert('Please add at least one curriculum subject.');
            return;
        }
        buildReviewStep();
    }

    // Toggle steps
    document.querySelectorAll('.wizard-step').forEach(el => el.style.display = 'none');
    document.getElementById('wizardStep' + stepNumber).style.display = '';

    // Update indicator states
    for (let i = 1; i <= 3; i++) {
        const ind = document.getElementById('stepIndicator' + i);
        const num = ind.querySelector('.step-number');
        if (i < stepNumber) {
            num.className = 'step-number rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center fw-bold';
            num.style.backgroundColor = '#10b981';
            num.innerHTML = '<i class="bi bi-check"></i>';
        } else if (i === stepNumber) {
            num.className = 'step-number rounded-circle text-white d-inline-flex align-items-center justify-content-center fw-bold';
            num.style.backgroundColor = '#2f4a86';
            num.innerHTML = i;
        } else {
            num.className = 'step-number rounded-circle bg-light border text-secondary d-inline-flex align-items-center justify-content-center fw-bold';
            num.style.backgroundColor = '';
            num.innerHTML = i;
        }
    }
}

function buildReviewStep() {
    const mode = document.getElementById('program_mode').value;
    const name = mode === 'new' ? document.getElementById('program_name').value : document.getElementById('existing_program_id').selectedOptions[0]?.getAttribute('data-name');
    const abbrev = mode === 'new' ? document.getElementById('program_abbrev').value : document.getElementById('existing_program_id').selectedOptions[0]?.getAttribute('data-abbrev');
    const deptSelect = document.getElementById('department_id');
    const deptName = deptSelect.selectedOptions[0] ? deptSelect.selectedOptions[0].text : 'None';
    const version = document.getElementById('version').value;
    const status = document.getElementById('status').value;

    document.getElementById('reviewProgramName').textContent = name || '—';
    document.getElementById('reviewProgramAbbrev').textContent = abbrev || '—';
    document.getElementById('reviewDepartment').textContent = deptName;
    document.getElementById('reviewVersion').textContent = version;
    document.getElementById('reviewStatus').innerHTML = status === 'active' 
        ? '<span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>'
        : '<span class="badge bg-warning-subtle text-warning border border-warning-subtle">Draft</span>';

    const totalUnits = subjectsList.reduce((sum, s) => sum + (parseFloat(s.units) || 0), 0);
    document.getElementById('reviewTotalSubjects').textContent = subjectsList.length;
    document.getElementById('reviewTotalUnits').textContent = totalUnits.toFixed(1);

    // Group breakdown
    const breakdown = {};
    subjectsList.forEach(s => {
        const key = `${s.year_level} — ${s.semester}`;
        if (!breakdown[key]) {
            breakdown[key] = { year_level: s.year_level, semester: s.semester, count: 0, units: 0 };
        }
        breakdown[key].count++;
        breakdown[key].units += (parseFloat(s.units) || 0);
    });

    const tbody = document.getElementById('reviewSummaryTbody');
    tbody.innerHTML = '';
    Object.values(breakdown).forEach(g => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="py-2 px-3 fw-semibold text-dark">${escapeHtml(g.year_level)}</td>
            <td class="py-2 px-3 text-secondary">${escapeHtml(g.semester)}</td>
            <td class="py-2 px-3 text-center">${g.count}</td>
            <td class="py-2 px-3 text-center fw-bold">${g.units.toFixed(1)}</td>
        `;
        tbody.appendChild(tr);
    });
}

function submitCurriculum() {
    document.getElementById('subjectsJsonInput').value = JSON.stringify(subjectsList);
    const form = document.getElementById('curriculumWizardForm');
    const submitBtn = document.getElementById('submitCurriculumBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
    form.submit();
}

document.addEventListener('DOMContentLoaded', function() {
    addSampleSubjects();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
