<?php
$pageTitle = 'My Assigned Subjects';
$subtitle = 'Curricular course sets assigned to your instructional teaching workload.';
ob_start();
?>

<div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="h6 mb-0 fw-semibold text-dark">Teaching Workload Roster</h3>
            <span class="text-muted small"><?= count($subjects) ?> courses assigned &bull; <?= htmlspecialchars($academicTerm['name'] ?? 'Active Term') ?></span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small fw-semibold">Semester:</span>
            <div class="btn-group btn-group-sm" role="group" aria-label="Semester selection">
                <a href="<?= url('/faculty/subjects?semester=1') ?>" class="btn <?= ($selectedSemester ?? '1') === '1' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    1st Semester
                </a>
                <a href="<?= url('/faculty/subjects?semester=2') ?>" class="btn <?= ($selectedSemester ?? '1') === '2' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    2nd Semester
                </a>
            </div>
        </div>
    </div>

    <?php if (empty($subjects)): ?>
        <?php
        $icon = 'bi-journal-bookmark';
        $iconColor = 'blue';
        $title = 'No subjects assigned yet';
        $message = 'You have not been designated to any course sets for ' . ($academicTerm['name'] ?? 'this term') . '. Contact the College Dean for curriculum assignments.';
        include __DIR__ . '/../../components/empty-state.php';
        ?>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-white border-bottom">
                    <tr>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 11px;">Subject code</th>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Descriptive title</th>
                        <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="width: 120px; font-size: 11px;">Nature</th>
                        <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 11px;">Grading method</th>
                        <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="width: 110px; font-size: 11px;">Year level</th>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="width: 80px; font-size: 11px;">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($subjects as $subject): ?>
                    <tr>
                        <td class="px-3 fw-semibold text-dark font-monospace"><?= htmlspecialchars($subject['subject_code'] ?? $subject['code']) ?></td>
                        <td class="px-3 fw-semibold text-dark">
                            <?= htmlspecialchars($subject['descriptive_title'] ?? $subject['name']) ?>
                        </td>
                        <td class="px-3">
                            <span class="badge <?= match($subject['nature'] ?? 'Lecture') {
                                'Laboratory' => 'bg-info-subtle text-info border border-info-subtle',
                                'Combined' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                default => 'bg-primary-subtle text-primary border border-primary-subtle'
                            } ?> fw-semibold">
                                <?= htmlspecialchars($subject['nature'] ?? 'Lecture') ?>
                            </span>
                        </td>
                        <td class="px-3 small">
                            <span class="badge bg-light text-dark border fw-semibold">
                                <?= ($subject['grading_method'] ?? 'zero_based') === 'fifty_based' ? '50-Based' : 'Zero-Based' ?>
                            </span>
                        </td>
                        <td class="px-3 small text-secondary">
                            <?= htmlspecialchars($subject['year_level']) ?><?= match((int)$subject['year_level']) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' } ?> year
                        </td>
                        <td class="px-3 text-end text-nowrap">
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-sm btn-action-trigger" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false" title="Actions">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end action-dropdown-menu shadow-sm">
                                    <li>
                                        <a class="dropdown-item" href="<?= url('/faculty/grading?subject_id=' . $subject['id'] . '&semester=' . ($selectedSemester ?? '1')) ?>">
                                            <i class="bi bi-pencil-square text-primary"></i> Enter grades
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= url('/faculty/students?subject_id=' . $subject['id']) ?>">
                                            <i class="bi bi-people text-muted"></i> View students
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#setupModal<?= $subject['id'] ?>">
                                            <i class="bi bi-gear text-muted"></i> Subject setup
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php foreach ($subjects as $subject): ?>
        <div class="modal fade" id="setupModal<?= $subject['id'] ?>" tabindex="-1" aria-labelledby="setupModalLabel<?= $subject['id'] ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form method="POST" action="<?= url('/faculty/subjects/' . $subject['id'] . '/setup') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="academic_term_id" value="<?= htmlspecialchars((string)($academicTerm['id'] ?? 1)) ?>">
                        <div class="modal-header border-bottom">
                            <h5 class="modal-title h6 fw-semibold mb-0" id="setupModalLabel<?= $subject['id'] ?>">
                                Subject Setup: <?= htmlspecialchars($subject['subject_code'] ?? $subject['code']) ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small mb-3">Configure course nature and grading period percentage weights per institutional syllabus guidelines.</p>

                            <div class="mb-3">
                                <label for="nature_<?= $subject['id'] ?>" class="form-label small fw-semibold">Subject nature</label>
                                <select class="form-select" id="nature_<?= $subject['id'] ?>" name="nature" required>
                                    <option value="Lecture" <?= ($subject['nature'] ?? 'Lecture') === 'Lecture' ? 'selected' : '' ?>>Lecture</option>
                                    <option value="Laboratory" <?= ($subject['nature'] ?? '') === 'Laboratory' ? 'selected' : '' ?>>Laboratory</option>
                                    <option value="Combined" <?= ($subject['nature'] ?? '') === 'Combined' ? 'selected' : '' ?>>Combined (Lecture + Lab)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="grading_method_<?= $subject['id'] ?>" class="form-label small fw-semibold">Grading method</label>
                                <select class="form-select" id="grading_method_<?= $subject['id'] ?>" name="grading_method" required>
                                    <option value="zero_based" <?= ($subject['grading_method'] ?? 'zero_based') === 'zero_based' ? 'selected' : '' ?>>Zero-Based (Raw % = Score / Total × 100)</option>
                                    <option value="fifty_based" <?= ($subject['grading_method'] ?? '') === 'fifty_based' ? 'selected' : '' ?>>50-Based (Raw % = (Score / Total × 50) + 50)</option>
                                </select>
                            </div>

                            <div class="card bg-light border p-3 mb-2">
                                <span class="small fw-semibold text-dark mb-2 d-block">Grading period weights (%)</span>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small text-muted mb-1" for="prelim_<?= $subject['id'] ?>">Prelim (%)</label>
                                        <input type="number" step="0.01" min="0" max="100" class="form-control" id="prelim_<?= $subject['id'] ?>" name="prelim_weight" value="<?= htmlspecialchars((string)$subject['prelim_weight']) ?>" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small text-muted mb-1" for="midterm_<?= $subject['id'] ?>">Midterm (%)</label>
                                        <input type="number" step="0.01" min="0" max="100" class="form-control" id="midterm_<?= $subject['id'] ?>" name="midterm_weight" value="<?= htmlspecialchars((string)$subject['midterm_weight']) ?>" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small text-muted mb-1" for="semifinal_<?= $subject['id'] ?>">Semi-final (%)</label>
                                        <input type="number" step="0.01" min="0" max="100" class="form-control" id="semifinal_<?= $subject['id'] ?>" name="semi_final_weight" value="<?= htmlspecialchars((string)$subject['semi_final_weight']) ?>" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small text-muted mb-1" for="final_<?= $subject['id'] ?>">Final (%)</label>
                                        <input type="number" step="0.01" min="0" max="100" class="form-control" id="final_<?= $subject['id'] ?>" name="final_weight" value="<?= htmlspecialchars((string)$subject['final_weight']) ?>" required>
                                    </div>
                                </div>
                                <div class="form-text mt-2 small">Total must sum to exactly 100% (e.g. 20% + 20% + 20% + 40%).</div>
                            </div>
                        </div>
                        <div class="modal-footer border-top py-3 d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                                <i class="bi bi-check2"></i> Save configuration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
