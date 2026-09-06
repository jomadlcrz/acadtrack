<?php
$pageTitle = 'Students';
ob_start();
?>

<div class="page-header">
    <h2>Enrolled Students</h2>
</div>

<?php if (empty($students)): ?>
    <div class="empty-state">
        <p>No students enrolled in this subject.</p>
    </div>
<?php else: ?>
<table class="table">
    <thead>
        <tr>
            <th>Student Number</th>
            <th>Name</th>
            <th>Email</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($students as $student): ?>
        <tr>
            <td><?= htmlspecialchars($student['student_number']) ?></td>
            <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
            <td><?= htmlspecialchars($student['email']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php
$content = ob_get_clean();
$pageTitle = 'Students';
include __DIR__ . '/../../layouts/dashboard.php';
?>
