// Faculty roster import: parse a class list, preview it, then bulk-enroll.
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('rosterImportModal');

    if (!modal) {
        return;
    }

    const dropzone = document.getElementById('rosterImportDropzone');
    const fileInput = document.getElementById('rosterFileInput');
    const statusAlert = document.getElementById('rosterImportStatus');
    const previewContainer = document.getElementById('rosterPreviewContainer');
    const previewBody = document.getElementById('rosterPreviewBody');
    const previewSummary = document.getElementById('rosterPreviewSummary');
    const previewBadge = document.getElementById('rosterPreviewBadge');
    const submitBtn = document.getElementById('rosterImportSubmit');

    const targetSetSelect = document.getElementById('rosterImportSetSelect');

    const importUrl = modal.dataset.importUrl;
    const rosterUrl = modal.dataset.rosterUrl;
    const csrfToken = modal.dataset.csrfToken;
    const subjectId = modal.dataset.subjectId;
    const termId = modal.dataset.termId;
    const semester = modal.dataset.semester;

    let parsedRows = [];

    if (targetSetSelect) {
        targetSetSelect.addEventListener('change', function() {
            if (parsedRows.length > 0) {
                renderPreview();
            }
        });
    }

    function icon(name) {
        const el = document.createElement('i');
        el.className = 'bi ' + name + ' me-1';
        return el;
    }

    /* Builds an alert using textContent so spreadsheet/server values can never inject markup. */
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
                    studentNumber: findColumn(headers, ['student number', 'student id', 'student_number', 'id']),
                    firstName: findColumn(headers, ['first name', 'given name', 'firstname', 'first_name']),
                    lastName: findColumn(headers, ['last name', 'surname', 'lastname', 'family name', 'last_name']),
                    email: findColumn(headers, ['email', 'email address', 'e-mail']),
                    section: findColumn(headers, ['class section', 'class_section', 'section', 'set', 'set_name', 'set name']),
                    status: findColumn(headers, ['enrollment status', 'enrollment_status', 'status', 'student status', 'student_status']),
                    yearLevel: findColumn(headers, ['year level', 'year_level', 'year', 'level'])
                };

                if (colMap.studentNumber === -1 && colMap.email === -1) {
                    showError('No "Student Number" or "Email" column was found. Download the template and try again.');
                    return;
                }

                parsedRows = [];

                for (let r = 1; r < rawRows.length; r++) {
                    const row = rawRows[r];

                    if (!row || row.every(function(cell) { return cell === '' || cell == null; })) {
                        continue;
                    }

                    const entry = {
                        student_number: cell(row, colMap.studentNumber),
                        first_name: cell(row, colMap.firstName),
                        last_name: cell(row, colMap.lastName),
                        email: cell(row, colMap.email),
                        section: cell(row, colMap.section),
                        status: cell(row, colMap.status),
                        year_level: cell(row, colMap.yearLevel)
                    };

                    const issues = [];
                    if (!entry.student_number && !entry.email) {
                        issues.push('Missing student ID and email');
                    } else if (entry.email && entry.email.indexOf('@') === -1) {
                        issues.push('Invalid email address');
                    }

                    entry._issues = issues;
                    parsedRows.push(entry);
                }

                if (parsedRows.length === 0) {
                    showError('No student rows were found in this file.');
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
        const selectedSetOptionText = (targetSetSelect && targetSetSelect.value && targetSetSelect.selectedIndex >= 0)
            ? targetSetSelect.options[targetSetSelect.selectedIndex].text
            : '';

        parsedRows.forEach(function(entry, index) {
            const tr = document.createElement('tr');
            const valid = entry._issues.length === 0;
            const name = [entry.last_name, entry.first_name].filter(Boolean).join(', ');
            const displaySection = entry.section || selectedSetOptionText || '—';
            const displayStatus = entry.status || (displaySection !== '—' ? 'Regular' : 'Irregular');

            if (!valid) {
                tr.classList.add('table-danger');
            }

            const cells = [
                { text: String(index + 1), className: 'py-1 px-2 text-muted text-center' },
                { text: entry.student_number || '—', className: 'py-1 px-2 font-monospace text-nowrap', dim: !entry.student_number },
                { text: name || '—', className: 'py-1 px-2 text-nowrap', dim: !name },
                { text: entry.email || '—', className: 'py-1 px-2 text-nowrap text-secondary', dim: !entry.email },
                { text: displaySection, className: 'py-1 px-2 font-monospace text-nowrap', dim: displaySection === '—' },
                { text: displayStatus, className: 'py-1 px-2 text-nowrap' }
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
        previewBadge.textContent = validCount > 0 ? validCount + ' ready to enroll' : 'No valid records';
        previewBadge.className = 'badge ' + (validCount > 0 ? 'bg-primary text-white' : 'bg-danger text-white');

        setAlert('success', 'bi-check2-circle', 'Parsed ' + parsedRows.length + ' records. Review the preview before submitting.');

        previewContainer.classList.remove('d-none');
        submitBtn.disabled = (validCount === 0);
        submitBtn.textContent = 'Enroll ' + validCount + ' Students';
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

        const selectedSetId = (targetSetSelect && targetSetSelect.value) ? Number(targetSetSelect.value) : 0;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Enrolling...';

        fetch(importUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({
                _token: csrfToken,
                subject_id: Number(subjectId),
                academic_term_id: Number(termId),
                semester: semester,
                set_id: selectedSetId,
                students: payload
            })
        })
            .then(function(res) {
                return res.json().then(function(data) {
                    return { ok: res.ok, data: data };
                });
            })
            .then(function(result) {
                if (result.ok && result.data.success) {
                    setAlert('success', 'bi-check2-circle', result.data.message || 'Roster import complete.');
                    submitBtn.textContent = 'Done';
                    setTimeout(function() {
                        window.location.href = rosterUrl + '?subject_id=' + subjectId + '&semester=' + semester;
                    }, 1200);
                    return;
                }

                const errors = Array.isArray(result.data.errors) ? result.data.errors : [];
                const errorLines = errors.map(function(e) {
                    return 'Row ' + e.row + (e.name ? ' (' + e.name + ')' : '') + ': ' + e.message;
                });

                showError(result.data.message || 'The roster import failed.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Retry import';

                if (errorLines.length) {
                    setAlert('danger', 'bi-exclamation-octagon', result.data.message || 'The roster import failed.', errorLines);
                }
            })
            .catch(function(err) {
                showError('Network error: ' + err.message);
                submitBtn.disabled = false;
                submitBtn.textContent = 'Retry import';
            });
    });
});
