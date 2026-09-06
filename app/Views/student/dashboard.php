<?php
$pageTitle = 'Student Dashboard';
ob_start();
?>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-value"><?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></div>
        <div class="stat-label">Welcome Back</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= htmlspecialchars($user['student_number'] ?? 'N/A') ?></div>
        <div class="stat-label">Student Number</div>
    </div>
</div>

<div class="card" style="margin-top: 20px; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <h3 style="margin-bottom: 10px;">Student Portal</h3>
    <p style="margin-bottom: 15px; color: #555;">View your enrolled subjects, preliminary, midterm, and final grades, or review your curriculum evaluations.</p>
    <div style="display: flex; gap: 10px;">
        <a href="/student/grades" class="btn btn-primary">View My Grades</a>
        <a href="/student/evaluation" class="btn">View Curriculum Evaluation</a>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Student Dashboard';
include __DIR__ . '/../layouts/dashboard.php';
?>