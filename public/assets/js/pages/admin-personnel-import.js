// Faculty / Dean account import: parse a personnel sheet, preview it, then bulk-create accounts.
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('personnelImportModal');

    if (!modal) {
        return;
    }

    const dropzone = document.getElementById('personnelImportDropzone');
    const fileInput = document.getElementById('personnelImportFileInput');
    const statusAlert = document.getElementById('personnelImportStatus');
    const previewContainer = document.getElementById('personnelPreviewContainer');
    const previewBody = document.getElementById('personnelPreviewBody');
    const previewSummary = document.getElementById('personnelPreviewSummary');
    const previewBadge = document.getElementById('personnelPreviewBadge');
    const submitBtn = document.getElementById('personnelImportSubmit');

    const importUrl = modal.dataset.importUrl;
    const listUrl = modal.dataset.listUrl;
    const csrfToken = modal.dataset.csrfToken;
    const role = modal.dataset.role;

    let parsedRows = [];

    function icon(name) {
        const el = document.createElement('i');
        el.className = 'bi ' + name + ' me-1';
        return el;
    }

    /* Builds an alert with textContent so spreadsheet/server values can never inject markup. */
    function setAlert(variant, iconName, message, extraList) {
        statusAlert.className = 'alert alert-' + variant + ' py-2 px-3 small mb-3';
        statusAlert.textContent = '';

        if (iconName) {
            statusAlert.appendChild(icon(iconName));
        }

        statusAlert.appendChild(document.createTextNode(message));

        if (extraList && extraList.length) {
            const list = document.createElement('ul');
            list.className = 'mb-0 mt-1 ps-3';

            extraList.forEach(function(item) {
                const li = document.createElement('li');
                li.textContent = item;
                list.appendChild(li);
            });

            statusAlert.appendChild(list);
        }

        statusAlert.classList.remove('d-none');
    }

    function showError(message) {
        setAlert('danger', 'bi-exclamation-octagon', message);
        submitBtn.disabled = true;
    }

    function findColumn(headers, aliases) {
        for (let i = 0; i < headers.length; i++) {
            for (let a = 0; a < aliases.length; a++) {
                if (headers[i] === aliases[a] || headers[i].indexOf(aliases[a]) !== -1) {
                    return i;
                }
            }
        }
        return -1;
    }

    function cell(row, index) {
        return index === -1 ? '' : String(row[index] == null ? '' : row[index]).trim();
    }

    function processFile(file) {
        setAlert('info', 'bi-hourglass-split', 'Processing file: ' + file.name + '...');

        const reader = new FileReader();

        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const worksheet = workbook.Sheets[workbook.SheetNames[0]];
                const rawRows = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

                if (!rawRows || rawRows.length < 2) {
                    showError('The file contains no data rows.');
                    return;
                }

                const headers = rawRows[0].map(function(h) { return String(h || '').trim().toLowerCase(); });
                const colMap = {
                    firstName: findColumn(headers, ['first name', 'given name', 'firstname', 'first_name']),
                    lastName: findColumn(headers, ['last name', 'surname', 'lastname', 'family name', 'last_name']),
                    email: findColumn(headers, ['email', 'email address', 'e-mail']),
                    department: findColumn(headers, ['department', 'dept', 'college', 'department name'])
                };

                if (colMap.firstName === -1 || colMap.lastName === -1 || colMap.email === -1 || colMap.department === -1) {
                    showError('This sheet is missing one of the required columns: First Name, Last Name, Email, Department. Download the template and try again.');
                    return;
                }

                parsedRows = [];

                for (let r = 1; r < rawRows.length; r++) {
                    const row = rawRows[r];

                    if (!row || row.every(function(value) { return value === '' || value == null; })) {
                        continue;
                    }

                    const entry = {
                        first_name: cell(row, colMap.firstName),
                        last_name: cell(row, colMap.lastName),
                        email: cell(row, colMap.email),
                        department: cell(row, colMap.department)
                    };

                    const issues = [];
                    if (!entry.first_name) issues.push('Missing first name');
                    if (!entry.last_name) issues.push('Missing last name');
                    if (!entry.email || entry.email.indexOf('@') === -1) issues.push('Invalid email address');
                    if (!entry.department) issues.push('Missing department');

                    entry._issues = issues;
                    parsedRows.push(entry);
                }

                if (parsedRows.length === 0) {
                    showError('No personnel rows were found in this file.');
                    return;
                }

                renderPreview();
            } catch (err) {
                showError('Failed to parse the file: ' + err.message);
            }
        };

        reader.readAsArrayBuffer(file);
    }

    function renderPreview() {
        previewBody.textContent = '';

        parsedRows.forEach(function(entry, index) {
            const tr = document.createElement('tr');
            const valid = entry._issues.length === 0;
            const name = [entry.last_name, entry.first_name].filter(Boolean).join(', ');

            if (!valid) {
                tr.classList.add('table-danger');
            }

            const cells = [
                { text: String(index + 1), className: 'py-1 px-2 text-muted text-center' },
                { text: name || '—', className: 'py-1 px-2 text-nowrap', dim: !name },
                { text: entry.email || '—', className: 'py-1 px-2 text-nowrap text-secondary', dim: !entry.email },
                { text: entry.department || '—', className: 'py-1 px-2 text-nowrap', dim: !entry.department }
            ];

            cells.forEach(function(cellDef) {
                const td = document.createElement('td');
                td.className = cellDef.className;
                if (cellDef.dim) {
                    td.classList.add('text-muted');
                }
                td.textContent = cellDef.text;
                tr.appendChild(td);
            });

            const statusTd = document.createElement('td');
            statusTd.className = 'py-1 px-2 text-center text-nowrap';

            const badge = document.createElement('span');
            if (valid) {
                badge.className = 'badge bg-success-subtle text-success border border-success-subtle';
                badge.appendChild(icon('bi-check'));
                badge.appendChild(document.createTextNode('Ready'));
            } else {
                badge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle';
                badge.title = entry._issues.join(', ');
                badge.appendChild(icon('bi-x'));
                badge.appendChild(document.createTextNode('Check'));
            }

            statusTd.appendChild(badge);
            tr.appendChild(statusTd);
            previewBody.appendChild(tr);
        });

        const validCount = parsedRows.filter(function(e) { return e._issues.length === 0; }).length;
        const issueCount = parsedRows.length - validCount;

        previewSummary.textContent = 'Found ' + parsedRows.length + ' records (' + validCount + ' ready, ' + issueCount + ' need attention)';
        previewBadge.textContent = validCount > 0 ? validCount + ' ready to create' : 'No valid records';
        previewBadge.className = 'badge ' + (validCount > 0 ? 'bg-primary text-white' : 'bg-danger text-white');

        setAlert('success', 'bi-check2-circle', 'Parsed ' + parsedRows.length + ' records. Review the preview before submitting.');

        previewContainer.classList.remove('d-none');
        submitBtn.disabled = (validCount === 0);
        submitBtn.textContent = 'Create ' + validCount + ' ' + role + ' Accounts';
    }

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

    submitBtn.addEventListener('click', function() {
        const payload = parsedRows.filter(function(e) { return e._issues.length === 0; });

        if (payload.length === 0) {
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Creating accounts...';

        fetch(importUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({
                _token: csrfToken,
                role: role,
                personnel: payload
            })
        })
            .then(function(res) {
                return res.json().then(function(data) {
                    return { ok: res.ok, data: data };
                });
            })
            .then(function(result) {
                if (result.ok && result.data.success) {
                    setAlert('success', 'bi-check2-circle', result.data.message || 'Import complete.');
                    submitBtn.textContent = 'Done';
                    setTimeout(function() {
                        window.location.href = listUrl;
                    }, 1200);
                    return;
                }

                const errors = Array.isArray(result.data.errors) ? result.data.errors : [];
                const errorLines = errors.map(function(e) {
                    return 'Row ' + e.row + (e.name ? ' (' + e.name + ')' : '') + ': ' + e.message;
                });

                showError(result.data.message || 'The import failed.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Retry import';

                if (errorLines.length) {
                    setAlert('danger', 'bi-exclamation-octagon', result.data.message || 'The import failed.', errorLines);
                }
            })
            .catch(function(err) {
                showError('Network error: ' + err.message);
                submitBtn.disabled = false;
                submitBtn.textContent = 'Retry import';
            });
    });
});