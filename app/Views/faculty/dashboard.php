<?php
$pageTitle = 'Faculty Dashboard';
ob_start();
?>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-value"><?= $assignedSubjectsCount ?? 0 ?></div>
        <div class="stat-label">Assigned Subjects</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $totalStudents ?? 0 ?></div>
        <div class="stat-label">Total Students</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $submittedCount ?? 0 ?></div>
        <div class="stat-label">Submitted Sheets</div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Faculty Dashboard';
include __DIR__ . '/../layouts/dashboard.php';
?>
