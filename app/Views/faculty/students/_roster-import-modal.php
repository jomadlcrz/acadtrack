<?php
/**
 * Modal partial: bulk class roster import.
 *
 * Expects:
 *   - $subjectId (int)     currently selected course offering
 *   - $academicTerm (array) active term row
 *   - $selectedSemester (string)
 */
?>
<div class="modal fade" id="rosterImportModal" tabindex="-1" aria-labelledby="rosterImportModalLabel" aria-hidden="true"
     data-csrf-token="<?= csrf_token() ?>"
     data-import-url="<?= url('/faculty/students/import-roster') ?>"
     data-template-url="<?= url('/faculty/students/roster-template') ?>"
     data-subject-id="<?= (int) $subjectId ?>"
     data-term-id="<?= (int) ($academicTerm['id'] ?? 0) ?>"
     data-semester="<?= htmlspecialchars((string)($selectedSemester ?? '1')) ?>"
     data-roster-url="<?= url('/faculty/students') ?>">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="rosterImportModalLabel">Import Class Roster</h5>
                    <small class="text-muted">Upload your class list to add many students to this course at once.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 pb-3 border-bottom">
                    <div>
                        <div class="fw-semibold">Spreadsheet Template Format</div>
                        <div class="text-muted small">Required columns: Student Number or Email. Names are optional and used only for the preview.</div>
                    </div>
                    <a href="<?= url('/faculty/students/roster-template') ?>" class="btn btn-outline-secondary btn-sm text-nowrap d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-download"></i> Download template (.csv)
                    </a>
                </div>

                <div class="row g-2 align-items-center mb-3">
                    <div class="col-md-6 col-lg-5">
                        <label for="rosterImportSetSelect" class="form-label small fw-semibold mb-1">Target section / set <span class="text-muted fw-normal">(Optional)</span></label>
                        <select id="rosterImportSetSelect" class="form-select form-select-sm">
                            <option value="">Auto-detect from file (or unassigned)</option>
                            <?php foreach (($sets ?? []) as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= ((int)($selectedSet ?? 0) === (int)$s['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['set_name'] ?? $s['name'] ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text small" style="font-size: 11px;">If your spreadsheet has a section column (e.g. BSIT-1A), it will be used automatically. Otherwise, choose a default section here.</div>
                    </div>
                </div>

                <div id="rosterImportDropzone" class="border border-2 border-dashed rounded-3 p-4 text-center mb-3" style="border-color: #cbd5e1 !important; background-color: #f8fafc; cursor: pointer;">
                    <i class="bi bi-file-earmark-spreadsheet text-primary d-block mb-2"></i>
                    <div class="fw-semibold mb-1">Click to select or drag and drop your class list here</div>
                    <div class="text-muted small">Supports Excel (.xlsx, .xls) and Comma-Separated Values (.csv)</div>
                    <input type="file" id="rosterFileInput" accept=".xlsx,.xls,.csv" class="d-none">
                </div>

                <div id="rosterImportStatus" class="alert d-none py-2 px-3 small mb-3"></div>

                <div id="rosterPreviewContainer" class="d-none">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="fw-semibold" id="rosterPreviewSummary">Verification preview: 0 records found</div>
                        <span class="badge bg-secondary-subtle text-secondary" id="rosterPreviewBadge">Ready to import</span>
                    </div>

                    <div class="table-responsive border rounded-2" style="max-height: 320px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="bg-light sticky-top border-bottom text-secondary text-uppercase" style="font-size: 11px;">
                                <tr>
                                    <th class="text-center" style="width: 45px;">#</th>
                                    <th>Student ID</th>
                                    <th>Full name</th>
                                    <th>Email address</th>
                                    <th>Section</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 100px;">Verification</th>
                                </tr>
                            </thead>
                            <tbody id="rosterPreviewBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="rosterImportSubmit" class="btn btn-primary d-inline-flex align-items-center gap-2" disabled>
                    <i class="bi bi-cloud-arrow-up"></i> Enroll verified students
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= url('/assets/js/xlsx.full.min.js') ?>"></script>
<script src="<?= url('/assets/js/pages/faculty-roster-import.js') ?>"></script>
