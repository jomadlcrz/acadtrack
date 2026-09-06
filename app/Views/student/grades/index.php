<?php
$pageTitle = 'Student Grades';
ob_start();
?>

<div class="page-header">
    <h2>My Grades</h2>
</div>

<?php if (empty($summary)): ?>
    <div class="empty-state">
        <p>No grades available yet.</p>
    </div>
<?php else: ?>
<table class="table">
    <thead>
        <tr>
            <th>Subject</th>
            <?php
            $periodNames = [];
            foreach ($summary as $subject) {
                foreach ($subject['periods'] as $name => $grade) {
                    if (!in_array($name, $periodNames)) {
                        $periodNames[] = $name;
                    }
                }
            }
            foreach ($periodNames as $period): ?>
                <th><?= htmlspecialchars($period) ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($summary as $subjectCode => $subject): ?>
        <tr>
            <td><?= htmlspecialchars($subjectCode . ' - ' . $subject['subject_name']) ?></td>
            <?php foreach ($periodNames as $period): ?>
                <td><?= $subject['periods'][$period] ?? '-' ?></td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php
$content = ob_get_clean();
$pageTitle = 'My Grades';
include __DIR__ . '/../../layouts/dashboard.php';
?>
