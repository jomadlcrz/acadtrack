<?php
$pageTitle = 'Dean Dashboard';
ob_start();
?>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-value"><?= $pendingReviewCount ?? 0 ?></div>
        <div class="stat-label">Pending Reviews</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $totalSubjects ?? 0 ?></div>
        <div class="stat-label">Subjects</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $totalFaculty ?? 0 ?></div>
        <div class="stat-label">Faculty Members</div>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Dean Dashboard';
include __DIR__ . '/../layouts/dashboard.php';
?>
