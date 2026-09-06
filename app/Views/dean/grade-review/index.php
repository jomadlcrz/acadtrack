<?php
$pageTitle = 'Grade Review';
ob_start();
?>

<div class="page-header">
    <h2>Pending Grade Reviews</h2>
</div>

<?php if (empty($gradingSheets)): ?>
    <div class="empty-state">
        <p>No grading sheets pending review.</p>
    </div>
<?php else: ?>
<table class="table">
    <thead>
        <tr>
            <th>Subject</th>
            <th>Period</th>
            <th>Faculty</th>
            <th>Submitted</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($gradingSheets as $sheet): ?>
        <tr>
            <td><?= htmlspecialchars($sheet['subject_code'] . ' - ' . $sheet['subject_name']) ?></td>
            <td><?= htmlspecialchars($sheet['period_name']) ?></td>
            <td><?= htmlspecialchars($sheet['first_name'] . ' ' . $sheet['last_name']) ?></td>
            <td><?= htmlspecialchars($sheet['submitted_at'] ?? 'N/A') ?></td>
            <td><span class="badge badge-<?= strtolower($sheet['status']) ?>"><?= htmlspecialchars($sheet['status']) ?></span></td>
            <td>
                <form method="POST" action="/dean/grade-review/approve" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="grading_sheet_id" value="<?= $sheet['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                </form>
                <form method="POST" action="/dean/grade-review/return" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="grading_sheet_id" value="<?= $sheet['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-warning">Return</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php
$content = ob_get_clean();
$pageTitle = 'Grade Review';
include __DIR__ . '/../../layouts/dashboard.php';
?>
