<?php
$pageTitle = 'Review Grading Sheet: ' . htmlspecialchars($sheet['subject_code']);
$subtitle = 'Audit student marks for ' . htmlspecialchars($sheet['period_name']) . ', make administrative adjustments, or authorize confirmation.';
ob_start();

$isFinalized = ($sheet['status'] === 'FINALIZED');
$isApproved = ($sheet['status'] === 'APPROVED');
$isPending = in_array($sheet['status'], ['SUBMITTED', 'UNDER_REVIEW']);
?>

<!-- Metadata Summary Card -->
<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-md-7">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-primary text-white font-monospace"><?= htmlspecialchars($sheet['subject_code']) ?></span>
                    <span class="badge <?= match($sheet['subject_nature'] ?? 'Lecture') {
                        'Laboratory' => 'bg-info-subtle text-info border border-info-subtle',
                        'Combined' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                        default => 'bg-primary-subtle text-primary border border-primary-subtle'
                    } ?> fw-semibold">
                        <?= htmlspecialchars($sheet['subject_nature'] ?? 'Lecture') ?>
                    </span>
                    <span class="badge bg-light text-dark border fw-semibold">
                        <?= ($setting['grading_method'] ?? 'zero_based') === 'fifty_based' ? '50-Based' : 'Zero-Based' ?>
                    </span>
                    <span class="badge <?= match($sheet['status']) {
                        'APPROVED' => 'bg-success-subtle text-success border border-success-subtle',
                        'FINALIZED' => 'bg-dark-subtle text-dark border border-secondary',
                        'RETURNED' => 'bg-danger-subtle text-danger border border-danger-subtle',
                        default => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
                    } ?>">
                        <?= htmlspecialchars($sheet['status']) ?>
                    </span>
                </div>
                <h2 class="h5 fw-semibold text-dark mb-1"><?= htmlspecialchars($sheet['subject_name']) ?></h2>
                <div class="text-muted small d-flex flex-wrap gap-3 mt-2">
                    <span><i class="bi bi-person me-1"></i>Instructor: <strong><?= htmlspecialchars($sheet['faculty_first_name'] . ' ' . $sheet['faculty_last_name']) ?></strong></span>
                    <span><i class="bi bi-calendar-event me-1"></i>Period: <strong><?= htmlspecialchars($sheet['period_name']) ?></strong></span>
                    <span><i class="bi bi-mortarboard me-1"></i>Year Level: <strong><?= htmlspecialchars((string)$sheet['year_level']) ?></strong></span>
                </div>
            </div>

            <div class="col-md-5 text-md-end">
                <div class="d-flex flex-wrap justify-content-md-end gap-2">
                    <a href="<?= url('/dean/grade-review') ?>" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
                        <i class="bi bi-arrow-left"></i> Back to queue
                    </a>
                    <a href="<?= url('/dean/grade-review/' . $sheet['id'] . '/print') ?>" target="_blank" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
                        <i class="bi bi-printer"></i> Print official sheet
                    </a>

                    <?php if ($isPending): ?>
                        <form method="POST" action="<?= url('/dean/grade-review/approve') ?>" class="d-inline" onsubmit="return confirm('Officially approve this grading sheet?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="grading_sheet_id" value="<?= $sheet['id'] ?>">
                            <input type="hidden" name="redirect_to" value="<?= url('/dean/grade-review/' . $sheet['id']) ?>">
                            <button type="submit" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1">
                                <i class="bi bi-check-circle"></i> Approve sheet
                            </button>
                        </form>
                        <form method="POST" action="<?= url('/dean/grade-review/return') ?>" class="d-inline" onsubmit="return confirm('Return this grading sheet to faculty for revisions?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="grading_sheet_id" value="<?= $sheet['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1">
                                <i class="bi bi-arrow-counterclockwise"></i> Return
                            </button>
                        </form>
                    <?php elseif ($isApproved): ?>
                        <button type="button" class="btn btn-sm btn-dark d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#confirmSheetModal">
                            <i class="bi bi-shield-check"></i> Confirm grading sheet
                        </button>
                    <?php endif; ?>
                </div>

                <?php if (!empty($sheet['remarks'])): ?>
                    <div class="mt-2 text-muted small fst-italic">
                        Remarks: "<?= htmlspecialchars($sheet['remarks']) ?>"
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Student Roster & Period Grade Editing Form -->
<div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h3 class="h6 mb-0 fw-semibold text-dark">Enrolled Student Grades Roster</h3>
            <span class="text-muted small"><?= count($students) ?> students evaluated for <?= htmlspecialchars($sheet['period_name']) ?></span>
        </div>
        <?php if (!$isFinalized): ?>
            <button type="submit" form="editGradesForm" class="btn btn-sm btn-primary py-1 px-3 d-inline-flex align-items-center gap-1">
                <i class="bi bi-save"></i> Save grade adjustments
            </button>
        <?php endif; ?>
    </div>

    <?php if (empty($students)): ?>
        <div class="card-body text-center py-5">
            <div class="d-inline-flex align-items-center justify-content-center bg-light text-muted rounded-circle mb-3" style="width: 52px; height: 52px; font-size: 24px;">
                <i class="bi bi-person-x"></i>
            </div>
            <h4 class="h6 fw-semibold text-dark mb-1">No enrolled students</h4>
            <p class="text-muted small mb-0">There are no students enrolled in this course set for the active term.</p>
        </div>
    <?php else: ?>
        <form id="editGradesForm" method="POST" action="<?= url('/dean/grade-review/' . $sheet['id'] . '/edit') ?>">
            <?= csrf_field() ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="fw-semibold text-muted small py-3 px-3" style="width: 60px;">#</th>
                            <th class="fw-semibold text-muted small py-3 px-3" style="width: 150px;">Student number</th>
                            <th class="fw-semibold text-muted small py-3 px-3">Student full name</th>
                            <th class="fw-semibold text-muted small py-3 px-3" style="width: 120px;">Status</th>
                            <th class="fw-semibold text-muted small py-3 px-3" style="width: 100px;">Year level</th>
                            <th class="fw-semibold text-muted small py-3 px-3 text-end" style="width: 140px;"><?= htmlspecialchars($sheet['period_name']) ?> mark</th>
                            <th class="fw-semibold text-muted small py-3 px-3 text-center" style="width: 120px;">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $index => $student): ?>
                        <?php
                            $grade = $grades[$student['id']] ?? null;
                            $hasGrade = ($grade !== null && $grade !== '');
                            $numericGrade = (float) ($grade ?? 0);
                            $passed = $hasGrade && $numericGrade >= 75.0;
                        ?>
                        <tr>
                            <td class="px-3 small text-muted"><?= $index + 1 ?></td>
                            <td class="px-3 font-monospace small text-dark fw-semibold">
                                <?= !empty($student['student_number']) ? htmlspecialchars($student['student_number']) : 'No ID' ?>
                            </td>
                            <td class="px-3">
                                <div class="fw-semibold text-dark">
                                    <?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) ?>
                                </div>
                                <div class="text-muted small"><?= htmlspecialchars($student['email'] ?? '') ?></div>
                            </td>
                            <td class="px-3">
                                <span class="badge <?= ($student['status'] ?? 'Regular') === 'Regular' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' ?>">
                                    <?= htmlspecialchars($student['status'] ?? 'Regular') ?>
                                </span>
                            </td>
                            <td class="px-3 small text-secondary">
                                <?= htmlspecialchars((string)($student['year_level'] ?? 1)) ?><?= match((int)($student['year_level'] ?? 1)) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' } ?> year
                            </td>
                            <td class="px-3 text-end">
                                <?php if ($isFinalized): ?>
                                    <span class="font-monospace fw-semibold <?= $hasGrade ? ($passed ? 'text-success' : 'text-danger') : 'text-muted' ?>">
                                        <?= $hasGrade ? number_format($numericGrade, 2) : '—' ?>
                                    </span>
                                <?php else: ?>
                                    <input type="number" step="0.01" min="0" max="100"
                                           class="form-control form-control-sm text-end font-monospace ms-auto"
                                           style="width: 100px;"
                                           name="grades[<?= $student['id'] ?>]"
                                           value="<?= $hasGrade ? htmlspecialchars((string)$grade) : '' ?>"
                                           placeholder="0.00">
                                <?php endif; ?>
                            </td>
                            <td class="px-3 text-center">
                                <?php if ($hasGrade): ?>
                                    <span class="badge <?= $passed ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' ?>">
                                        <?= $passed ? 'Passed' : 'Failed' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small fst-italic">No mark</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!$isFinalized): ?>
                <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        <i class="bi bi-info-circle me-1"></i> As Dean or Admin, edits made here update the official student marks prior to approval or confirmation.
                    </span>
                    <button type="submit" class="btn btn-sm btn-primary py-1 px-3 d-inline-flex align-items-center gap-1">
                        <i class="bi bi-save"></i> Save grade adjustments
                    </button>
                </div>
            <?php endif; ?>
        </form>
    <?php endif; ?>
</div>

<!-- Modal for Confirming Grading Sheet -->
<?php if ($isApproved): ?>
<div class="modal fade" id="confirmSheetModal" tabindex="-1" aria-labelledby="confirmSheetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="<?= url('/dean/grade-review/confirm') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="grading_sheet_id" value="<?= $sheet['id'] ?>">
                <input type="hidden" name="redirect_to" value="<?= url('/dean/grade-review/' . $sheet['id']) ?>">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title h6 fw-semibold mb-0" id="confirmSheetModalLabel">
                        Confirm &amp; Finalize: <?= htmlspecialchars($sheet['subject_code']) ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-exclamation-triangle-fill fs-6 flex-shrink-0 mt-1"></i>
                        <div>
                            <strong>Irreversible action:</strong> Confirming permanently locks this grading sheet into <code>FINALIZED</code> status and records your administrator signature. No further edits can be made.
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="remarks" class="form-label small fw-semibold">Confirmation notes / institutional remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="e.g. Official rating approved and confirmed for registrar filing."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark d-inline-flex align-items-center gap-2">
                        <i class="bi bi-check2"></i> Confirm &amp; finalize sheet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
