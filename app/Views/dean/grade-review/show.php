<?php
$pageTitle = 'Review Grading Sheet';
$subtitle = htmlspecialchars($sheet['subject_code']) . ' · ' . htmlspecialchars($sheet['period_name']) . ' · Check the marks, then approve or return the sheet.';
$headerActions = '
<div class="d-flex align-items-center gap-2">
    <a href="' . url('/dean/grade-review') . '" class="btn btn-sm btn-outline-secondary">Back to queue</a>
    <a href="' . url('/dean/grade-review/' . $sheet['id'] . '/print') . '" target="_blank" class="btn btn-sm btn-outline-secondary">Print sheet</a>
</div>';
ob_start();

$status = (string) $sheet['status'];
$st = grading_sheet_status($status);
$isDraft = $status === 'DRAFT';
$isPending = in_array($status, ['SUBMITTED', 'UNDER_REVIEW'], true);
$isApproved = $status === 'APPROVED';
$isReturned = $status === 'RETURNED';
$isFinalized = $status === 'FINALIZED';
$canAdjust = $isPending || $isApproved;
$canReturn = $isPending || $isApproved;

$instructor = trim(($sheet['faculty_first_name'] ?? '') . ' ' . ($sheet['faculty_last_name'] ?? ''));
$approver = trim(($sheet['approver_first_name'] ?? '') . ' ' . ($sheet['approver_last_name'] ?? ''));
$fmt = static fn (?string $ts): string => $ts ? date('M d, Y h:i A', strtotime($ts)) : '';
$backHere = url('/dean/grade-review/' . $sheet['id']);

// History: recorded events; sheets that predate the history log fall back to their stored timestamps
$timeline = [];
foreach ($events ?? [] as $e) {
    $timeline[] = ['action' => $e['action'], 'actor' => $e['actor_name'], 'at' => $e['created_at'], 'note' => $e['remarks']];
}
if ($timeline === []) {
    foreach ([
        ['SUBMITTED', 'submitted_at', $instructor, null],
        ['APPROVED', 'approved_at', $approver, null],
        ['RETURNED', 'returned_at', '', $isReturned ? ($sheet['remarks'] ?? null) : null],
        ['FINALIZED', 'confirmed_at', $approver, $isFinalized ? ($sheet['remarks'] ?? null) : null],
    ] as [$action, $col, $who, $note]) {
        if (!empty($sheet[$col])) {
            $timeline[] = ['action' => $action, 'actor' => $who, 'at' => $sheet[$col], 'note' => $note];
        }
    }
}
$timeline = array_reverse($timeline);

$eventMeta = [
    'SUBMITTED' => ['Submitted for review', 'bi-send', 'text-primary'],
    'REVIEW_STARTED' => ['Review started', 'bi-eye', 'text-primary'],
    'APPROVED' => ['Approved', 'bi-check-circle-fill', 'text-success'],
    'RETURNED' => ['Returned to instructor', 'bi-arrow-return-left', 'text-danger'],
    'GRADES_ADJUSTED' => ['Marks adjusted', 'bi-pencil-square', 'text-warning'],
    'FINALIZED' => ['Finalized and published', 'bi-lock-fill', 'text-dark'],
];

$ordinal = static fn (int $n): string => $n . match (true) {
    $n % 100 >= 11 && $n % 100 <= 13 => 'th',
    $n % 10 === 1 => 'st',
    $n % 10 === 2 => 'nd',
    $n % 10 === 3 => 'rd',
    default => 'th',
};
?>

<!-- Sheet header -->
<div class="card shadow-sm border-0 mb-3" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <div class="queue-code"><?= htmlspecialchars($sheet['subject_code']) ?></div>
                <h2 class="h5 fw-semibold text-dark mb-2"><?= htmlspecialchars($sheet['subject_name']) ?></h2>
                <div class="queue-meta d-flex flex-wrap gap-x-3" style="column-gap: 20px; row-gap: 4px;">
                    <span>Instructor: <strong class="text-dark"><?= htmlspecialchars($instructor ?: '—') ?></strong></span>
                    <span>Period: <strong class="text-dark"><?= htmlspecialchars($sheet['period_name']) ?></strong></span>
                    <span><?= htmlspecialchars($academicTerm['name'] ?? '') ?></span>
                    <span><?= htmlspecialchars($ordinal((int) $sheet['year_level'])) ?> year &bull; <?= htmlspecialchars($sheet['subject_nature'] ?? 'Lecture') ?> &bull; <?= ($setting['grading_method'] ?? 'zero_based') === 'fifty_based' ? '50-based' : 'Zero-based' ?></span>
                </div>
            </div>
            <span class="badge <?= $st['class'] ?> fs-7 d-inline-flex align-items-center gap-1">
                <i class="bi <?= $st['icon'] ?>"></i> <?= htmlspecialchars($st['label']) ?>
            </span>
        </div>

        <?php if (!empty($siblings)): ?>
            <div class="period-steps mt-3 pt-3">
                <span class="queue-meta">This course, all periods:</span>
                <?php foreach ($siblings as $sib): ?>
                    <?php $ss = $sib['status'] ? grading_sheet_status((string) $sib['status']) : null; ?>
                    <?php if ($sib['sheet_id']): ?>
                        <a href="<?= url('/dean/grade-review/' . $sib['sheet_id']) ?>" class="period-step <?= $sib['is_this'] ? 'is-current' : '' ?>">
                            <?= htmlspecialchars($sib['period_name']) ?>
                            <span class="period-step-status"><i class="bi <?= $ss['icon'] ?>"></i> <?= htmlspecialchars($ss['short']) ?></span>
                        </a>
                    <?php else: ?>
                        <span class="period-step is-empty"><?= htmlspecialchars($sib['period_name']) ?> <span class="period-step-status">Not submitted</span></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Next step: always tells the Dean what to do now -->
<div class="next-step mb-4">
    <div class="next-step-text">
        <?php if ($isPending): ?>
            <div class="next-step-title"><i class="bi bi-clipboard-check text-primary"></i> Your decision</div>
            <p class="mb-0">Check the class summary and marks below. If they look right, <strong>approve</strong> the sheet. If something is wrong, <strong>return</strong> it to the instructor and say what to correct. You can also adjust a mark yourself.</p>
        <?php elseif ($isApproved): ?>
            <div class="next-step-title"><i class="bi bi-shield-check text-primary"></i> Approved, not yet published</div>
            <p class="mb-0">Students cannot see these marks yet. <strong>Confirm and finalize</strong> to lock the sheet, make the marks official, and notify the students.</p>
        <?php elseif ($isFinalized): ?>
            <div class="next-step-title"><i class="bi bi-lock-fill text-dark"></i> Finalized</div>
            <p class="mb-0">Official and visible to students since <strong><?= htmlspecialchars($fmt($sheet['confirmed_at'] ?? null)) ?></strong><?= $approver ? ' (confirmed by ' . htmlspecialchars($approver) . ')' : '' ?>. This sheet is locked.<?= !empty($sheet['remarks']) ? ' Remarks: &ldquo;' . htmlspecialchars($sheet['remarks']) . '&rdquo;' : '' ?></p>
        <?php elseif ($isReturned): ?>
            <div class="next-step-title"><i class="bi bi-arrow-return-left text-danger"></i> Waiting for the instructor</div>
            <p class="mb-0">Returned on <strong><?= htmlspecialchars($fmt($sheet['returned_at'] ?? null)) ?></strong>. It will come back here when the instructor submits it again.<?= !empty($sheet['remarks']) ? ' Reason given: &ldquo;' . htmlspecialchars($sheet['remarks']) . '&rdquo;' : '' ?></p>
        <?php else: ?>
            <div class="next-step-title"><i class="bi bi-pencil text-secondary"></i> Not submitted yet</div>
            <p class="mb-0">The instructor is still encoding marks. You can review it once it is submitted.</p>
        <?php endif; ?>
    </div>

    <?php if ($isPending || $isApproved): ?>
        <div class="next-step-actions">
            <?php if ($isPending): ?>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveSheetModal">Approve sheet</button>
            <?php else: ?>
                <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#confirmSheetModal">Confirm and finalize</button>
            <?php endif; ?>
            <?php if ($canReturn): ?>
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#returnSheetModal">Return to instructor</button>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Class summary -->
<div class="summary-strip mb-4">
    <div class="summary-cell">
        <div class="summary-label">Marks entered</div>
        <div class="summary-value"><?= (int) $summary['graded'] ?> <span class="summary-note">of <?= (int) $summary['total'] ?></span></div>
        <div class="summary-note"><?= $summary['no_mark'] > 0 ? (int) $summary['no_mark'] . ' without a mark' : 'Everyone has a mark' ?></div>
    </div>
    <div class="summary-cell">
        <div class="summary-label">Class average</div>
        <div class="summary-value"><?= $summary['average'] !== null ? number_format((float) $summary['average'], 2) : '—' ?></div>
        <div class="summary-note"><?= $summary['highest'] !== null ? 'Low ' . number_format((float) $summary['lowest'], 2) . ' &bull; High ' . number_format((float) $summary['highest'], 2) : 'No marks yet' ?></div>
    </div>
    <div class="summary-cell">
        <div class="summary-label">Passed</div>
        <div class="summary-value"><?= (int) $summary['passed'] ?></div>
        <div class="summary-note">75 and above</div>
    </div>
    <div class="summary-cell">
        <div class="summary-label">Failed</div>
        <div class="summary-value <?= $summary['failed'] > 0 ? 'is-attention' : '' ?>"><?= (int) $summary['failed'] ?></div>
        <div class="summary-note"><?= $summary['near_line'] > 0 ? (int) $summary['near_line'] . ' within 2 points of passing' : 'Below 75' ?></div>
    </div>
</div>

<div class="row g-4">
    <!-- Marks roster -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h3 class="h6 mb-0 fw-semibold text-dark">Student Marks</h3>
                <small class="text-muted"><?= (int) $summary['total'] ?> students &bull; <?= htmlspecialchars($sheet['period_name']) ?></small>
            </div>

            <?php if (empty($students)): ?>
                <?php
                $icon = 'bi-people';
                $iconColor = 'blue';
                $title = 'No students on this sheet';
                $message = 'No students are enrolled in this course for this term.';
                include __DIR__ . '/../../components/empty-state.php';
                ?>
            <?php else: ?>
                <form id="editGradesForm" method="POST" action="<?= url('/dean/grade-review/' . $sheet['id'] . '/edit') ?>">
                    <?= csrf_field() ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 48px;">#</th>
                                    <th>Student</th>
                                    <th class="text-end" style="width: 150px;">Mark</th>
                                    <th style="width: 130px;">Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $index => $student): ?>
                                <?php
                                    $grade = $grades[$student['id']] ?? null;
                                    $hasGrade = ($grade !== null && $grade !== '');
                                    $mark = (float) ($grade ?? 0);
                                    $passed = $hasGrade && $mark >= 75.0;
                                    $nearLine = $hasGrade && $mark >= 73.0 && $mark < 75.0;
                                ?>
                                <tr>
                                    <td class="queue-meta tabular-nums"><?= $index + 1 ?></td>
                                    <td>
                                        <div class="text-dark"><?= htmlspecialchars(($student['last_name'] ?? '') . ', ' . ($student['first_name'] ?? '')) ?></div>
                                        <div class="queue-meta"><?= !empty($student['student_number']) ? htmlspecialchars($student['student_number']) : 'No student ID yet' ?></div>
                                    </td>
                                    <td class="text-end">
                                        <span class="mark-text tabular-nums <?= $hasGrade ? ($passed ? 'text-dark' : 'text-danger') : 'text-muted' ?>"><?= $hasGrade ? number_format($mark, 2) : '—' ?></span>
                                        <?php if ($canAdjust): ?>
                                            <input type="number" step="0.01" min="0" max="100"
                                                   class="mark-input form-control form-control-sm text-end tabular-nums ms-auto"
                                                   name="grades[<?= $student['id'] ?>]"
                                                   value="<?= $hasGrade ? htmlspecialchars((string) $grade) : '' ?>"
                                                   placeholder="0.00"
                                                   aria-label="Mark for <?= htmlspecialchars(($student['last_name'] ?? '') . ', ' . ($student['first_name'] ?? '')) ?>"
                                                   disabled>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!$hasGrade): ?>
                                            <span class="queue-meta">No mark</span>
                                        <?php elseif ($passed): ?>
                                            <span class="text-success small"><i class="bi bi-check-circle-fill"></i> Passed</span>
                                        <?php else: ?>
                                            <span class="text-danger small"><i class="bi bi-x-circle-fill"></i> Failed</span>
                                            <?php if ($nearLine): ?><i class="bi bi-flag-fill text-warning ms-1" title="Within 2 points of passing"></i><?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($canAdjust): ?>
                        <!-- Read mode -->
                        <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center gap-2" id="viewBar">
                            <span class="queue-meta">Spotted a wrong mark? Adjust it here; your reason is saved in the sheet history.</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnAdjust">Adjust marks</button>
                        </div>
                        <!-- Edit mode: one Save, with the required reason -->
                        <div class="card-footer bg-white border-top py-3 d-none" id="editBar">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg">
                                    <input type="text" name="reason" id="adjustReason" class="form-control form-control-sm" maxlength="200"
                                           placeholder="Reason for the adjustment (required), e.g. recomputed from the class record" disabled>
                                </div>
                                <div class="col-lg-auto d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCancelAdjust">Cancel</button>
                                    <button type="submit" class="btn btn-sm btn-primary">Save adjustments</button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sheet history -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h3 class="h6 mb-0 fw-semibold text-dark">Sheet History</h3>
                <small class="text-muted">Every step, newest first</small>
            </div>
            <div class="card-body pt-0">
                <?php if ($timeline === []): ?>
                    <p class="queue-meta mb-0">Nothing has happened to this sheet yet.</p>
                <?php else: ?>
                    <ol class="timeline">
                        <?php foreach ($timeline as $item): ?>
                            <?php $meta = $eventMeta[$item['action']] ?? [ucfirst(strtolower((string) $item['action'])), 'bi-circle', 'text-secondary']; ?>
                            <li class="timeline-item">
                                <i class="bi <?= $meta[1] ?> <?= $meta[2] ?> timeline-icon"></i>
                                <div>
                                    <div class="text-dark"><?= htmlspecialchars($meta[0]) ?><?= !empty($item['actor']) ? ' <span class="queue-meta">by ' . htmlspecialchars($item['actor']) . '</span>' : '' ?></div>
                                    <div class="queue-meta tabular-nums"><?= htmlspecialchars($fmt($item['at'])) ?></div>
                                    <?php if (!empty($item['note'])): ?>
                                        <div class="timeline-note"><?= nl2br(htmlspecialchars($item['note'])) ?></div>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($isPending): ?>
<!-- Approve -->
<div class="modal fade" id="approveSheetModal" tabindex="-1" aria-labelledby="approveSheetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= url('/dean/grade-review/approve') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="grading_sheet_id" value="<?= $sheet['id'] ?>">
                <input type="hidden" name="redirect_to" value="<?= $backHere ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveSheetModalLabel"><i class="bi bi-check-circle text-success"></i> Approve Grading Sheet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Approve the <strong><?= htmlspecialchars($sheet['period_name']) ?></strong> marks for <strong><?= htmlspecialchars($sheet['subject_code']) ?></strong>?</p>
                    <p class="queue-meta mb-0">Students will not see the marks yet. After approving, you still confirm and finalize the sheet to publish it.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve sheet</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canReturn): ?>
<!-- Return -->
<div class="modal fade" id="returnSheetModal" tabindex="-1" aria-labelledby="returnSheetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= url('/dean/grade-review/return') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="grading_sheet_id" value="<?= $sheet['id'] ?>">
                <input type="hidden" name="redirect_to" value="<?= $backHere ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="returnSheetModalLabel"><i class="bi bi-arrow-return-left text-danger"></i> Return to Instructor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="returnReason" class="form-label">What needs to be corrected? <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="returnReason" name="reason" rows="4" maxlength="500" required
                              placeholder="e.g. Two students' Midterm marks look swapped. Please recheck and resubmit."></textarea>
                    <div class="form-text">The instructor receives this reason by email and sees it on the sheet. The sheet stays locked for them until you return it.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Return sheet</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($isApproved): ?>
<!-- Confirm and finalize -->
<div class="modal fade" id="confirmSheetModal" tabindex="-1" aria-labelledby="confirmSheetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= url('/dean/grade-review/confirm') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="grading_sheet_id" value="<?= $sheet['id'] ?>">
                <input type="hidden" name="redirect_to" value="<?= $backHere ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmSheetModalLabel"><i class="bi bi-shield-check text-primary"></i> Confirm and Finalize: <?= htmlspecialchars($sheet['subject_code']) ?> &middot; <?= htmlspecialchars($sheet['period_name']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-exclamation-triangle-fill fs-6 flex-shrink-0 mt-1"></i>
                        <div><strong>This cannot be undone.</strong> The marks become official, the sheet is locked, and the enrolled students are notified and can see their marks.</div>
                    </div>
                    <label for="remarks" class="form-label">Confirmation notes (optional)</label>
                    <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="e.g. Official rating confirmed for registrar filing."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">Confirm and finalize</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canAdjust): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('editGradesForm');
    var btnAdjust = document.getElementById('btnAdjust');
    var btnCancel = document.getElementById('btnCancelAdjust');
    var viewBar = document.getElementById('viewBar');
    var editBar = document.getElementById('editBar');
    var reason = document.getElementById('adjustReason');
    if (!form || !btnAdjust) return;

    function setEditing(on) {
        form.classList.toggle('is-editing', on);
        viewBar.classList.toggle('d-none', on);
        editBar.classList.toggle('d-none', !on);
        reason.disabled = !on;
        reason.required = on;
        form.querySelectorAll('.mark-input').forEach(function (i) { i.disabled = !on; });
        if (on) {
            var first = form.querySelector('.mark-input');
            if (first) first.focus();
        } else {
            form.reset();
        }
    }

    btnAdjust.addEventListener('click', function () { setEditing(true); });
    btnCancel.addEventListener('click', function () { setEditing(false); });

    // Mark which marks were actually changed
    form.querySelectorAll('.mark-input').forEach(function (i) {
        i.dataset.original = i.value;
        i.addEventListener('input', function () { i.classList.toggle('is-changed', i.value !== i.dataset.original); });
    });
});
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
