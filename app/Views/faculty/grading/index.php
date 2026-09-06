<?php
$pageTitle = 'Grade Encoding Sheet';
$subtitle = 'Encode and review student performance marks for the designated academic grading period.';
$headerActions = '<a href="' . url('/faculty/subjects') . '" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"><i class="bi bi-arrow-left"></i> Back to subjects</a>';
ob_start();
$isLocked = in_array($gradingSheet['status'] ?? '', ['SUBMITTED', 'APPROVED']);
?>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="card-body py-3 px-4">
        <form method="GET" action="<?= url('/faculty/grading') ?>" class="row g-3 align-items-center">
            <div class="col-md-3">
                <label for="semester" class="form-label mb-1 fw-semibold small text-muted d-flex align-items-center gap-1">
                    <i class="bi bi-calendar3 text-primary"></i> Semester:
                </label>
                <select id="semester" name="semester" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="1" <?= ($selectedSemester ?? '1') === '1' ? 'selected' : '' ?>>1st Semester</option>
                    <option value="2" <?= ($selectedSemester ?? '1') === '2' ? 'selected' : '' ?>>2nd Semester</option>
                </select>
            </div>

            <div class="col-md-4">
                <label for="subject_id" class="form-label mb-1 fw-semibold small text-muted d-flex align-items-center gap-1">
                    <i class="bi bi-journal-bookmark text-primary"></i> Subject:
                </label>
                <select id="subject_id" name="subject_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php if (empty($assignedSubjects)): ?>
                        <option value="">No subjects assigned</option>
                    <?php else: ?>
                        <?php foreach ($assignedSubjects as $subj): ?>
                            <option value="<?= $subj['id'] ?>" <?= $subj['id'] == $subjectId ? 'selected' : '' ?>>
                                <?= htmlspecialchars($subj['code'] . ' - ' . $subj['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="period_id" class="form-label mb-1 fw-semibold small text-muted d-flex align-items-center gap-1">
                    <i class="bi bi-calendar-event text-primary"></i> Grading period:
                </label>
                <select id="period_id" name="period_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php foreach ($periods as $period): ?>
                        <option value="<?= $period['id'] ?>" <?= $period['id'] == $periodId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($period['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 text-md-end pt-md-3">
                <?php if ($gradingSheet): ?>
                    <span class="badge badge-<?= strtolower($gradingSheet['status']) ?> fs-6 py-2 px-3">
                        <?php if ($gradingSheet['status'] === 'DRAFT'): ?>
                            <i class="bi bi-pencil-square me-1"></i> Draft
                        <?php elseif ($gradingSheet['status'] === 'SUBMITTED'): ?>
                            <i class="bi bi-lock-fill me-1"></i> Submitted
                        <?php elseif ($gradingSheet['status'] === 'APPROVED'): ?>
                            <i class="bi bi-check-circle-fill me-1"></i> Approved
                        <?php elseif ($gradingSheet['status'] === 'RETURNED'): ?>
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Returned
                        <?php else: ?>
                            <?= htmlspecialchars(ucfirst(strtolower($gradingSheet['status']))) ?>
                        <?php endif; ?>
                    </span>
                <?php else: ?>
                    <span class="badge bg-secondary-subtle text-secondary border fs-6 py-2 px-3">
                        <i class="bi bi-dash-circle me-1"></i> Unsaved
                    </span>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if ($gradingSheet && $gradingSheet['status'] === 'SUBMITTED'): ?>
    <div class="alert alert-info d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-info-circle-fill fs-5"></i>
        <div>
            <strong class="fw-semibold">Grading sheet submitted.</strong> This sheet is currently locked under Dean review. Editing is disabled until approved or returned.
        </div>
    </div>
<?php elseif ($gradingSheet && $gradingSheet['status'] === 'RETURNED'): ?>
    <div class="alert alert-warning d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-arrow-counterclockwise fs-5"></i>
        <div>
            <strong class="fw-semibold">Grading sheet returned for revision.</strong> Please inspect the scores below, apply the necessary revisions, and submit for review once finalized.
        </div>
    </div>
<?php elseif ($gradingSheet && $gradingSheet['status'] === 'APPROVED'): ?>
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-check-circle-fill fs-5"></i>
        <div>
            <strong class="fw-semibold">Grading sheet approved.</strong> Scores have been officially verified and locked into permanent student academic records.
        </div>
    </div>
<?php endif; ?>

<?php if (empty($students)): ?>
    <div class="card shadow-sm border-0 text-center py-5" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
        <div class="card-body">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-3" style="width: 52px; height: 52px; font-size: 24px;">
                <i class="bi bi-people"></i>
            </div>
            <h4 class="h6 fw-semibold text-dark mb-1">No enrolled students found</h4>
            <p class="text-muted small mb-0">There are currently no students registered for this course section.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <h3 class="h6 mb-0 fw-semibold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-table text-primary"></i> Student Grade Roster
                </h3>
                <span class="text-muted small">(<?= count($students) ?> enrolled students)</span>
            </div>
            <small class="text-muted">
                <i class="bi bi-keyboard me-1"></i> Use <kbd class="bg-secondary-subtle text-dark px-1 rounded">Tab</kbd> to move across rows
            </small>
        </div>

        <form id="saveGradesForm" method="POST" action="<?= url('/faculty/grading/save') ?>" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="subject_id" value="<?= htmlspecialchars((string)$subjectId) ?>">
            <input type="hidden" name="grading_period_id" value="<?= htmlspecialchars((string)$periodId) ?>">
            <input type="hidden" name="academic_term_id" value="<?= htmlspecialchars((string)($academicTerm['id'] ?? 1)) ?>">
            <input type="hidden" name="semester" value="<?= htmlspecialchars((string)($selectedSemester ?? '1')) ?>">

            <div class="table-responsive" style="max-height: 65vh;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom sticky-top">
                        <tr>
                            <th class="fw-semibold text-muted small py-3 px-3" style="width: 160px;">Student ID</th>
                            <th class="fw-semibold text-muted small py-3 px-3">Student name</th>
                            <th class="fw-semibold text-muted small py-3 px-3 text-end" style="width: 180px;">Raw score (0.00 – 100.00)</th>
                            <th class="fw-semibold text-muted small py-3 px-3 text-center" style="width: 140px;">Entry status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <?php $score = $grades[$student['id']] ?? ''; ?>
                        <tr>
                            <td class="px-3 fw-semibold text-secondary font-monospace small">
                                <i class="bi bi-person-vcard me-1 text-muted"></i>
                                <?= htmlspecialchars($student['student_number']) ?>
                            </td>
                            <td class="px-3 fw-semibold text-dark">
                                <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                            </td>
                            <td class="px-3 text-end">
                                <input type="number" 
                                       name="grades[<?= $student['id'] ?>]" 
                                       value="<?= htmlspecialchars((string)$score) ?>"
                                       min="0" max="100" step="0.01"
                                       class="form-control form-control-sm grade-input d-inline-block text-end font-monospace"
                                       placeholder="—"
                                       <?= $isLocked ? 'disabled' : '' ?>>
                            </td>
                            <td class="px-3 text-center">
                                <?php if ($score !== '' && $score !== null): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check2"></i> Encoded
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                        Pending
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-light py-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted d-flex align-items-center gap-1">
                    <i class="bi bi-shield-check text-primary"></i> Scores are saved as working draft until officially submitted.
                </small>
                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2" <?= $isLocked ? 'disabled' : '' ?>>
                    <i class="bi bi-save"></i> Save draft
                </button>
            </div>
        </form>
    </div>

    <?php if ($gradingSheet && in_array($gradingSheet['status'], ['DRAFT', 'RETURNED'])): ?>
        <div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
            <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="h6 fw-semibold text-dark mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-send-check text-primary"></i> Submit Sheet for Dean Approval
                    </h4>
                    <p class="text-muted small mb-0">
                        Once submitted, score encoding will be locked and routed directly to the Dean Review Queue.
                    </p>
                </div>
                <form method="POST" action="<?= url('/faculty/grading/submit') ?>" class="m-0" onsubmit="return confirm('Are you sure you want to submit this grading sheet for review? Once submitted, student score fields will be locked.');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="grading_sheet_id" value="<?= htmlspecialchars((string)$gradingSheet['id']) ?>">
                    <button type="submit" class="btn btn-warning d-inline-flex align-items-center gap-2 fw-semibold">
                        <i class="bi bi-send"></i> Submit for review
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
