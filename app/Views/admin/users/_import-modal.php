<!-- Modal: Batch Excel / CSV Import -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="importExcelModalLabel">
                        Batch Student Registration
                    </h5>
                    <small class="text-muted" style="font-size: 12px;">Upload a student roster spreadsheet (.xlsx, .xls, or .csv) to register multiple accounts.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
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
                <div id="indexImportDropzone" class="border border-2 border-dashed rounded-3 p-4 text-center mb-3" style="border-color: #cbd5e1 !important; background-color: #f8fafc; cursor: pointer; transition: all 0.2s ease;">
                    <i class="bi bi-file-earmark-excel text-primary d-block mb-2" style="font-size: 32px;"></i>
                    <div class="fw-semibold text-dark mb-1">Click to select or drag and drop your spreadsheet here</div>
                    <div class="text-muted small">Supports Excel (.xlsx, .xls) and Comma-Separated Values (.csv)</div>
                    <input type="file" id="indexExcelFileInput" accept=".xlsx,.xls,.csv" class="d-none">
                </div>

                <!-- Progress / Error Banner -->
                <div id="indexImportStatusAlert" class="alert d-none py-2 px-3 small mb-3"></div>

                <!-- Step 2: Parsed Rows Preview Table -->
                <div id="indexPreviewContainer" class="d-none">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="fw-semibold text-dark small" id="indexPreviewSummary">
                            Verification Preview: 0 records found
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary" id="indexPreviewBadge">Ready to import</span>
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
                            <tbody id="indexPreviewTableBody">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="indexBtnSubmitImport" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5" disabled style="background-color: #1e3a8a; border-color: #1e3a8a;">
                    <i class="bi bi-cloud-arrow-up"></i> Register Verified Records
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= url('/assets/js/xlsx.full.min.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var dropzone = document.getElementById('indexImportDropzone');
    var fileInput = document.getElementById('indexExcelFileInput');
    var previewContainer = document.getElementById('indexPreviewContainer');
    var previewTableBody = document.getElementById('indexPreviewTableBody');
    var previewSummary = document.getElementById('indexPreviewSummary');
    var previewBadge = document.getElementById('indexPreviewBadge');
    var btnSubmitImport = document.getElementById('indexBtnSubmitImport');
    var statusAlert = document.getElementById('indexImportStatusAlert');
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

            var csrfToken = '<?= csrf_token() ?>';

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
                        window.location.reload();
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
