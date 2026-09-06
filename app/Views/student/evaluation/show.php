<?php
$pageTitle = 'Evaluation';
ob_start();
?>

<div class="page-header">
    <h2>Academic Evaluation</h2>
</div>

<?php if (empty($evaluations)): ?>
    <div class="empty-state">
        <p>No evaluations available yet.</p>
    </div>
<?php else: ?>
<table class="table">
    <thead>
        <tr>
            <th>Subject</th>
            <th>Average</th>
            <th>Status</th>
            <th>Remarks</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($evaluations as $subjectCode => $eval): ?>
        <tr>
            <td><?= htmlspecialchars($subjectCode . ' - ' . $eval['subject_name']) ?></td>
            <td><?= number_format($eval['average'], 2) ?></td>
            <td><span class="badge badge-<?= strtolower(str_replace(' ', '-', $eval['status'])) ?>"><?= htmlspecialchars($eval['status']) ?></span></td>
            <td><?= htmlspecialchars($eval['remarks']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php
$content = ob_get_clean();
$pageTitle = 'Evaluation';
include __DIR__ . '/../../layouts/dashboard.php';
?>
