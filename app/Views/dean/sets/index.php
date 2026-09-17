<?php
$pageTitle = 'Academic Sets';
$subtitle = 'Manage class batches, year-level groups, and student set allocations.';
$headerActions = '
<div class="d-flex align-items-center gap-2">
    <div class="btn-group btn-group-sm" role="group" aria-label="Semester selection">
        <a href="' . url('/dean/sets?semester=1') . '" class="btn ' . (($selectedSemester ?? '1') === '1' ? 'btn-primary' : 'btn-outline-secondary') . '">
            1st Semester
        </a>
        <a href="' . url('/dean/sets?semester=2') . '" class="btn ' . (($selectedSemester ?? '1') === '2' ? 'btn-primary' : 'btn-outline-secondary') . '">
            2nd Semester
        </a>
    </div>
    <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addSetModal">
        <i class="bi bi-plus-circle"></i> Add set
    </button>
</div>';
ob_start();
$displaySets = $sets ?? [];
?>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h6 mb-0 fw-semibold text-dark">Class Batches Roster</h3>
            <small class="text-muted">Active set batches for <?= htmlspecialchars($academicTerm['name'] ?? 'Active Term') ?></small>
        </div>
        <span class="badge bg-light text-dark border"><?= count($displaySets) ?> sets registered</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light border-bottom">
                <tr>
                    <th class="fw-semibold text-muted small py-3 px-3" style="width: 160px;">Set name</th>
                    <th class="fw-semibold text-muted small py-3 px-3" style="width: 140px;">Year level</th>
                    <th class="fw-semibold text-muted small py-3 px-3">Department / College</th>
                    <th class="fw-semibold text-muted small py-3 px-3 text-center" style="width: 160px;">Enrolled students</th>
                    <th class="fw-semibold text-muted small py-3 px-3 text-center" style="width: 120px;">Status</th>
                    <th class="fw-semibold text-muted small py-3 px-3 text-end" style="width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($displaySets)): ?>
                    <tr>
                        <td colspan="6" class="p-0">
                            <?php
                            $icon = 'bi-collection';
                            $title = 'No academic sets';
                            $message = 'No academic sets configured for this semester yet. Click "Add set" to create one.';
                            include __DIR__ . '/../../components/empty-state.php';
                            ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($displaySets as $sec): ?>
                    <tr>
                        <td class="px-3 fw-semibold text-dark font-monospace">
                            <i class="bi bi-people me-1 text-muted"></i>
                            <?= htmlspecialchars($sec['name']) ?>
                        </td>
                        <td class="px-3 text-secondary small">
                            <?= htmlspecialchars((string)$sec['year_level']) ?><?= match((int)$sec['year_level']) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' } ?> year
                        </td>
                        <td class="px-3 small">
                            <?php if (!empty($sec['department'])): ?>
                                <span class="badge bg-light text-dark border">
                                    <?= htmlspecialchars($sec['department']['name']) ?> (<?= htmlspecialchars($sec['department']['code']) ?>)
                                </span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 text-center">
                            <?php $studentCount = (int) ($sec['students_count'] ?? 0); ?>
                            <?php if ($studentCount > 0): ?>
                                <span class="badge badge-student">
                                    <i class="bi bi-person me-1"></i> <?= $studentCount ?> <?= $studentCount === 1 ? 'student' : 'students' ?>
                                </span>
                            <?php else: ?>
                                <span class="badge badge-draft">0 students</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 text-center">
                            <?php if (($sec['status'] ?? 'active') === 'active'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary border">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 text-end">
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-sm btn-action-trigger" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false" title="Actions">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end action-dropdown-menu shadow-sm">
                                    <li>
                                        <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editSetModal<?= $sec['id'] ?>">
                                            <i class="bi bi-pencil text-muted"></i> Edit set
                                        </button>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <?php if (($sec['status'] ?? 'active') === 'active'): ?>
                                        <li>
                                            <form method="POST" action="<?= url('/dean/sets/' . $sec['id'] . '/archive') ?>" class="m-0" onsubmit="return confirm('Archive set <?= htmlspecialchars(addslashes($sec['name'])) ?>?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="semester" value="<?= htmlspecialchars((string)($selectedSemester ?? '1')) ?>">
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-archive"></i> Archive set
                                                </button>
                                            </form>
                                        </li>
                                    <?php else: ?>
                                        <li>
                                            <form method="POST" action="<?= url('/dean/sets/' . $sec['id'] . '/restore') ?>" class="m-0">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="semester" value="<?= htmlspecialchars((string)($selectedSemester ?? '1')) ?>">
                                                <button type="submit" class="dropdown-item text-success">
                                                    <i class="bi bi-arrow-counterclockwise"></i> Restore set
                                                </button>
                                            </form>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </td>
                    </tr>

                    <!-- Edit Set Modal -->
                    <div class="modal fade" id="editSetModal<?= $sec['id'] ?>" tabindex="-1" aria-labelledby="editSetModalLabel<?= $sec['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="<?= url('/dean/sets/' . $sec['id']) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="semester" value="<?= htmlspecialchars((string)($selectedSemester ?? '1')) ?>">
                                    <div class="modal-header">
                                        <h5 class="modal-title h6 fw-semibold" id="editSetModalLabel<?= $sec['id'] ?>">Edit Set</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="name_<?= $sec['id'] ?>" class="form-label">Set name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control font-monospace" id="name_<?= $sec['id'] ?>" name="name" value="<?= htmlspecialchars($sec['name']) ?>" required style="text-transform: uppercase;">
                                            <div class="form-text">e.g., BSIT-1A, BSIT-2B, BSCS-1A</div>
                                        </div>
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-6">
                                                <label for="year_level_<?= $sec['id'] ?>" class="form-label">Year level <span class="text-danger">*</span></label>
                                                <select class="form-select" id="year_level_<?= $sec['id'] ?>" name="year_level" required>
                                                    <option value="1" <?= (int)$sec['year_level'] === 1 ? 'selected' : '' ?>>1st year</option>
                                                    <option value="2" <?= (int)$sec['year_level'] === 2 ? 'selected' : '' ?>>2nd year</option>
                                                    <option value="3" <?= (int)$sec['year_level'] === 3 ? 'selected' : '' ?>>3rd year</option>
                                                    <option value="4" <?= (int)$sec['year_level'] === 4 ? 'selected' : '' ?>>4th year</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="status_<?= $sec['id'] ?>" class="form-label">Status</label>
                                                <select class="form-select" id="status_<?= $sec['id'] ?>" name="status">
                                                    <option value="active" <?= ($sec['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                                    <option value="inactive" <?= ($sec['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="department_id_<?= $sec['id'] ?>" class="form-label">Department / College</label>
                                            <select class="form-select" id="department_id_<?= $sec['id'] ?>" name="department_id">
                                                <option value="">None / General</option>
                                                <?php foreach ($departments ?? [] as $dept): ?>
                                                    <option value="<?= $dept['id'] ?>" <?= ((string)($sec['department_id'] ?? '') === (string)$dept['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($dept['name']) ?> (<?= htmlspecialchars($dept['code']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light py-2.5 px-4 border-top d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1.5">
                                            <i class="bi bi-check2"></i> Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Set Modal -->
<div class="modal fade" id="addSetModal" tabindex="-1" aria-labelledby="addSetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('/dean/sets') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="academic_term_id" value="<?= htmlspecialchars((string)($academicTerm['id'] ?? 1)) ?>">
                <input type="hidden" name="semester" value="<?= htmlspecialchars((string)($selectedSemester ?? '1')) ?>">
                <div class="modal-header">
                    <h5 class="modal-title h6 fw-semibold" id="addSetModalLabel">Add New Set</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="add_name" class="form-label">Set name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-monospace" id="add_name" name="name" placeholder="e.g., BSIT-1A" required style="text-transform: uppercase;">
                        <div class="form-text">Unique batch identifier for this semester.</div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="add_year_level" class="form-label">Year level <span class="text-danger">*</span></label>
                            <select class="form-select" id="add_year_level" name="year_level" required>
                                <option value="1" selected>1st year</option>
                                <option value="2">2nd year</option>
                                <option value="3">3rd year</option>
                                <option value="4">4th year</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="add_status" class="form-label">Status</label>
                            <select class="form-select" id="add_status" name="status">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="add_department_id" class="form-label">Department / College</label>
                        <select class="form-select" id="add_department_id" name="department_id">
                            <option value="">None / General</option>
                            <?php foreach ($departments ?? [] as $dept): ?>
                                <option value="<?= $dept['id'] ?>">
                                    <?= htmlspecialchars($dept['name']) ?> (<?= htmlspecialchars($dept['code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5 px-4 border-top d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-plus-lg"></i> Save Set
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
