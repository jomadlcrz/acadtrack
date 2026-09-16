<?php
$pageTitle = 'My Academic Grades';
$subtitle = 'Official grade report across all completed and ongoing curricular grading periods.';
$headerActions = '<a href="' . url('/student/evaluation') . '" class="btn btn-primary d-inline-flex align-items-center gap-2"><i class="bi bi-award"></i> View whole evaluation</a>';
ob_start();
?>

<!-- Semester Switcher Toolbar -->
<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="card-body py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small fw-semibold">Academic term:</span>
            <span class="fw-semibold text-dark small"><?= htmlspecialchars($academicTerm['academic_year_name'] ?? '2026-2027') ?></span>
        </div>
        <div class="btn-group btn-group-sm" role="group" aria-label="Semester selection">
            <a href="<?= url('/student/grades?semester=1') ?>" class="btn <?= ($selectedSemester ?? '1') === '1' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                1st Semester
            </a>
            <a href="<?= url('/student/grades?semester=2') ?>" class="btn <?= ($selectedSemester ?? '1') === '2' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                2nd Semester
            </a>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h3 class="h6 mb-0 fw-semibold text-dark"><?= ($selectedSemester ?? '1') === '2' ? '2nd Semester' : '1st Semester' ?> Grade Summary</h3>
        <span class="badge badge-student">Active enrollment</span>
    </div>

    <?php if (empty($summary)): ?>
        <?php
        $icon = 'bi-mortarboard';
        $iconColor = 'blue';
        $title = 'No grades available yet';
        $message = 'Your course instructors have not published approved marks for the current semester.';
        include __DIR__ . '/../../components/empty-state.php';
        ?>
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
                <thead class="bg-white border-bottom">
                    <tr>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Subject code &amp; title</th>
                        <?php foreach ($periodNames as $period): ?>
                            <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 140px; font-size: 11px;">
                                <?= htmlspecialchars($period) ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($summary as $subjectCode => $subject): ?>
                    <tr>
                        <td class="px-3">
                            <div class="fw-semibold text-dark font-monospace small">
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
