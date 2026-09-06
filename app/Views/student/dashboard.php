<?php
$pageTitle = 'Student Dashboard';
ob_start();
?>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-card-top">
            <span class="stat-label">Student name</span>
            <div class="stat-icon stat-icon-blue"><i class="bi bi-person-circle"></i></div>
        </div>
        <div class="stat-value" style="font-size: 20px;"><?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-top">
            <span class="stat-label">Student number</span>
            <div class="stat-icon stat-icon-green"><i class="bi bi-card-text"></i></div>
        </div>
        <div class="stat-value tabular-nums"><?= htmlspecialchars($user['student_number'] ?? 'N/A') ?></div>
    </div>
</div>

<div class="content-card">
    <div class="content-card-header">
        <h3 class="content-card-title">Student Portal Quick Actions</h3>
    </div>
    <p style="margin-bottom: 16px; color: #475569; font-size: 13.5px;">View your enrolled subjects, periodic scores (Prelim, Midterm, Semi-final, Final), and your complete curriculum evaluation progress.</p>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="<?= url('/student/grades') ?>" class="btn btn-primary">
            <i class="bi bi-award-fill me-1"></i>
            <span>View my grades</span>
        </a>
        <a href="<?= url('/student/evaluation') ?>" class="btn">
            <i class="bi bi-mortarboard-fill me-1"></i>
            <span>View whole evaluation</span>
        </a>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard.php';
?>