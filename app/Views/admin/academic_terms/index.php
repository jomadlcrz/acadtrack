<?php
$pageTitle = 'Academic Terms';
$subtitle = 'Manage academic school years, semestral terms, and session cycles.';
$headerActions = '<button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#createTermModal"><i class="bi bi-plus-lg"></i> Create Academic Term</button>';
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
<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="termsTable">
            <thead class="bg-white border-bottom">
                <tr>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="width: 200px; font-size: 11px;">School Year</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Semester</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 140px; font-size: 11px;">Status</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="width: 160px; font-size: 11px;">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (empty($terms)): ?>
                    <tr id="emptyRow">
                        <td colspan="4" class="p-0">
                            <?php
                            $icon = 'bi-calendar-x';
                            $title = 'No academic terms created yet';
                            $message = 'Click "Create Academic Term" to register one.';
                            include __DIR__ . '/../../components/empty-state.php';
                            ?>
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
                                <?php if ($isActive): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                <?php elseif ($isArchived): ?>
                                    <span class="badge bg-secondary-subtle text-secondary border">Archived</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-end">
                                <?php if (!$isActive && !$isArchived): ?>
                                    <div class="dropdown d-inline-block">
                                        <button class="btn btn-sm btn-action-trigger" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false" title="Actions">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end action-dropdown-menu shadow-sm">
                                            <li>
                                                <form method="POST" action="<?= url('/admin/academic-terms/' . $term['id'] . '/toggle-active') ?>" class="m-0">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="dropdown-item text-primary">
                                                        <i class="bi bi-check2"></i> Activate term
                                                    </button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="<?= url('/admin/academic-terms/' . $term['id'] . '/archive') ?>" class="m-0" onsubmit="return confirm('Archive academic term <?= $syDisplay ?> (<?= $semLabel ?>)?');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-archive"></i> Archive term
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                <?php elseif ($isArchived): ?>
                                    <form method="POST" action="<?= url('/admin/academic-terms/' . $term['id'] . '/restore') ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2 d-inline-flex align-items-center gap-1" title="Restore Term">
                                            <i class="bi bi-arrow-counterclockwise"></i> Restore
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="emptyRow" style="display: none;">
                        <td colspan="4" class="p-0">
                            <?php
                            $icon = 'bi-search';
                            $title = 'No academic terms found';
                            $message = 'No academic terms match your search keywords.';
                            include __DIR__ . '/../../components/empty-state.php';
                            ?>
                        </td>
                    </tr>
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
                <div class="modal-footer border-top py-2.5 px-4 bg-light d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-check2"></i> Save Term
                    </button>
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
    const emptyRow = document.getElementById('emptyRow');

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

            if (emptyRow && rows.length > 0) {
                emptyRow.style.display = visibleCount === 0 ? '' : 'none';
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
