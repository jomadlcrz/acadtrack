<?php
$pageTitle = 'Create Program Curriculum';
$subtitle = 'Build a multi-year academic curriculum pathway with subjects, units, and prerequisites.';
$headerActions = '<a href="' . url('/admin/program-curricula') . '" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1.5"><i class="bi bi-arrow-left"></i> Back to Curricula</a>';
ob_start();
?>

<!-- Stepper Progress Bar -->
<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 8px;">
    <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-around">
            <div class="step-indicator active d-flex align-items-center gap-2" id="stepIndicator1">
                <span class="step-number rounded-circle text-white d-inline-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 13px; background-color: #2f4a86;">1</span>
                <div>
                    <div class="fw-bold small text-dark">Program Details</div>
                    <div class="text-muted text-xs">Degree &amp; Department</div>
                </div>
            </div>
            <div class="text-muted"><i class="bi bi-chevron-right"></i></div>
            <div class="step-indicator text-muted d-flex align-items-center gap-2" id="stepIndicator2">
                <span class="step-number rounded-circle bg-light border text-secondary d-inline-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 13px;">2</span>
                <div>
                    <div class="fw-bold small">Curriculum Subjects</div>
                    <div class="text-muted text-xs">Courses &amp; Units</div>
                </div>
            </div>
            <div class="text-muted"><i class="bi bi-chevron-right"></i></div>
            <div class="step-indicator text-muted d-flex align-items-center gap-2" id="stepIndicator3">
                <span class="step-number rounded-circle bg-light border text-secondary d-inline-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 13px;">3</span>
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
    <div class="card shadow-sm border-0 mb-4 wizard-step" id="wizardStep1" style="border: 1px solid #e2e8f0 !important; border-radius: 8px;">
        <div class="card-header bg-white py-3 px-4 border-bottom">
            <h3 class="h6 fw-bold text-dark mb-0">Step 1: Program Information</h3>
            <small class="text-muted">Define the degree program, department, and academic classification.</small>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <!-- Program Name -->
                <div class="col-12">
                    <label class="form-label small fw-semibold text-dark">Program Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="program_name" name="program_name" placeholder="e.g. Bachelor of Science in Information Technology" required>
                    <div class="form-text text-muted text-xs">The full title of the degree or academic certificate program.</div>
                </div>

                <!-- Grouped: Department & Program Abbreviation -->
                <div class="col-12">
                    <div class="p-3 rounded border" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="bi bi-building text-primary" style="color: #2f4a86 !important;"></i>
                            <span class="small fw-bold text-dark text-uppercase" style="letter-spacing: 0.5px; font-size: 11px;">Department Affiliation &amp; Program Code</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label class="form-label small fw-semibold text-dark">Managing Department <span class="text-danger">*</span></label>
                                <select class="form-select bg-white" id="department_id" name="department_id" required>
                                    <option value="">-- Select Department --</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept->id ?>">
                                            <?= htmlspecialchars($dept->dept_abbrev ?? $dept->code ?? '') ?> — <?= htmlspecialchars($dept->dept_name ?? $dept->name ?? '') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text text-muted text-xs">The academic college or unit overseeing this curriculum.</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold text-dark">Abbreviation / Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control font-monospace bg-white fw-bold" id="program_abbrev" name="program_abbrev" placeholder="e.g. BSIT" style="text-transform: uppercase; letter-spacing: 0.5px;" required>
                                <div class="form-text text-muted text-xs">Official institutional program abbreviation.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Program Type, Length, Status -->
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold text-dark">Program Type</label>
                    <select class="form-select" id="program_type" name="program_type">
                        <option value="Bachelor's Degree">Bachelor's Degree</option>
                        <option value="Associate Degree">Associate Degree</option>
                        <option value="Master's Degree">Master's Degree</option>
                        <option value="Certificate">Certificate</option>
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold text-dark">Program Length</label>
                    <select class="form-select" id="program_length" name="program_length">
                        <option value="4 Years">4 Years</option>
                        <option value="2 Years">2 Years</option>
                        <option value="3 Years">3 Years</option>
                        <option value="5 Years">5 Years</option>
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold text-dark">Initial Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active" selected>Active</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>

                <!-- Program Description -->
                <div class="col-12">
                    <label class="form-label small fw-semibold text-dark">Program Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" placeholder="Brief academic overview, learning outcomes, and career pathways."></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer bg-light py-3 px-4 d-flex justify-content-end">
            <button type="button" class="btn text-white px-4 d-inline-flex align-items-center gap-1.5" style="background-color: #2f4a86; border-color: #2f4a86;" onclick="goToStep(2)">
                Continue to Subjects <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </div>

    <!-- STEP 2: Curriculum Subjects -->
    <div class="card shadow-sm border-0 mb-4 wizard-step" id="wizardStep2" style="display: none; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h3 class="h6 fw-bold text-dark mb-0">Step 2: Curriculum Subjects</h3>
                <small class="text-muted">Define courses, credit units, prerequisites, and sequential order.</small>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1.5" onclick="openImportModal()">
                    <i class="bi bi-file-earmark-excel text-success"></i> Import Excel / CSV
                </button>
                <div class="dropdown d-inline-block">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-inline-flex align-items-center gap-1.5" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-download"></i> Template
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border text-xs" style="font-size: 13px;">
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-1.5" onclick="downloadExcelTemplate()">
                                <i class="bi bi-file-earmark-excel text-success fs-6"></i> Excel Template (.xlsx)
                            </button>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-1.5" href="<?= url('/admin/program-curricula/template-csv') ?>" download>
                                <i class="bi bi-file-earmark-text text-primary fs-6"></i> CSV Template (.csv)
                            </a>
                        </li>
                    </ul>
                </div>
                <button type="button" class="btn btn-sm text-white d-inline-flex align-items-center gap-1.5" style="background-color: #2f4a86; border-color: #2f4a86;" onclick="addSubjectRow()">
                    <i class="bi bi-plus-lg"></i> Add Subject
                </button>
            </div>
        </div>

        <!-- Table drop zone area -->
        <div class="card-body p-0" id="subjectsTableContainer">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="subjectsTable">
                    <thead class="bg-light">
                        <tr style="font-size: 11px; letter-spacing: 0.5px;" class="text-uppercase text-secondary">
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
            <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1.5" onclick="goToStep(1)">
                <i class="bi bi-arrow-left"></i> Back to Program Info
            </button>
            <div class="fw-semibold small text-secondary">
                <span id="step2SubjectCount" class="fw-bold text-dark">0</span> subjects · <span id="step2UnitCount" class="fw-bold text-dark">0.0</span> total units
            </div>
            <button type="button" class="btn text-white px-4 d-inline-flex align-items-center gap-1.5" style="background-color: #2f4a86; border-color: #2f4a86;" onclick="goToStep(3)">
                Review &amp; Finalize <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </div>

    <!-- STEP 3: Review & Save -->
    <div class="card shadow-sm border-0 mb-4 wizard-step" id="wizardStep3" style="display: none; border: 1px solid #e2e8f0 !important; border-radius: 8px;">
        <div class="card-header bg-white py-3 px-4 border-bottom">
            <h3 class="h6 fw-bold text-dark mb-0">Step 3: Review &amp; Finalize</h3>
            <small class="text-muted">Verify the curriculum configuration before saving to the institutional catalog.</small>
        </div>
        <div class="card-body p-4">
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                    <div class="p-3 bg-light rounded border">
                        <h4 class="small fw-bold text-uppercase text-secondary mb-3" style="letter-spacing: 0.5px;">Program Summary</h4>
                        <div class="row g-2 small">
                            <div class="col-5 text-muted">Program:</div>
                            <div class="col-7 fw-bold text-dark" id="reviewProgramName">—</div>
                            <div class="col-5 text-muted">Abbreviation / Code:</div>
                            <div class="col-7 font-monospace fw-bold text-primary" id="reviewProgramAbbrev">—</div>
                            <div class="col-5 text-muted">Department:</div>
                            <div class="col-7 text-dark" id="reviewDepartment">—</div>
                            <div class="col-5 text-muted">Classification:</div>
                            <div class="col-7 text-dark" id="reviewClassification">—</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="p-3 bg-light rounded border">
                        <h4 class="small fw-bold text-uppercase text-secondary mb-3" style="letter-spacing: 0.5px;">Curriculum Metrics</h4>
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
                        <tr class="small text-secondary text-uppercase" style="font-size: 11px;">
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
            <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1.5" onclick="goToStep(2)">
                <i class="bi bi-arrow-left"></i> Back to Subjects
            </button>
            <button type="button" id="submitCurriculumBtn" class="btn text-white px-4 d-inline-flex align-items-center gap-1.5" style="background-color: #2f4a86; border-color: #2f4a86;" onclick="submitCurriculum()">
                <i class="bi bi-check-circle-fill"></i> Save Program Curriculum
            </button>
        </div>
    </div>
</form>

<!-- IMPORT SUBJECTS MODAL -->
<div class="modal fade" id="importSubjectsModal" tabindex="-1" aria-labelledby="importSubjectsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 8px;">
            <div class="modal-header border-bottom py-3 px-4">
                <div>
                    <h5 class="modal-title fw-bold text-dark mb-0" id="importSubjectsModalLabel">
                        <i class="bi bi-file-earmark-excel text-success me-1"></i> Import Curriculum Subjects (Excel / CSV)
                    </h5>
                    <div class="text-muted text-xs mt-0.5">Upload an Excel workbook (.xlsx, .xls) or CSV file to bulk-populate subjects.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Tabs: Upload File vs Paste Data -->
                <ul class="nav nav-tabs mb-3" id="importTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold small" id="upload-tab" data-bs-toggle="tab" data-bs-target="#uploadTabPane" type="button" role="tab" aria-selected="true">
                            <i class="bi bi-file-earmark-excel text-success me-1"></i> Upload Excel / CSV File
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold small" id="paste-tab" data-bs-toggle="tab" data-bs-target="#pasteTabPane" type="button" role="tab" aria-selected="false">
                            <i class="bi bi-clipboard me-1"></i> Paste Tabular Data
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="importTabContent">
                    <!-- Upload File Pane -->
                    <div class="tab-pane fade show active" id="uploadTabPane" role="tabpanel">
                        <div id="dropZone" class="border border-2 border-dashed rounded p-4 text-center" style="border-color: #cbd5e1 !important; background-color: #f8fafc; cursor: pointer;">
                            <i class="bi bi-file-earmark-excel display-6 text-success mb-2 d-block"></i>
                            <div class="fw-semibold text-dark mb-1">Drag &amp; drop your Excel (.xlsx, .xls) or CSV file here, or click to browse</div>
                            <div class="text-muted text-xs mb-3">Accepts Microsoft Excel (.xlsx, .xls) and standard CSV/TSV spreadsheets</div>
                            <input type="file" id="csvFileInput" accept=".xlsx,.xls,.csv,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" style="display: none;">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('csvFileInput').click()">
                                Choose Excel / CSV File
                            </button>
                            <div id="selectedFileName" class="mt-2 text-xs fw-bold text-success" style="display: none;"></div>
                            <!-- Sheet selector for multi-sheet workbooks -->
                            <div id="sheetSelectorContainer" class="mt-3 text-start p-2.5 bg-white rounded border" style="display: none;">
                                <label class="form-label small fw-semibold text-dark mb-1 d-flex align-items-center gap-1.5">
                                    <i class="bi bi-layers text-primary"></i> Multiple sheets found. Select sheet to import:
                                </label>
                                <select id="sheetSelect" class="form-select form-select-sm" onchange="onSheetSelectChange()"></select>
                            </div>
                        </div>
                    </div>

                    <!-- Paste Data Pane -->
                    <div class="tab-pane fade" id="pasteTabPane" role="tabpanel">
                        <label class="form-label small fw-semibold text-dark">Paste tabular data (copied directly from Excel or Google Sheets):</label>
                        <textarea id="pasteCsvTextarea" class="form-control font-monospace text-xs" rows="7" placeholder="Year Level&#9;Semester&#9;Subject Code&#9;Descriptive Title&#9;Units&#9;Subject Type&#9;Pre-Requisite&#10;First Year&#9;1st Semester&#9;IT101&#9;Introduction to Computing&#9;3.0&#9;GenEd Core&#9;&#10;First Year&#9;1st Semester&#9;IT102&#9;Computer Programming 1&#9;3.0&#9;Major with Lab&#9;&#10;First Year&#9;2nd Semester&#9;IT103&#9;Computer Programming 2&#9;3.0&#9;Major with Lab&#9;IT102"></textarea>
                        <div class="d-flex justify-content-end mt-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="parsePastedText()">
                                <i class="bi bi-lightning-charge me-1"></i> Parse Pasted Content
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Format Guidance Banner -->
                <div class="mt-3 p-3 bg-light rounded border" style="border-color: #e2e8f0 !important;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-bold text-dark">Expected Column Order / Headers:</span>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-link p-0 text-xs fw-semibold text-success text-decoration-none" onclick="downloadExcelTemplate()">
                                <i class="bi bi-file-earmark-excel me-0.5"></i> Excel Template (.xlsx)
                            </button>
                            <span class="text-muted">·</span>
                            <a href="<?= url('/admin/program-curricula/template-csv') ?>" class="text-xs fw-semibold text-primary text-decoration-none" download>
                                <i class="bi bi-file-earmark-text me-0.5"></i> CSV Template (.csv)
                            </a>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-1 mt-1 font-monospace text-xs">
                        <span class="badge bg-white border text-dark">Year Level</span>
                        <span class="badge bg-white border text-dark">Semester</span>
                        <span class="badge bg-white border text-dark">Subject Code</span>
                        <span class="badge bg-white border text-dark">Descriptive Title</span>
                        <span class="badge bg-white border text-dark">Units</span>
                        <span class="badge bg-white border text-dark">Subject Type</span>
                        <span class="badge bg-white border text-dark">Pre-Requisite</span>
                    </div>
                </div>

                <!-- Import Mode Options -->
                <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between">
                    <span class="small fw-semibold text-dark">Import Action:</span>
                    <div class="d-flex gap-3">
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="radio" name="importModeRadio" id="importModeAppend" value="append" checked>
                            <label class="form-check-label small" for="importModeAppend">Append to current list</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="radio" name="importModeRadio" id="importModeReplace" value="replace">
                            <label class="form-check-label small" for="importModeReplace">Replace current list</label>
                        </div>
                    </div>
                </div>

                <!-- Parse Preview Section -->
                <div id="importPreviewContainer" class="mt-3 pt-3 border-top" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-bold text-dark" id="importPreviewTitle">Parsed Preview</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle" id="importPreviewBadge">0 Valid Courses</span>
                    </div>
                    <div class="table-responsive border rounded" style="max-height: 200px;">
                        <table class="table table-sm table-striped mb-0 text-xs" id="importPreviewTable">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th>Year</th>
                                    <th>Sem</th>
                                    <th>Code</th>
                                    <th>Title</th>
                                    <th class="text-center">Units</th>
                                    <th>Type</th>
                                    <th>Prereq</th>
                                </tr>
                            </thead>
                            <tbody id="importPreviewTbody">
                                <!-- Preview rows inserted by JS -->
                            </tbody>
                        </table>
                    </div>
                    <div id="importValidationErrors" class="alert alert-warning py-2 px-3 small mt-2" style="display: none;"></div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4 border-top d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="applyImportBtn" class="btn btn-primary d-inline-flex align-items-center gap-1.5" disabled onclick="applyImportedSubjects()">
                    <i class="bi bi-check2"></i> Apply Subjects to Curriculum
                </button>
            </div>
        </div>
    </div>
</div>

<!-- SheetJS for Excel (.xlsx, .xls) and Spreadsheet Parsing -->
<script src="<?= asset('vendor/xlsx/xlsx.full.min.js') ?>"></script>
<script>
if (typeof XLSX === 'undefined') {
    const s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js';
    document.head.appendChild(s);
}
</script>

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
let parsedImportRows = [];
let currentWorkbook = null;

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
        tbody.innerHTML = `<tr><td colspan="8" class="p-0"><div class="empty-state py-4"><div class="empty-state-icon"><i class="bi bi-journal-x"></i></div><h4 class="empty-state-title">No subjects added yet</h4><p class="empty-state-text">Click "+ Add Subject" or "Import Subjects" above to start building the curriculum framework.</p></div></td></tr>`;
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
                <input type="text" class="form-control form-control-sm font-monospace fw-semibold" style="text-transform: uppercase;" value="${escapeHtml(sub.subject_code)}" placeholder="e.g. IT101" oninput="updateSubjectField(${idx}, 'subject_code', this.value.toUpperCase())">
            </td>
            <td class="p-2">
                <input type="text" class="form-control form-control-sm" value="${escapeHtml(sub.descriptive_title)}" placeholder="Descriptive Title" oninput="updateSubjectField(${idx}, 'descriptive_title', this.value)">
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
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeSubjectRow(${idx})" title="Remove Subject"><i class="bi bi-trash"></i></button>
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

function goToStep(stepNumber) {
    if (stepNumber === 2) {
        const name = document.getElementById('program_name').value.trim();
        const abbrev = document.getElementById('program_abbrev').value.trim();
        const dept = document.getElementById('department_id').value;
        if (!name || !abbrev) {
            alert('Please enter Program Name and Program Abbreviation before proceeding.');
            return;
        }
        if (!dept) {
            alert('Please select a Managing Department before proceeding.');
            return;
        }
    }

    if (stepNumber === 3) {
        if (subjectsList.length === 0) {
            alert('Please add or import at least one curriculum subject.');
            return;
        }
        buildReviewStep();
    }

    // Toggle wizard steps
    document.querySelectorAll('.wizard-step').forEach(el => el.style.display = 'none');
    document.getElementById('wizardStep' + stepNumber).style.display = '';

    // Update step indicator icons and states
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
    const name = document.getElementById('program_name').value.trim();
    const abbrev = document.getElementById('program_abbrev').value.trim();
    const deptSelect = document.getElementById('department_id');
    const deptName = deptSelect.selectedOptions[0] ? deptSelect.selectedOptions[0].text : 'None';
    const pType = document.getElementById('program_type').value;
    const pLength = document.getElementById('program_length').value;
    const status = document.getElementById('status').value;

    document.getElementById('reviewProgramName').textContent = name || '—';
    document.getElementById('reviewProgramAbbrev').textContent = abbrev || '—';
    document.getElementById('reviewDepartment').textContent = deptName;
    document.getElementById('reviewClassification').textContent = `${pType} · ${pLength}`;
    document.getElementById('reviewStatus').innerHTML = status === 'active' 
        ? '<span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>'
        : '<span class="badge bg-warning-subtle text-warning border border-warning-subtle">Draft</span>';

    const totalUnits = subjectsList.reduce((sum, s) => sum + (parseFloat(s.units) || 0), 0);
    document.getElementById('reviewTotalSubjects').textContent = subjectsList.length;
    document.getElementById('reviewTotalUnits').textContent = totalUnits.toFixed(1);

    // Group breakdown by year level & semester
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
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving Curriculum...';
    form.submit();
}

// ----------------------------------------------------
// IMPORT CSV / TSV LOGIC
// ----------------------------------------------------
function openImportModal() {
    parsedImportRows = [];
    currentWorkbook = null;
    document.getElementById('importPreviewContainer').style.display = 'none';
    document.getElementById('sheetSelectorContainer').style.display = 'none';
    document.getElementById('applyImportBtn').disabled = true;
    document.getElementById('selectedFileName').style.display = 'none';
    document.getElementById('selectedFileName').textContent = '';
    document.getElementById('pasteCsvTextarea').value = '';
    document.getElementById('csvFileInput').value = '';

    const modalEl = document.getElementById('importSubjectsModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

// File drop zone setup
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('csvFileInput');

if (dropZone && fileInput) {
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#2f4a86';
        dropZone.style.backgroundColor = '#eff6ff';
    });
    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#cbd5e1';
        dropZone.style.backgroundColor = '#f8fafc';
    });
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.style.borderColor = '#cbd5e1';
        dropZone.style.backgroundColor = '#f8fafc';
        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            handleFileSelect(e.dataTransfer.files[0]);
        }
    });
    fileInput.addEventListener('change', (e) => {
        if (e.target.files && e.target.files.length > 0) {
            handleFileSelect(e.target.files[0]);
        }
    });
}

function handleFileSelect(file) {
    const isExcel = file.name.toLowerCase().endsWith('.xlsx') || file.name.toLowerCase().endsWith('.xls');
    const isCsv = file.name.toLowerCase().endsWith('.csv') || file.type.includes('csv') || file.type.includes('text');

    if (!isExcel && !isCsv) {
        alert('Please upload an Excel workbook (.xlsx, .xls) or a CSV file (.csv).');
        return;
    }

    const nameEl = document.getElementById('selectedFileName');
    nameEl.textContent = `✓ Selected: ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
    nameEl.style.display = 'block';

    if (isExcel) {
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                if (typeof XLSX === 'undefined') {
                    alert('Excel library is loading, please try again in a moment.');
                    return;
                }
                const data = new Uint8Array(e.target.result);
                currentWorkbook = XLSX.read(data, { type: 'array' });
                setupSheetSelector(currentWorkbook);
                loadSheetData(currentWorkbook, currentWorkbook.SheetNames[0]);
            } catch (err) {
                console.error('Excel parse error:', err);
                alert('Could not parse this Excel workbook. Please verify the file format.');
            }
        };
        reader.readAsArrayBuffer(file);
    } else {
        document.getElementById('sheetSelectorContainer').style.display = 'none';
        currentWorkbook = null;
        const reader = new FileReader();
        reader.onload = function(e) {
            parseDelimitedText(e.target.result);
        };
        reader.readAsText(file);
    }
}

function setupSheetSelector(workbook) {
    const container = document.getElementById('sheetSelectorContainer');
    const select = document.getElementById('sheetSelect');
    select.innerHTML = '';

    if (!workbook || !workbook.SheetNames || workbook.SheetNames.length <= 1) {
        container.style.display = 'none';
        return;
    }

    workbook.SheetNames.forEach(name => {
        const opt = document.createElement('option');
        opt.value = name;
        opt.textContent = name;
        select.appendChild(opt);
    });
    container.style.display = 'block';
}

function onSheetSelectChange() {
    if (!currentWorkbook) return;
    const select = document.getElementById('sheetSelect');
    loadSheetData(currentWorkbook, select.value);
}

function loadSheetData(workbook, sheetName) {
    const worksheet = workbook.Sheets[sheetName];
    if (!worksheet) return;
    const matrix = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: '' });
    parseMatrixData(matrix);
}

function parsePastedText() {
    const text = document.getElementById('pasteCsvTextarea').value;
    if (!text.trim()) {
        alert('Please paste spreadsheet tabular data first.');
        return;
    }
    parseDelimitedText(text);
}

function parseDelimitedText(rawText) {
    const lines = rawText.split(/\r?\n/).filter(line => line.trim() !== '');
    if (lines.length === 0) {
        alert('File or input text is empty.');
        return;
    }

    const firstLine = lines[0];
    const delimiter = firstLine.includes('\t') ? '\t' : ',';

    const parseLine = (line) => {
        if (delimiter === '\t') {
            return line.split('\t').map(c => c.trim().replace(/^["']|["']$/g, ''));
        }
        const pattern = /(?:,|\r?\n|^)("(?:(?:"")*[^"]*)*"|[^",\r\n]*)/gi;
        const entries = [];
        let match;
        while ((match = pattern.exec(line)) !== null) {
            let val = match[1];
            if (val === undefined) val = '';
            if (val.startsWith('"') && val.endsWith('"')) {
                val = val.substring(1, val.length - 1).replace(/""/g, '"');
            }
            entries.push(val.trim());
        }
        return entries;
    };

    const matrix = lines.map(line => parseLine(line));
    parseMatrixData(matrix);
}

function parseMatrixData(rows) {
    if (!rows || rows.length === 0) {
        alert('No rows found to parse.');
        return;
    }

    const nonEmptyRows = rows.filter(r => Array.isArray(r) && r.some(c => String(c || '').trim() !== ''));
    if (nonEmptyRows.length === 0) {
        renderImportPreview(['No non-empty data rows found in worksheet.']);
        return;
    }

    const firstRowClean = nonEmptyRows[0].map(c => String(c || '').toLowerCase().replace(/[^a-z0-9]/g, ''));
    let startIdx = 0;

    const isHeader = firstRowClean.some(h => ['yearlevel', 'year', 'semester', 'sem', 'subjectcode', 'code', 'descriptivetitle', 'title', 'units', 'nature', 'prerequisite'].includes(h));
    if (isHeader) {
        startIdx = 1;
    }

    let colYear = -1, colSem = -1, colCode = -1, colTitle = -1, colUnits = -1, colType = -1, colPrereq = -1;

    if (isHeader) {
        firstRowClean.forEach((col, idx) => {
            if (col.includes('year')) colYear = idx;
            else if (col.includes('sem')) colSem = idx;
            else if (col.includes('code')) colCode = idx;
            else if (col.includes('title') || col.includes('name') || col.includes('desc')) colTitle = idx;
            else if (col.includes('unit')) colUnits = idx;
            else if (col.includes('type') || col.includes('nature') || col.includes('category')) colType = idx;
            else if (col.includes('prereq') || col.includes('requisite')) colPrereq = idx;
        });
    }

    if (colYear === -1) colYear = 0;
    if (colSem === -1) colSem = 1;
    if (colCode === -1) colCode = 2;
    if (colTitle === -1) colTitle = 3;
    if (colUnits === -1) colUnits = 4;
    if (colType === -1) colType = 5;
    if (colPrereq === -1) colPrereq = 6;

    parsedImportRows = [];
    const errors = [];

    for (let i = startIdx; i < nonEmptyRows.length; i++) {
        const row = nonEmptyRows[i];
        const rawCode = String(row[colCode] || '').trim().toUpperCase();
        const rawTitle = String(row[colTitle] || '').trim();
        const rawUnits = parseFloat(row[colUnits]) || 3.0;
        const rawYear = normalizeYearLevel(row[colYear]);
        const rawSem = normalizeSemester(row[colSem]);
        const rawType = normalizeSubjectType(row[colType]);
        const rawPrereq = String(row[colPrereq] || '').trim();

        if (!rawCode || !rawTitle) {
            errors.push(`Row ${i + 1}: Missing course code or title.`);
            continue;
        }

        parsedImportRows.push({
            year_level: rawYear,
            semester: rawSem,
            subject_code: rawCode,
            descriptive_title: rawTitle,
            units: rawUnits,
            subject_type: rawType,
            prerequisites: rawPrereq
        });
    }

    renderImportPreview(errors);
}

function downloadExcelTemplate() {
    if (typeof XLSX === 'undefined') {
        window.location.href = '<?= url('/admin/program-curricula/template-csv') ?>';
        return;
    }

    const wsData = [
        ["Year Level", "Semester", "Subject Code", "Descriptive Title", "Units", "Subject Type", "Pre-Requisite"],
        ["First Year", "1st Semester", "IT101", "Introduction to Computing", 3.0, "GenEd Core", ""],
        ["First Year", "1st Semester", "IT102", "Computer Programming 1", 3.0, "Major with Lab", ""],
        ["First Year", "2nd Semester", "IT103", "Computer Programming 2", 3.0, "Major with Lab", "IT102"],
        ["First Year", "2nd Semester", "IT104", "Data Structures and Algorithms", 3.0, "Major with Lab", "IT102"]
    ];

    const ws = XLSX.utils.aoa_to_sheet(wsData);
    ws['!cols'] = [
        { wch: 14 },
        { wch: 16 },
        { wch: 14 },
        { wch: 35 },
        { wch: 8 },
        { wch: 22 },
        { wch: 16 }
    ];

    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Curriculum");
    XLSX.writeFile(wb, "Curriculum_Subjects_Template.xlsx");
}

function normalizeYearLevel(val) {
    const s = String(val || '').toLowerCase();
    if (s.includes('1') || s.includes('first')) return 'First Year';
    if (s.includes('2') || s.includes('second')) return 'Second Year';
    if (s.includes('3') || s.includes('third')) return 'Third Year';
    if (s.includes('4') || s.includes('fourth')) return 'Fourth Year';
    return 'First Year';
}

function normalizeSemester(val) {
    const s = String(val || '').toLowerCase();
    if (s.includes('summer') || s.includes('midyear')) return 'Summer';
    if (s.includes('2') || s.includes('second') || s.includes('2nd')) return '2nd Semester';
    return '1st Semester';
}

function normalizeSubjectType(val) {
    const s = String(val || '').trim();
    const match = subjectTypes.find(t => t.toLowerCase() === s.toLowerCase());
    if (match) return match;
    if (s.toLowerCase().includes('lab')) return 'Major with Lab';
    if (s.toLowerCase().includes('pe') || s.toLowerCase().includes('physical')) return 'Physical Education';
    if (s.toLowerCase().includes('nstp')) return 'National Service Training Program';
    if (s.toLowerCase().includes('rizal')) return 'Mandated Rizal';
    if (s.toLowerCase().includes('thesis') || s.toLowerCase().includes('research')) return 'Research/Thesis';
    if (s.toLowerCase().includes('major')) return 'Major without Lab';
    return 'GenEd Core';
}

function renderImportPreview(errors) {
    const container = document.getElementById('importPreviewContainer');
    const tbody = document.getElementById('importPreviewTbody');
    const badge = document.getElementById('importPreviewBadge');
    const applyBtn = document.getElementById('applyImportBtn');
    const errorEl = document.getElementById('importValidationErrors');

    tbody.innerHTML = '';
    container.style.display = 'block';

    if (parsedImportRows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-3 text-muted">No valid rows could be parsed. Check the headers or format.</td></tr>';
        badge.textContent = '0 Valid Courses';
        badge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle';
        applyBtn.disabled = true;
    } else {
        const totalUnits = parsedImportRows.reduce((sum, r) => sum + r.units, 0);
        badge.textContent = `${parsedImportRows.length} Valid Courses (${totalUnits.toFixed(1)} units)`;
        badge.className = 'badge bg-success-subtle text-success border border-success-subtle';
        applyBtn.disabled = false;
        applyBtn.textContent = `Apply ${parsedImportRows.length} Subjects to Curriculum`;

        parsedImportRows.slice(0, 10).forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHtml(r.year_level)}</td>
                <td>${escapeHtml(r.semester)}</td>
                <td class="font-monospace fw-bold">${escapeHtml(r.subject_code)}</td>
                <td>${escapeHtml(r.descriptive_title)}</td>
                <td class="text-center">${r.units}</td>
                <td>${escapeHtml(r.subject_type)}</td>
                <td>${escapeHtml(r.prerequisites || '—')}</td>
            `;
            tbody.appendChild(tr);
        });

        if (parsedImportRows.length > 10) {
            const trMore = document.createElement('tr');
            trMore.innerHTML = `<td colspan="7" class="text-center text-muted fst-italic py-1">+ ${parsedImportRows.length - 10} more subjects...</td>`;
            tbody.appendChild(trMore);
        }
    }

    if (errors && errors.length > 0) {
        errorEl.style.display = 'block';
        errorEl.innerHTML = `<strong>Note:</strong> Skipped ${errors.length} invalid line(s). Missing code or title.`;
    } else {
        errorEl.style.display = 'none';
    }
}

function applyImportedSubjects() {
    if (parsedImportRows.length === 0) return;

    const isReplace = document.getElementById('importModeReplace').checked;
    if (isReplace) {
        subjectsList = [...parsedImportRows];
    } else {
        subjectsList.push(...parsedImportRows);
    }

    renderSubjectsTable();

    // Close modal
    const modalEl = document.getElementById('importSubjectsModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
}

document.addEventListener('DOMContentLoaded', function() {
    renderSubjectsTable();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
