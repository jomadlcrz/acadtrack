<?php
$pageTitle = 'Program Curricula';
$subtitle = 'Manage degree programs, curriculum frameworks, and subject sequences.';
$headerActions = '<a href="' . url('/admin/program-curricula/new') . '" class="btn text-white d-inline-flex align-items-center gap-1.5" style="background-color: #2f4a86; border-color: #2f4a86;"><i class="bi bi-plus-lg"></i> New Curriculum</a>';
ob_start();
?>

<!-- Print-only header -->
<div class="d-none d-print-block mb-4">
    <h2 class="h4 mb-1"><?= htmlspecialchars($selectedProgram ? $selectedProgram->program_name : 'Program Curriculum') ?></h2>
    <div class="text-muted small">
        Program Code: <?= htmlspecialchars($selectedProgram->program_abbrev ?? '') ?> | 
        Version: <?= htmlspecialchars($curriculum->version ?? '2026-2027') ?> | 
        Total Units: <?= $totalUnits ?> | 
        Printed on <?= date('F j, Y') ?>
    </div>
    <hr>
</div>

<?php if (empty($programs) || $programs->isEmpty()): ?>
    <div class="card shadow-sm border-0 text-center py-5" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
        <div class="card-body py-5">
            <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-3 text-primary" style="width: 56px; height: 56px; background-color: #eff6ff;">
                <i class="bi bi-book fs-3" style="color: #2f4a86;"></i>
            </div>
            <h3 class="h5 fw-bold text-dark mb-2">No program curricula yet</h3>
            <p class="text-muted small mb-4" style="max-width: 420px; margin-inline: auto;">
                Create the first program and its curriculum to start organizing subjects, units, and academic pathways.
            </p>
            <a href="<?= url('/admin/program-curricula/new') ?>" class="btn text-white" style="background-color: #2f4a86; border-color: #2f4a86;">
                <i class="bi bi-plus-lg me-1"></i> Create First Curriculum
            </a>
        </div>
    </div>
<?php else: ?>

    <!-- Program Selector & Header Card -->
    <div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
        <div class="p-4 border-bottom bg-white">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="programSelect" class="form-label text-uppercase text-secondary fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">
                        Selected Program
                    </label>
                    <select id="programSelect" class="form-select fw-medium" onchange="window.location.href = '<?= url('/admin/program-curricula') ?>?program=' + encodeURIComponent(this.value);">
                        <?php foreach ($programs as $p): ?>
                            <option value="<?= htmlspecialchars($p->program_abbrev) ?>" <?= $p->program_abbrev === $selectedAbbrev ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p->program_abbrev) ?> — <?= htmlspecialchars($p->program_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-8 d-flex flex-wrap justify-content-md-end align-items-center gap-2">
                    <div class="position-relative flex-grow-1 flex-sm-grow-0" style="min-width: 220px;">
                        <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 12px; font-size: 13px;"></i>
                        <input type="text" id="curriculumSubjectSearch" class="form-control form-control-sm ps-4" placeholder="Search subjects..." autocomplete="off">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1.5" onclick="window.print();">
                        <i class="bi bi-printer"></i> Print
                    </button>
                    <?php if ($selectedProgram): ?>
                        <a href="<?= url('/admin/program-curricula/' . $selectedProgram->id . '/export-csv') ?>" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1.5">
                            <i class="bi bi-download"></i> CSV / Excel
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($selectedProgram): ?>
            <!-- Program Metadata Banner -->
            <div class="px-4 py-3 bg-light border-bottom">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h2 class="h5 fw-bold text-dark mb-0">
                                <?= htmlspecialchars($selectedProgram->program_name) ?> (<?= htmlspecialchars($selectedProgram->program_abbrev) ?>)
                            </h2>
                        </div>
                        <div class="text-secondary small mt-1">
                            <?= htmlspecialchars($selectedProgram->department->name ?? 'Academic Department') ?>
                            <?php if (!empty($selectedProgram->department->code)): ?>
                                (<?= htmlspecialchars($selectedProgram->department->code) ?>)
                            <?php endif; ?>
                            · Version <?= htmlspecialchars($curriculum->version ?? '2026-2027') ?>
                        </div>
                        <div class="d-flex flex-wrap gap-3 mt-1 small text-secondary">
                            <span><span class="text-muted">Type:</span> <span class="fw-semibold text-dark"><?= htmlspecialchars($selectedProgram->program_type ?? "Bachelor's Degree") ?></span></span>
                            <span><span class="text-muted">Length:</span> <span class="fw-semibold text-dark"><?= htmlspecialchars($selectedProgram->program_length ?? '4 Years') ?></span></span>
                        </div>
                        <?php if (!empty($selectedProgram->description)): ?>
                            <p class="small text-muted mb-0 mt-1"><?= htmlspecialchars($selectedProgram->description) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-white border rounded px-3 py-1.5 small text-nowrap">
                            <span class="text-muted">Subjects:</span> <span class="fw-bold text-dark" id="statSubjectsCount"><?= $totalSubjects ?></span>
                        </div>
                        <div class="bg-white border rounded px-3 py-1.5 small text-nowrap">
                            <span class="text-muted">Total Units:</span> <span class="fw-bold text-dark"><?= number_format($totalUnits, 1) ?></span>
                        </div>
                        <span class="badge rounded-pill px-3 py-1.5 text-uppercase fw-semibold <?= ($curriculum->status ?? 'active') === 'active' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' ?>">
                            <?= htmlspecialchars($curriculum->status ?? 'active') ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Subjects Grouped by Year and Semester -->
    <?php if (empty($groupedSubjects)): ?>
        <div class="card shadow-sm border-0 text-center py-5 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
            <p class="text-muted small mb-0">No subjects found in this curriculum. Click "New Curriculum" to add subjects.</p>
        </div>
    <?php else: ?>
        <div class="space-y-4" id="curriculumGroupsContainer">
            <?php foreach ($groupedSubjects as $group): ?>
                <div class="card shadow-sm border-0 mb-4 curriculum-group-card" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
                    <div class="card-header bg-light py-2.5 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <h3 class="h6 fw-bold text-dark mb-0"><?= htmlspecialchars($group['title']) ?></h3>
                        <div class="small text-muted">
                            <span class="group-count"><?= count($group['subjects']) ?></span> <?= count($group['subjects']) === 1 ? 'subject' : 'subjects' ?> · 
                            <span class="fw-semibold text-dark"><?= number_format($group['total_units'], 1) ?> units</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-white border-bottom">
                                <tr>
                                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 11px;">Subject Code</th>
                                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Descriptive Title</th>
                                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 90px; font-size: 11px;">Units</th>
                                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="width: 200px; font-size: 11px;">Subject Type</th>
                                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="width: 220px; font-size: 11px;">Pre-Requisite</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <?php foreach ($group['subjects'] as $sub): ?>
                                    <tr class="curriculum-subject-row" data-search="<?= strtolower(htmlspecialchars($sub->subject_code . ' ' . $sub->descriptive_title . ' ' . $sub->subject_type)) ?>">
                                        <td class="py-3 px-4">
                                            <span class="badge bg-light text-dark border font-monospace px-2 py-1 fw-bold">
                                                <?= htmlspecialchars($sub->subject_code) ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 fw-medium text-dark">
                                            <?= htmlspecialchars($sub->descriptive_title) ?>
                                        </td>
                                        <td class="py-3 px-3 text-center tabular-nums fw-semibold text-secondary">
                                            <?= number_format((float) $sub->units, 1) ?>
                                        </td>
                                        <td class="py-3 px-3 small text-secondary">
                                            <?= htmlspecialchars($sub->subject_type) ?>
                                        </td>
                                        <td class="py-3 px-4 small">
                                            <?php if (!empty($sub->prerequisites)): ?>
                                                <?php foreach (explode(',', $sub->prerequisites) as $pr): ?>
                                                    <span class="badge bg-light text-secondary border me-1">
                                                        <?= htmlspecialchars(trim($pr)) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('curriculumSubjectSearch');
    const groupCards = document.querySelectorAll('.curriculum-group-card');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const q = this.value.trim().toLowerCase();

            groupCards.forEach(function(card) {
                const rows = card.querySelectorAll('.curriculum-subject-row');
                let visibleRows = 0;

                rows.forEach(function(row) {
                    const searchData = row.getAttribute('data-search') || '';
                    if (!q || searchData.includes(q)) {
                        row.style.display = '';
                        visibleRows++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Hide card if no rows match
                card.style.display = visibleRows > 0 ? '' : 'none';
            });
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
