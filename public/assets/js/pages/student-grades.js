document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('classSearchInput');
    const entriesSelect = document.getElementById('classEntriesSelect');
    const tableBody = document.querySelector('#classesTable tbody');
    const showingStartEl = document.getElementById('classShowingStart');
    const showingEndEl = document.getElementById('classShowingEnd');
    const totalCountEl = document.getElementById('classTotalCount');
    const prevBtn = document.getElementById('classPrevBtn');
    const nextBtn = document.getElementById('classNextBtn');
    const pageIndicator = document.getElementById('classCurrentPage');
    const modalEl = document.getElementById('classGradeModal');

    if (!tableBody) return;

    const rows = Array.from(tableBody.querySelectorAll('tr.class-row'));
    let filteredRows = [...rows];
    let currentPage = 1;
    let pageSize = entriesSelect ? parseInt(entriesSelect.value, 10) || 10 : 10;

    function renderTable() {
        const total = filteredRows.length;
        const totalPages = pageSize === 0 ? 1 : Math.max(1, Math.ceil(total / pageSize));

        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        const startIndex = pageSize === 0 ? 0 : (currentPage - 1) * pageSize;
        const endIndex = pageSize === 0 ? total : Math.min(startIndex + pageSize, total);

        rows.forEach(row => {
            row.style.display = 'none';
        });

        filteredRows.forEach((row, idx) => {
            if (idx >= startIndex && idx < endIndex) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Update counts
        if (showingStartEl) showingStartEl.textContent = total > 0 ? (startIndex + 1).toString() : '0';
        if (showingEndEl) showingEndEl.textContent = endIndex.toString();
        if (totalCountEl) totalCountEl.textContent = total.toString();

        // Update pagination buttons
        if (pageIndicator) pageIndicator.textContent = currentPage.toString();
        if (prevBtn) {
            prevBtn.disabled = currentPage <= 1;
            prevBtn.classList.toggle('disabled', currentPage <= 1);
        }
        if (nextBtn) {
            nextBtn.disabled = currentPage >= totalPages;
            nextBtn.classList.toggle('disabled', currentPage >= totalPages);
        }

        // Empty state row if no matches
        let noMatchRow = tableBody.querySelector('.no-match-row');
        if (total === 0) {
            if (!noMatchRow) {
                noMatchRow = document.createElement('tr');
                noMatchRow.className = 'no-match-row';
                noMatchRow.innerHTML = '<td colspan="5" class="text-center py-4 text-muted small"><i class="bi bi-search me-1"></i> No matching classes found.</td>';
                tableBody.appendChild(noMatchRow);
            }
            noMatchRow.style.display = '';
        } else if (noMatchRow) {
            noMatchRow.style.display = 'none';
        }
    }

    function applyFilter() {
        const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
        filteredRows = rows.filter(row => {
            const target = (row.getAttribute('data-search-target') || row.textContent).toLowerCase();
            return target.includes(query);
        });
        currentPage = 1;
        renderTable();
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilter);
    }

    if (entriesSelect) {
        entriesSelect.addEventListener('change', function () {
            pageSize = this.value === 'all' ? 0 : parseInt(this.value, 10) || 10;
            currentPage = 1;
            renderTable();
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function (e) {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                renderTable();
            }
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function (e) {
            e.preventDefault();
            const totalPages = pageSize === 0 ? 1 : Math.max(1, Math.ceil(filteredRows.length / pageSize));
            if (currentPage < totalPages) {
                currentPage++;
                renderTable();
            }
        });
    }

    // Modal populate handler
    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('.view-grade-btn');
        if (!trigger || !modalEl) return;

        try {
            const dataStr = trigger.getAttribute('data-class');
            if (!dataStr) return;
            const data = JSON.parse(dataStr);

            const modalCode = document.getElementById('modalSubjectCode');
            const modalTitle = document.getElementById('modalSubjectTitle');
            const modalInstructor = document.getElementById('modalInstructor');
            const modalUnits = document.getElementById('modalUnits');
            const modalNature = document.getElementById('modalNature');
            const modalPeriodsBody = document.getElementById('modalPeriodsBody');
            const modalAverage = document.getElementById('modalComputedAverage');
            const modalStatus = document.getElementById('modalStatusBadge');
            const modalNotice = document.getElementById('modalStatusNotice');

            if (modalCode) modalCode.textContent = data.subject_code || '';
            if (modalTitle) modalTitle.textContent = data.subject_name || '';
            if (modalInstructor) modalInstructor.textContent = data.instructor || 'TBA';
            if (modalUnits) modalUnits.textContent = (data.units || 3) + ' Units';
            if (modalNature) modalNature.textContent = data.nature || 'Lecture';

            // Populate periods
            if (modalPeriodsBody) {
                modalPeriodsBody.innerHTML = '';
                const standardPeriods = ['Prelim', 'Midterm', 'Semi-Final', 'Final'];
                const periodEntries = Object.entries(data.periods || {});

                // Combine standard periods with any additional periods from database
                const periodKeys = standardPeriods.slice();
                periodEntries.forEach(([key]) => {
                    if (!periodKeys.includes(key)) {
                        periodKeys.push(key);
                    }
                });

                let hasAnyScore = false;
                periodKeys.forEach(period => {
                    let score = null;
                    if (data.periods && data.periods[period] !== undefined) {
                        score = data.periods[period];
                    } else if (period === 'Prelim' && data.periods && data.periods['Preliminary'] !== undefined) {
                        score = data.periods['Preliminary'];
                    } else if (period === 'Semi-Final' && data.periods && data.periods['Semi-final'] !== undefined) {
                        score = data.periods['Semi-final'];
                    }

                    const isNumeric = score !== null && score !== '' && !isNaN(Number(score));
                    if (isNumeric) hasAnyScore = true;

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="px-3 py-2.5 fw-semibold text-dark">${escapeHtml(period)}</td>
                        <td class="px-3 py-2.5 text-center font-monospace ${isNumeric ? 'fw-bold text-dark' : 'text-muted'}">
                            ${isNumeric ? Number(score).toFixed(2) : '—'}
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            ${isNumeric
                                ? '<span class="badge bg-success-subtle text-success border border-success-subtle">Published</span>'
                                : '<span class="badge bg-light text-muted border">In progress</span>'}
                        </td>
                    `;
                    modalPeriodsBody.appendChild(tr);
                });

                if (!hasAnyScore && periodKeys.length === 0) {
                    modalPeriodsBody.innerHTML = '<tr><td colspan="3" class="text-center py-3 text-muted small">No periodic grades recorded yet.</td></tr>';
                }
            }

            // Overall computed average
            if (modalAverage) {
                if (data.computed_average !== null && data.computed_average !== undefined) {
                    modalAverage.textContent = Number(data.computed_average).toFixed(2);
                } else {
                    modalAverage.textContent = '—';
                }
            }

            // Status badge
            if (modalStatus) {
                if (data.status === 'Passed') {
                    modalStatus.className = 'badge bg-success text-white px-2.5 py-1.5';
                    modalStatus.textContent = 'Passed';
                } else if (data.status === 'Failed') {
                    modalStatus.className = 'badge bg-danger text-white px-2.5 py-1.5';
                    modalStatus.textContent = 'Failed';
                } else {
                    modalStatus.className = 'badge bg-secondary text-white px-2.5 py-1.5';
                    modalStatus.textContent = 'In progress';
                }
            }

            // Notice
            if (modalNotice) {
                if (data.status === 'Passed' || data.status === 'Failed') {
                    modalNotice.textContent = 'Official finalized record confirmed by academic administration.';
                    modalNotice.className = 'text-success small mb-0';
                } else {
                    modalNotice.textContent = 'Periodic marks remain confidential until official approval and finalization.';
                    modalNotice.className = 'text-muted small mb-0';
                }
            }

            const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
            modalInstance.show();
        } catch (err) {
            console.error('Error opening grade details modal:', err);
        }
    });

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Initial render
    renderTable();
});
