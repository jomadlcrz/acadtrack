<?php
$pageTitle = 'My Subjects';
ob_start();
?>

<div class="page-header">
    <h2>My Assigned Subjects</h2>
</div>

<?php if (empty($subjects)): ?>
    <div class="empty-state">
        <p>No subjects assigned yet.</p>
    </div>
<?php else: ?>
<table class="table">
    <thead>
        <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Year Level</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($subjects as $subject): ?>
        <tr>
            <td><?= htmlspecialchars($subject['code']) ?></td>
            <td><?= htmlspecialchars($subject['name']) ?></td>
            <td><?= $subject['year_level'] ?></td>
            <td>
                <a href="/faculty/grading?subject_id=<?= $subject['id'] ?>" class="btn btn-sm">Enter Grades</a>
                <a href="/faculty/students?subject_id=<?= $subject['id'] ?>" class="btn btn-sm">View Students</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php
$content = ob_get_clean();
$pageTitle = 'My Subjects';
include __DIR__ . '/../../layouts/dashboard.php';
?>
