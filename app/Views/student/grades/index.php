<?php
$pageTitle = 'My Academic Grades';
$subtitle = 'Official grade report across all completed and ongoing curricular grading periods.';
ob_start();
?>

<div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h3 class="h6 mb-0 fw-semibold text-dark d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-text text-primary"></i> Term Grade Summary
        </h3>
        <span class="badge badge-student">Active enrollment</span>
    </div>

    <?php if (empty($summary)): ?>
        <div class="card-body text-center py-5">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-3" style="width: 52px; height: 52px; font-size: 24px;">
                <i class="bi bi-mortarboard"></i>
            </div>
            <h4 class="h6 fw-semibold text-dark mb-1">No grades available yet</h4>
            <p class="text-muted small mb-0">Your course instructors have not published approved marks for the current semester.</p>
        </div>
    <?php else: ?>
        <?php
        $periodNames = [];
        foreach ($summary as $subject) {
            foreach ($subject['periods'] as $name => $grade) {
                if (!in_array($name, $periodNames)) {
                    $periodNames[] = $name;
                }
            }
        }
        ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light border-bottom">
                    <tr>
                        <th class="fw-semibold text-muted small py-3 px-3">Subject code & title</th>
                        <?php foreach ($periodNames as $period): ?>
                            <th class="fw-semibold text-muted small py-3 px-3 text-center" style="width: 140px;">
                                <?= htmlspecialchars($period) ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($summary as $subjectCode => $subject): ?>
                    <tr>
                        <td class="px-3">
                            <div class="fw-semibold text-primary font-monospace small">
                                <?= htmlspecialchars($subjectCode) ?>
                            </div>
                            <div class="fw-semibold text-dark small">
                                <?= htmlspecialchars($subject['subject_name']) ?>
                            </div>
                        </td>
                        <?php foreach ($periodNames as $period): ?>
                            <?php $val = $subject['periods'][$period] ?? null; ?>
                            <td class="px-3 text-center font-monospace fw-semibold">
                                <?php if ($val !== null && $val !== ''): ?>
                                    <span class="text-dark small">
                                        <?= number_format((float)$val, 2) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
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
