<?php
$pageTitle = 'Academic Terms';
$subtitle = 'Manage academic school years, semestral terms, and session cycles.';
$headerActions = '<button type="button" class="btn text-white d-inline-flex align-items-center gap-1.5" style="background-color: #2f4a86; border-color: #2f4a86;" data-bs-toggle="modal" data-bs-target="#createTermModal"><i class="bi bi-plus-lg"></i> Create Academic Term</button>';
ob_start();
?>

<!-- Search & Statistics Bar -->
<div class="d-flex flex-column flex-sm-row gap-3 align-items-sm-center justify-content-between mb-4">
    <div class="position-relative w-100" style="max-width: 420px;">
        <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 14px;"></i>
        <input type="text" 
               id="termsSearch" 
               class="form-control ps-5" 
               placeholder="Search by school year or semester..." 
               autocomplete="off">
    </div>
    <div class="text-muted small fw-medium" id="termsCounter">
        <?= count($terms) ?> <?= count($terms) === 1 ? 'term' : 'terms' ?>
    </div>
</div>

<!-- Academic Terms Table -->
<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="termsTable">
            <thead class="bg-white border-bottom">
                <tr>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="width: 200px; font-size: 11px;">School Year</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Semester</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 140px; font-size: 11px;">Curricular Sets</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 140px; font-size: 11px;">Subjects</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 140px; font-size: 11px;">Status</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="width: 160px; font-size: 11px;">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (empty($terms)): ?>
                    <tr id="emptyRow">
                        <td colspan="6" class="text-center py-5 text-muted small">
                            <i class="bi bi-calendar-x d-block fs-3 mb-2 text-secondary"></i>
                            No academic terms created yet. Click "Create Academic Term" to register one.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($terms as $term): ?>
                        <?php 
                            $isActive = (int) ($term['is_active'] ?? 0) === 1;
                            $isArchived = (int) ($term['is_archived'] ?? 0) === 1;
                            $semNum = (int) ($term['semester'] ?? 1);
                            $semLabel = match ($semNum) {
                                1 => '1st Semester',
                                2 => '2nd Semester',
                                3 => 'Summer',
                                default => "Semester {$semNum}",
                            };
                            $syDisplay = htmlspecialchars($term['school_year_display'] ?? $term['school_year'] ?? '2026-2027');
                        ?>
                        <tr class="term-row <?= $isArchived ? 'table-light text-muted' : '' ?>" 
                            data-search="<?= strtolower($syDisplay . ' ' . $semLabel) ?>">
                            <td class="py-3 px-4 fw-bold text-dark font-monospace">
                                <?= $syDisplay ?>
                            </td>
                            <td class="py-3 px-4 fw-medium text-secondary">
                                <?= $semLabel ?>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="badge bg-light text-dark border px-2.5 py-1">
                                    <i class="bi bi-collection me-1 text-muted"></i><?= (int)($term['sets_count'] ?? 0) ?> sets
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="badge bg-light text-dark border px-2.5 py-1">
                                    <i class="bi bi-book me-1 text-muted"></i><?= (int)($term['subjects_count'] ?? 0) ?> subjects
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <?php if ($isActive): ?>
                                    <span class="inline-flex px-2.5 py-1 rounded-pill text-xs fw-semibold" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                                        <i class="bi bi-check-circle-fill me-1"></i> Active
                                    </span>
                                <?php elseif ($isArchived): ?>
                                    <span class="inline-flex px-2.5 py-1 rounded-pill text-xs fw-semibold" style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">
                                        <i class="bi bi-archive me-1"></i> Archived
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex px-2.5 py-1 rounded-pill text-xs fw-semibold text-secondary" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                                        Inactive
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-end">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    <?php if (!$isActive && !$isArchived): ?>
                                        <form method="POST" action="<?= url('/admin/academic-terms/' . $term['id'] . '/toggle-active') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-primary py-1 px-2 d-inline-flex align-items-center gap-1" title="Set as Current Active Term">
                                                <i class="bi bi-check2"></i> Activate
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($isArchived): ?>
                                        <form method="POST" action="<?= url('/admin/academic-terms/' . $term['id'] . '/restore') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2 d-inline-flex align-items-center gap-1" title="Restore Term">
                                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                                            </button>
                                        </form>
                                    <?php elseif (!$isActive): ?>
                                        <form method="POST" action="<?= url('/admin/academic-terms/' . $term['id'] . '/archive') ?>" class="d-inline" onsubmit="return confirm('Archive academic term <?= $syDisplay ?> (<?= $semLabel ?>)?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-secondary py-1 px-2 d-inline-flex align-items-center gap-1" title="Archive Term">
                                                <i class="bi bi-archive"></i> Archive
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Academic Term Modal -->
<div class="modal fade" id="createTermModal" tabindex="-1" aria-labelledby="createTermModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="<?= url('/admin/academic-terms') ?>">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title h6 fw-bold mb-0" id="createTermModalLabel">Create Academic Term</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 space-y-3">
                    <div class="mb-3">
                        <label for="school_year" class="form-label small fw-semibold text-dark">School Year <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control font-monospace" 
                               id="school_year" 
                               name="school_year" 
                               placeholder="e.g. 2026-2027" 
                               required 
                               pattern="^\d{4}-\d{4}$">
                        <div class="form-text small text-muted">Format: YYYY-YYYY (consecutive years)</div>
                    </div>

                    <div class="mb-3">
                        <label for="semester" class="form-label small fw-semibold text-dark">Semester <span class="text-danger">*</span></label>
                        <select class="form-select" id="semester" name="semester" required>
                            <option value="1">1st Semester</option>
                            <option value="2">2nd Semester</option>
                            <option value="3">Summer</option>
                        </select>
                    </div>

                    <div class="form-check form-switch pt-2">
                        <input class="form-check-input" type="checkbox" id="set_active" name="set_active" value="1">
                        <label class="form-check-label small fw-medium" for="set_active">Set as current active institutional term</label>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4 bg-light d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm px-3 text-white" style="background-color: #2f4a86; border-color: #2f4a86;">Save Term</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('termsSearch');
    const rows = document.querySelectorAll('.term-row');
    const counter = document.getElementById('termsCounter');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            let visibleCount = 0;

            rows.forEach(row => {
                const text = row.getAttribute('data-search') || '';
                if (!query || text.includes(query)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (counter) {
                counter.textContent = `${visibleCount} ${visibleCount === 1 ? 'term' : 'terms'}`;
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
