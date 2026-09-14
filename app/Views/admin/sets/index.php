<?php
$isReadOnly = !empty($isReadOnly) || (strtolower((string) ($_SESSION['user']['role'] ?? '')) === 'dean');
$pageTitle = 'Sets';
$subtitle = $isReadOnly 
    ? 'Read-only view of academic class sections, cohorts, and student sets.' 
    : 'Manage academic class sections, cohorts, and batch-generate student sets.';
$headerActions = $isReadOnly 
    ? '<span class="badge bg-light text-secondary border px-2.5 py-1.5 fs-7 d-inline-flex align-items-center gap-1.5"><i class="bi bi-shield-lock"></i> Read-Only View</span>' 
    : '<button type="button" class="btn text-white d-inline-flex align-items-center gap-1.5" style="background-color: #2f4a86; border-color: #2f4a86;" data-bs-toggle="modal" data-bs-target="#createSetsModal"><i class="bi bi-plus-lg"></i> Create Sections</button>';
ob_start();
?>

<!-- Search, Filter & Statistics Bar -->
<div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between mb-4">
    <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1" style="max-width: 720px;">
        <div class="position-relative flex-grow-1" style="min-width: 220px;">
            <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 14px;"></i>
            <input type="text" 
                   id="setSearch" 
                   class="form-control ps-5" 
                   placeholder="Search by section, code, or program..." 
                   autocomplete="off">
        </div>

        <select class="form-select w-auto" id="programFilter" onchange="applyFilters()">
            <option value="ALL">All Programs</option>
            <?php foreach ($programs as $prog): ?>
                <option value="<?= htmlspecialchars($prog->program_abbrev) ?>"><?= htmlspecialchars($prog->program_abbrev) ?></option>
            <?php endforeach; ?>
        </select>

        <select class="form-select w-auto" id="yearFilter" onchange="applyFilters()">
            <option value="ALL">All Years</option>
            <option value="1">1st Year</option>
            <option value="2">2nd Year</option>
            <option value="3">3rd Year</option>
            <option value="4">4th Year</option>
        </select>
    </div>

    <div class="d-flex align-items-center gap-2">
        <?php if (!$isReadOnly): ?>
            <button type="button" id="bulkArchiveBtn" class="btn btn-outline-secondary btn-sm d-none align-items-center gap-1.5" onclick="submitBulkArchive()">
                <i class="bi bi-archive"></i> Archive Selected (<span id="selectedCount">0</span>)
            </button>
        <?php endif; ?>
        <div class="text-muted small fw-medium text-nowrap" id="setCounter">
            <?= count($sets) ?> <?= count($sets) === 1 ? 'section' : 'sections' ?>
        </div>
    </div>
</div>

<!-- Sets Table Card -->
<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="setsTable">
            <thead class="bg-white border-bottom">
                <tr>
                    <?php if (!$isReadOnly): ?>
                        <th class="py-2.5 px-3 text-center" style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onchange="toggleSelectAll(this)">
                        </th>
                    <?php endif; ?>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 11px;">Section Name</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Degree Program</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 110px; font-size: 11px;">Year Level</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 100px; font-size: 11px;">Code</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 140px; font-size: 11px;">Students</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 110px; font-size: 11px;">Status</th>
                    <?php if (!$isReadOnly): ?>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="width: 160px; font-size: 11px;">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (empty($sets) || $sets->isEmpty()): ?>
                    <tr id="emptySetsRow">
                        <td colspan="<?= $isReadOnly ? 6 : 8 ?>" class="p-0">
                            <?php
                            $icon = 'bi-collection';
                            $title = 'No sections found';
                            $message = $isReadOnly ? 'No sections found for the current academic term.' : 'No sections created for the current academic term yet. Click "Create Sections" to generate sections.';
                            include __DIR__ . '/../../components/empty-state.php';
                            ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($sets as $set): ?>
                        <?php
                            $progAbbrev = $set->program ? $set->program->program_abbrev : (explode('-', $set->name)[0] ?? '');
                            $progName = $set->program ? $set->program->program_name : '';
                            $ylInt = (int) $set->year_level;
                            $ylLabel = match($ylInt) {
                                1 => '1st Year',
                                2 => '2nd Year',
                                3 => '3rd Year',
                                4 => '4th Year',
                                default => "Year {$ylInt}",
                            };
                            $isActive = ($set->status ?? 'active') === 'active';
                        ?>
                        <tr class="set-row" 
                            data-program="<?= htmlspecialchars($progAbbrev) ?>" 
                            data-year="<?= $ylInt ?>"
                            data-search="<?= strtolower(htmlspecialchars($set->name . ' ' . $progAbbrev . ' ' . $progName . ' ' . $set->set_code)) ?>">
                            <?php if (!$isReadOnly): ?>
                                <td class="px-3 text-center">
                                    <input type="checkbox" class="form-check-input set-checkbox" value="<?= $set->id ?>" onchange="updateSelectedCount()">
                                </td>
                            <?php endif; ?>
                            <td class="py-3 px-3">
                                <span class="badge bg-light text-primary border font-monospace px-2.5 py-1 fw-bold fs-6">
                                    <?= htmlspecialchars($set->name) ?>
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($progName ?: $progAbbrev) ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($progAbbrev) ?></div>
                            </td>
                            <td class="py-3 px-3 text-center small text-secondary">
                                <?= $ylLabel ?>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="badge bg-white text-dark border font-monospace px-2 py-0.5">
                                    <?= htmlspecialchars($set->set_code ?: substr($set->name, -1)) ?>
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <?php $sCount = (int) ($set->students_count ?? 0); ?>
                                <span class="badge <?= $sCount > 0 ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-light text-muted border' ?> px-2.5 py-1">
                                    <i class="bi bi-people me-1"></i><?= $sCount ?> <?= $sCount === 1 ? 'student' : 'students' ?>
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <?php if ($isActive): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <?php if (!$isReadOnly): ?>
                                <td class="py-3 px-4 text-end">
                                    <div class="d-inline-flex align-items-center gap-1.5">
                                        <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#editSetModal<?= $set->id ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if ($isActive): ?>
                                            <form method="POST" action="<?= url('/admin/sets/' . $set->id . '/archive') ?>" class="d-inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Archive Section">
                                                    <i class="bi bi-archive"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="<?= url('/admin/sets/' . $set->id . '/restore') ?>" class="d-inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2" title="Restore Section">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>

                        <?php if (!$isReadOnly): ?>
                            <!-- Edit Set Modal -->
                            <div class="modal fade" id="editSetModal<?= $set->id ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <form method="POST" action="<?= url('/admin/sets/' . $set->id) ?>">
                                            <?= csrf_field() ?>
                                            <div class="modal-header border-bottom py-3 px-4">
                                                <h5 class="modal-title h6 fw-bold mb-0">Edit Section — <?= htmlspecialchars($set->name) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4 space-y-3">
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold text-dark">Program</label>
                                                    <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($progAbbrev . ' — ' . $progName) ?>" disabled>
                                                </div>
                                                <div class="row g-3 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label small fw-semibold text-dark">Year Level</label>
                                                        <input type="text" class="form-control bg-light" value="<?= $ylLabel ?>" disabled>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small fw-semibold text-dark">Section Code <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control font-monospace" name="set_code" value="<?= htmlspecialchars($set->set_code ?: substr($set->name, -1)) ?>" required style="text-transform: uppercase;">
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small fw-semibold text-dark">Status</label>
                                                    <select class="form-select" name="status">
                                                        <option value="active" <?= $isActive ? 'selected' : '' ?>>Active</option>
                                                        <option value="inactive" <?= !$isActive ? 'selected' : '' ?>>Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light py-3 px-4 d-flex justify-content-end gap-2">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-sm text-white" style="background-color: #2f4a86; border-color: #2f4a86;">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <tr id="emptySetsRow" style="display: none;">
                        <td colspan="<?= $isReadOnly ? 6 : 8 ?>" class="p-0">
                            <?php
                            $icon = 'bi-search';
                            $title = 'No sections found';
                            $message = 'No sections match your search and filter criteria.';
                            include __DIR__ . '/../../components/empty-state.php';
                            ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (!$isReadOnly): ?>
    <!-- Bulk Archive Form -->
    <form id="bulkArchiveForm" method="POST" action="<?= url('/admin/sets/bulk-archive') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="ids" id="bulkArchiveIds">
    </form>

    <!-- Create Sets Modal -->
    <div class="modal fade" id="createSetsModal" tabindex="-1" aria-labelledby="createSetsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="<?= url('/admin/sets') ?>" id="createSetsForm">
                    <?= csrf_field() ?>
                    <div class="modal-header border-bottom py-3 px-4">
                        <h5 class="modal-title h6 fw-bold mb-0" id="createSetsModalLabel">Create Sections</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-8">
                                <label class="form-label small fw-semibold text-dark">Program <span class="text-danger">*</span></label>
                                <select class="form-select" id="create_program_id" name="program_id" required onchange="updateLivePreview()">
                                    <option value="">-- Select Degree Program --</option>
                                    <?php foreach ($programs as $p): ?>
                                        <option value="<?= $p->id ?>" data-abbrev="<?= htmlspecialchars($p->program_abbrev) ?>">
                                            <?= htmlspecialchars($p->program_abbrev) ?> — <?= htmlspecialchars($p->program_name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold text-dark">Generation Mode</label>
                                <select class="form-select" id="create_mode" name="create_mode" onchange="toggleCreateMode(this.value)">
                                    <option value="all">All Selected Years</option>
                                    <option value="custom">Custom per Year</option>
                                </select>
                            </div>
                        </div>

                        <!-- Mode: All Selected Years -->
                        <div id="modeAllContainer">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-dark mb-2">Target Year Levels</label>
                                <div class="d-flex flex-wrap gap-3">
                                    <?php for ($y = 1; $y <= 4; $y++): ?>
                                        <div class="form-check">
                                            <input class="form-check-input year-checkbox" type="checkbox" name="selected_years[]" value="<?= $y ?>" id="yrCheck<?= $y ?>" checked onchange="updateLivePreview()">
                                            <label class="form-check-label small fw-medium" for="yrCheck<?= $y ?>">
                                                <?= $y === 1 ? '1st Year' : ($y === 2 ? '2nd Year' : ($y === 3 ? '3rd Year' : "{$y}th Year")) ?>
                                            </label>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-dark">Section Codes <span class="text-danger">*</span></label>
                                <input type="text" class="form-control font-monospace" id="create_set_codes" name="set_codes" placeholder="e.g. A, B, C or 1, 2" value="A, B" oninput="updateLivePreview()" style="text-transform: uppercase;">
                                <div class="form-text small text-muted">Separate multiple section codes with commas or spaces.</div>
                            </div>
                        </div>

                        <!-- Mode: Custom per Year -->
                        <div id="modeCustomContainer" style="display: none;">
                            <ul class="nav nav-pills mb-3" id="customYearPills" role="tablist">
                                <?php for ($y = 1; $y <= 4; $y++): ?>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link py-1.5 px-3 small <?= $y === 1 ? 'active' : '' ?>" id="pills-y<?= $y ?>-tab" data-bs-toggle="pill" data-bs-target="#pills-y<?= $y ?>" type="button" role="tab">
                                            <?= $y === 1 ? '1st Year' : ($y === 2 ? '2nd Year' : ($y === 3 ? '3rd Year' : "{$y}th Year")) ?>
                                        </button>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                            <div class="tab-content" id="customYearTabContent">
                                <?php for ($y = 1; $y <= 4; $y++): ?>
                                    <div class="tab-pane fade <?= $y === 1 ? 'show active' : '' ?>" id="pills-y<?= $y ?>" role="tabpanel">
                                        <label class="form-label small fw-semibold text-dark"><?= $y === 1 ? '1st' : ($y === 2 ? '2nd' : ($y === 3 ? '3rd' : "{$y}th")) ?> Year Section Codes</label>
                                        <input type="text" class="form-control font-monospace custom-year-code" data-year="<?= $y ?>" name="custom_codes[<?= $y ?>]" placeholder="e.g. A, B" oninput="updateLivePreview()" style="text-transform: uppercase;">
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <!-- Live Section Preview Box -->
                        <div class="p-3 bg-light rounded border mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-uppercase text-secondary" style="font-size: 11px;">Sections Preview</span>
                                <span class="badge bg-white text-dark border px-2 py-0.5" id="previewCountBadge">0 sections to generate</span>
                            </div>
                            <div id="previewBadgesContainer" class="d-flex flex-wrap gap-1.5" style="min-height: 38px;">
                                <span class="text-muted small">Select a program and specify section codes above to see preview.</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-3 px-4 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm text-white px-3" style="background-color: #2f4a86; border-color: #2f4a86;" id="createSetsSubmitBtn">
                            Create Sections
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
function toggleCreateMode(mode) {
    const allC = document.getElementById('modeAllContainer');
    const customC = document.getElementById('modeCustomContainer');
    if (allC) allC.style.display = mode === 'all' ? '' : 'none';
    if (customC) customC.style.display = mode === 'custom' ? '' : 'none';
    updateLivePreview();
}

function updateLivePreview() {
    const progSelect = document.getElementById('create_program_id');
    if (!progSelect) return;
    const selectedOpt = progSelect.selectedOptions[0];
    const abbrev = selectedOpt ? (selectedOpt.getAttribute('data-abbrev') || '') : '';
    const modeEl = document.getElementById('create_mode');
    const mode = modeEl ? modeEl.value : 'all';
    const container = document.getElementById('previewBadgesContainer');
    const badge = document.getElementById('previewCountBadge');

    if (!container || !badge) return;

    if (!abbrev) {
        container.innerHTML = '<span class="text-muted small">Select a degree program to preview generated sections.</span>';
        badge.textContent = '0 sections to generate';
        return;
    }

    const generated = [];

    if (mode === 'all') {
        const checkedYears = [];
        document.querySelectorAll('.year-checkbox:checked').forEach(cb => checkedYears.push(parseInt(cb.value)));
        const rawCodesEl = document.getElementById('create_set_codes');
        const rawCodes = rawCodesEl ? rawCodesEl.value.toUpperCase() : '';
        const codes = rawCodes.split(/[,\s]+/).map(s => s.trim()).filter(Boolean);

        checkedYears.sort().forEach(yl => {
            codes.forEach(c => {
                generated.push(`${abbrev}-${yl}${c}`);
            });
        });
    } else {
        document.querySelectorAll('.custom-year-code').forEach(input => {
            const yl = input.getAttribute('data-year');
            const codes = input.value.toUpperCase().split(/[,\s]+/).map(s => s.trim()).filter(Boolean);
            codes.forEach(c => {
                generated.push(`${abbrev}-${yl}${c}`);
            });
        });
    }

    badge.textContent = generated.length + (generated.length === 1 ? ' section to generate' : ' sections to generate');

    if (generated.length === 0) {
        container.innerHTML = '<span class="text-muted small">Enter section codes (e.g. A, B) to preview.</span>';
    } else {
        container.innerHTML = generated.map(name => `
            <span class="badge bg-white text-primary border font-monospace px-2.5 py-1.5 fw-semibold shadow-xs">
                ${name}
            </span>
        `).join('');
    }
}

function applyFilters() {
    const q = document.getElementById('setSearch').value.trim().toLowerCase();
    const prog = document.getElementById('programFilter').value;
    const year = document.getElementById('yearFilter').value;
    const rows = document.querySelectorAll('.set-row');
    const emptyRow = document.getElementById('emptySetsRow');
    let visibleCount = 0;

    rows.forEach(row => {
        const rowProg = row.getAttribute('data-program') || '';
        const rowYear = row.getAttribute('data-year') || '';
        const searchData = row.getAttribute('data-search') || '';

        const matchSearch = !q || searchData.includes(q);
        const matchProg = prog === 'ALL' || rowProg === prog;
        const matchYear = year === 'ALL' || rowYear === year;

        if (matchSearch && matchProg && matchYear) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const setCounter = document.getElementById('setCounter');
    if (setCounter) {
        setCounter.textContent = visibleCount + (visibleCount === 1 ? ' section' : ' sections');
    }
    if (emptyRow && rows.length > 0) {
        emptyRow.style.display = visibleCount === 0 ? '' : 'none';
    }
}

function toggleSelectAll(masterCheckbox) {
    document.querySelectorAll('.set-checkbox').forEach(cb => {
        const row = cb.closest('tr');
        if (row && row.style.display !== 'none') {
            cb.checked = masterCheckbox.checked;
        }
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    const checked = document.querySelectorAll('.set-checkbox:checked');
    const btn = document.getElementById('bulkArchiveBtn');
    const countSpan = document.getElementById('selectedCount');

    if (countSpan) {
        countSpan.textContent = checked.length;
    }
    if (btn) {
        if (checked.length > 0) {
            btn.classList.remove('d-none');
            btn.classList.add('d-inline-flex');
        } else {
            btn.classList.add('d-none');
            btn.classList.remove('d-inline-flex');
        }
    }
}

function submitBulkArchive() {
    const checked = document.querySelectorAll('.set-checkbox:checked');
    if (checked.length === 0) return;

    if (!confirm(`Are you sure you want to archive ${checked.length} selected sets?`)) {
        return;
    }

    const ids = Array.from(checked).map(cb => cb.value);
    const hiddenIds = document.getElementById('bulkArchiveIds');
    const form = document.getElementById('bulkArchiveForm');
    if (hiddenIds && form) {
        hiddenIds.value = ids.join(',');
        form.submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('setSearch');
    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }
    if (document.getElementById('create_program_id')) {
        updateLivePreview();
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
