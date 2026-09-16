<?php
$pageTitle = 'Academic Evaluation';
$subtitle = 'Official curricular evaluation, weighted averages, and academic standing.';
$headerActions = '<a href="' . url('/student/grades') . '" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"><i class="bi bi-arrow-left"></i> Back to grades</a>';
ob_start();
?>

<div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h3 class="h6 mb-0 fw-semibold text-dark">Curriculum Evaluation Matrix</h3>
        <div class="d-flex align-items-center gap-2">
            <div class="btn-group btn-group-sm" role="group" aria-label="Semester selection">
                <a href="<?= url('/student/evaluation') ?>" class="btn <?= empty($selectedSemester) ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    Whole Evaluation
                </a>
                <a href="<?= url('/student/evaluation?semester=1') ?>" class="btn <?= ($selectedSemester ?? '') === '1' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    1st Semester
                </a>
                <a href="<?= url('/student/evaluation?semester=2') ?>" class="btn <?= ($selectedSemester ?? '') === '2' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    2nd Semester
                </a>
            </div>
        </div>
    </div>

    <?php if (empty($evaluations)): ?>
        <?php
        $icon = 'bi-award';
        $iconColor = 'blue';
        $title = 'No evaluation records available';
        $message = 'Finalized course evaluations will appear here once term grading is officially concluded.';
        include __DIR__ . '/../../components/empty-state.php';
        ?>
    <?php else: ?>
        <?php
            $totalSum = 0;
            $count = 0;
            $passedCount = 0;
            foreach ($evaluations as $e) {
                $avg = (float)($e['average'] ?? 0);
                if ($avg > 0) {
                    $totalSum += $avg;
                    $count++;
                    if ($avg >= 75.0 && !in_array(strtoupper($e['status'] ?? ''), ['FAILING', 'NO GRADES', 'NEEDS IMPROVEMENT'])) {
                        $passedCount++;
                    }
                }
            }
            $overallGwa = $count > 0 ? round($totalSum / $count, 2) : 0.0;
        ?>

        <div class="card-body bg-light border-bottom py-3 px-4">
            <div class="row g-3 text-center">
                <div class="col-6 col-md-3">
                    <span class="text-muted small d-block">Evaluated subjects</span>
                    <span class="h5 fw-semibold text-dark mb-0"><?= $count ?></span>
                </div>
                <div class="col-6 col-md-3">
                    <span class="text-muted small d-block">General Weighted Average (GWA)</span>
                    <span class="h5 fw-semibold text-primary mb-0 font-monospace"><?= number_format($overallGwa, 2) ?></span>
                </div>
                <div class="col-6 col-md-3">
                    <span class="text-muted small d-block">Academic status</span>
                    <span class="badge <?= ($overallGwa >= 75.0 && $passedCount === $count) ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' ?> fs-6 fw-semibold py-1 px-3">
                        <?= ($overallGwa >= 75.0 && $passedCount === $count) ? 'Good Academic Standing' : 'Academic Warning' ?>
                    </span>
                </div>
                <div class="col-6 col-md-3">
                    <span class="text-muted small d-block">Attendance record</span>
                    <?php if (!empty($attendanceSummary)): ?>
                        <?php if (!empty($attendanceSummary['has_warning'])): ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-6 fw-semibold py-1 px-3">
                                ⚠️ <?= $attendanceSummary['absent_count'] + $attendanceSummary['excused_count'] ?> Absences
                            </span>
                        <?php else: ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle fs-6 fw-semibold py-1 px-3">
                                <i class="bi bi-check-circle me-1"></i> <?= $attendanceSummary['attendance_percentage'] ?>% Present
                            </span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle fs-6 fw-semibold py-1 px-3">
                            No records
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-white border-bottom">
                    <tr>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Subject code &amp; title</th>
                        <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 160px; font-size: 11px;">Computed average</th>
                        <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 150px; font-size: 11px;">Status</th>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($evaluations as $subjectCode => $eval): ?>
                    <?php 
                        $status = strtoupper($eval['status'] ?? '');
                        $average = (float)($eval['average'] ?? 0);
                        $isPassed = !in_array($status, ['FAILING', 'NO GRADES', 'NEEDS IMPROVEMENT']) && $average >= 75.0;
                    ?>
                    <tr>
                        <td class="px-3">
                            <div class="fw-semibold text-dark font-monospace small">
                                <?= htmlspecialchars($subjectCode) ?>
                            </div>
                            <div class="fw-semibold text-dark small">
                                <?= htmlspecialchars($eval['subject_name']) ?>
                            </div>
                        </td>
                        <td class="px-3 text-center font-monospace fw-semibold fs-6">
                            <?= number_format((float)$eval['average'], 2) ?>
                        </td>
                        <td class="px-3 text-center">
                            <span class="badge <?= $isPassed ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' ?>">
                                <i class="bi bi-<?= $isPassed ? 'check-circle' : 'x-circle' ?> me-1"></i>
                                <?= htmlspecialchars($eval['status']) ?>
                            </span>
                        </td>
                        <td class="px-3 small text-secondary">
                            <?= htmlspecialchars($eval['remarks'] ?? '—') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
