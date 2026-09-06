<?php
$pageTitle = 'Admin Dashboard';
ob_start();
?>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-value"><?= $totalUsers ?? 0 ?></div>
        <div class="stat-label">Total Users</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $totalStudents ?? 0 ?></div>
        <div class="stat-label">Students</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $totalFaculty ?? 0 ?></div>
        <div class="stat-label">Faculty</div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Admin Dashboard';
include __DIR__ . '/../layouts/dashboard.php';
?>
