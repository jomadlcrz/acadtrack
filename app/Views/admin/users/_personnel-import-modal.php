<!-- Modal: Batch Faculty / Dean account import -->
<div class="modal fade" id="personnelImportModal" tabindex="-1" aria-labelledby="personnelImportModalLabel" aria-hidden="true"
    data-import-url="<?= url('/admin/users/import-personnel') ?>"
    data-csrf-token="<?= htmlspecialchars(csrf_token()) ?>"
    data-role="<?= htmlspecialchars($activeRole) ?>"
    data-list-url="<?= url('/admin/faculty') ?>">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="personnelImportModalLabel">
                        Batch <?= htmlspecialchars($activeRole) ?> Registration
                    </h5>
                    <small class="text-muted" style="font-size: 12px;">Upload a personnel spreadsheet (.xlsx, .xls, or .csv) to create multiple <?= htmlspecialchars(strtolower($activeRole)) ?> accounts.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 pb-3 border-bottom">
                    <div>
                        <div class="fw-semibold text-dark small">Spreadsheet Template Format</div>
                        <div class="text-muted" style="font-size: 12px;">Required columns: First Name, Last Name, Email, Department</div>
                    </div>
                    <a href="<?= url('/admin/users/import-template?role=' . urlencode($activeRole)) ?>" class="btn btn-outline-secondary btn-sm text-nowrap d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-download"></i> Download Template (.csv)
                    </a>
                </div>

                <div id="personnelImportDropzone" class="border border-2 border-dashed rounded-3 p-4 text-center mb-3" style="border-color: #cbd5e1 !important; background-color: #f8fafc; cursor: pointer; transition: all 0.2s ease;">
                    <i class="bi bi-file-earmark-spreadsheet text-primary d-block mb-2" style="font-size: 32px;"></i>
                    <div class="fw-semibold text-dark mb-1">Click to select or drag and drop your spreadsheet here</div>
                    <div class="text-muted small">Supports Excel (.xlsx, .xls) and Comma-Separated Values (.csv)</div>
                    <input type="file" id="personnelImportFileInput" accept=".xlsx,.xls,.csv" class="d-none">
                </div>

                <div id="personnelImportStatus" class="alert d-none py-2 px-3 small mb-3"></div>

                <div id="personnelPreviewContainer" class="d-none">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="fw-semibold text-dark small" id="personnelPreviewSummary">
                            Verification Preview: 0 records found
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary" id="personnelPreviewBadge">Ready to import</span>
                    </div>

                    <div class="table-responsive border rounded-2" style="max-height: 320px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 11.5px;">
                            <thead class="bg-light sticky-top border-bottom text-secondary text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.3px;">
                                <tr>
                                    <th class="py-1.5 px-2 text-center" style="width: 40px;">#</th>
                                    <th class="py-1.5 px-2">Full Name</th>
                                    <th class="py-1.5 px-2">Email Address</th>
                                    <th class="py-1.5 px-2">Department</th>
                                    <th class="py-1.5 px-2 text-center" style="width: 90px;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="personnelPreviewBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="personnelImportSubmit" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5" disabled style="background-color: #1e3a8a; border-color: #1e3a8a;">
                    <i class="bi bi-cloud-arrow-up"></i> Create Verified Accounts
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= url('/assets/js/xlsx.full.min.js') ?>"></script>
<script src="<?= url('/assets/js/pages/admin-personnel-import.js') ?>"></script>