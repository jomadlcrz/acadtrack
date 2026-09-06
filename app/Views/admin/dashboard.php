<?php
$pageTitle = 'Admin Dashboard';
ob_start();
?>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-card-top">
            <span class="stat-label">Total users</span>
            <div class="stat-icon stat-icon-blue"><i class="bi bi-people-fill"></i></div>
        </div>
        <div class="stat-value tabular-nums"><?= $totalUsers ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-top">
            <span class="stat-label">Registered students</span>
            <div class="stat-icon stat-icon-purple"><i class="bi bi-mortarboard-fill"></i></div>
        </div>
        <div class="stat-value tabular-nums"><?= $totalStudents ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-top">
            <span class="stat-label">Teaching faculty</span>
            <div class="stat-icon stat-icon-green"><i class="bi bi-person-badge-fill"></i></div>
        </div>
        <div class="stat-value tabular-nums"><?= $totalFaculty ?? 0 ?></div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard.php';
?>
