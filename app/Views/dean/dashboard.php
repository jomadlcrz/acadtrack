<?php
$pageTitle = 'Dean Dashboard';
ob_start();
?>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-card-top">
            <span class="stat-label">Pending reviews</span>
            <div class="stat-icon stat-icon-amber"><i class="bi bi-file-earmark-check-fill"></i></div>
        </div>
        <div class="stat-value tabular-nums"><?= $pendingReviewCount ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-top">
            <span class="stat-label">Curriculum subjects</span>
            <div class="stat-icon stat-icon-blue"><i class="bi bi-journal-bookmark-fill"></i></div>
        </div>
        <div class="stat-value tabular-nums"><?= $totalSubjects ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-top">
            <span class="stat-label">Faculty members</span>
            <div class="stat-icon stat-icon-green"><i class="bi bi-person-badge-fill"></i></div>
        </div>
        <div class="stat-value tabular-nums"><?= $totalFaculty ?? 0 ?></div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard.php';
?>
