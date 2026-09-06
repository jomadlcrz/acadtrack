<?php
$pageTitle = 'Faculty Dashboard';
ob_start();
?>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-card-top">
            <span class="stat-label">Assigned subjects</span>
            <div class="stat-icon stat-icon-blue"><i class="bi bi-journal-text"></i></div>
        </div>
        <div class="stat-value tabular-nums"><?= $assignedSubjectsCount ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-top">
            <span class="stat-label">Total students</span>
            <div class="stat-icon stat-icon-green"><i class="bi bi-people-fill"></i></div>
        </div>
        <div class="stat-value tabular-nums"><?= $totalStudents ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-top">
            <span class="stat-label">Submitted sheets</span>
            <div class="stat-icon stat-icon-amber"><i class="bi bi-file-earmark-check-fill"></i></div>
        </div>
        <div class="stat-value tabular-nums"><?= $submittedCount ?? 0 ?></div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Faculty Dashboard';
include __DIR__ . '/../layouts/dashboard.php';
?>
