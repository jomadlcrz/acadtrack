<?php
$pageTitle = 'Admin Dashboard';
$subtitle = 'System overview, user account metrics, and institutional administration.';
ob_start();
?>

<!-- Metric KPI Cards (Clickable & Dynamic) -->
<div class="dashboard-stats">
    <a href="<?= url('/admin/users') ?>" class="stat-card stat-card-link">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Total users</span>
                <div class="stat-icon stat-icon-blue"><i class="bi bi-people-fill"></i></div>
            </div>
            <div class="stat-value tabular-nums"><?= number_format($totalUsers ?? 0) ?></div>
        </div>
        <span class="stat-subtext">Manage all accounts <i class="bi bi-arrow-right"></i></span>
    </a>

    <a href="<?= url('/admin/users?role=Student') ?>" class="stat-card stat-card-link">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Registered students</span>
                <div class="stat-icon stat-icon-purple"><i class="bi bi-mortarboard-fill"></i></div>
            </div>
            <div class="stat-value tabular-nums"><?= number_format($totalStudents ?? 0) ?></div>
        </div>
        <span class="stat-subtext">View student roster <i class="bi bi-arrow-right"></i></span>
    </a>

    <a href="<?= url('/admin/users?role=Faculty') ?>" class="stat-card stat-card-link">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Teaching faculty</span>
                <div class="stat-icon stat-icon-green"><i class="bi bi-person-badge-fill"></i></div>
            </div>
            <div class="stat-value tabular-nums"><?= number_format($totalFaculty ?? 0) ?></div>
        </div>
        <span class="stat-subtext">View faculty list <i class="bi bi-arrow-right"></i></span>
    </a>

    <a href="<?= url('/admin/departments') ?>" class="stat-card stat-card-link">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Departments</span>
                <div class="stat-icon stat-icon-amber"><i class="bi bi-building"></i></div>
            </div>
            <div class="stat-value tabular-nums"><?= number_format($totalDepts ?? 0) ?></div>
        </div>
        <span class="stat-subtext">Manage departments <i class="bi bi-arrow-right"></i></span>
    </a>
</div>

<!-- Quick Administration Shortcuts -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <a href="<?= url('/admin/users/create') ?>" class="quick-action-card">
            <div class="quick-action-icon bg-primary-subtle text-primary">
                <i class="bi bi-person-plus-fill"></i>
            </div>
            <div>
                <h4 class="h6 mb-0 fw-semibold">Add new user</h4>
                <p class="text-muted small mb-0">Create student, faculty, or dean</p>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= url('/admin/departments') ?>" class="quick-action-card">
            <div class="quick-action-icon bg-success-subtle text-success">
                <i class="bi bi-building-add"></i>
            </div>
            <div>
                <h4 class="h6 mb-0 fw-semibold">Department catalog</h4>
                <p class="text-muted small mb-0">Manage college departments</p>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= url('/admin/settings') ?>" class="quick-action-card">
            <div class="quick-action-icon bg-warning-subtle text-warning-emphasis">
                <i class="bi bi-sliders"></i>
            </div>
            <div>
                <h4 class="h6 mb-0 fw-semibold">Grading settings</h4>
                <p class="text-muted small mb-0">Configure grading formulas &amp; terms</p>
            </div>
        </a>
    </div>
</div>

<!-- Main Dashboard Grid -->
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h3 class="h6 mb-0 fw-semibold text-dark">Recently Created Accounts</h3>
                <a href="<?= url('/admin/users') ?>" class="btn btn-sm btn-outline-secondary">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="fw-semibold text-muted small py-3 px-3">User</th>
                            <th class="fw-semibold text-muted small py-3 px-3">Role</th>
                            <th class="fw-semibold text-muted small py-3 px-3 text-center">Status</th>
                            <th class="fw-semibold text-muted small py-3 px-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentUsers)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted small">No users found in database.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentUsers as $ru): ?>
                                <tr>
                                    <td class="px-3">
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($ru['first_name'] . ' ' . $ru['last_name']) ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($ru['email']) ?></div>
                                    </td>
                                    <td class="px-3">
                                        <span class="badge badge-<?= strtolower($ru['role']) ?>"><?= htmlspecialchars($ru['role']) ?></span>
                                    </td>
                                    <td class="px-3 text-center">
                                        <?php if (($ru['status'] ?? 'active') === 'inactive'): ?>
                                            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                                <i class="bi bi-dash-circle me-1"></i>Inactive
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="bi bi-check-circle me-1"></i>Active
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 text-end">
                                        <a href="<?= url('/admin/users/' . $ru['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary py-1 px-2">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
            <div class="card-header bg-white py-3 border-bottom">
                <h3 class="h6 mb-0 fw-semibold text-dark">Institutional Term</h3>
            </div>
            <div class="card-body py-3 px-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="stat-icon stat-icon-blue" style="width: 44px; height: 44px; font-size: 20px;">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div>
                        <div class="fw-semibold text-dark fs-6"><?= htmlspecialchars($activeTerm['academic_year_name'] ?? '2026-2027') ?></div>
                        <div class="text-muted small"><?= ($activeTerm['semester'] ?? '1') === '1' ? '1st Semester' : '2nd Semester' ?> (Active)</div>
                    </div>
                </div>
                <hr class="my-2 border-light">
                <div class="d-flex justify-content-between py-1 small">
                    <span class="text-muted">Default grading method</span>
                    <span class="fw-semibold text-dark"><?= ($gradingSetting['grading_method'] ?? 'zero_based') === 'fifty_based' ? '50-Based' : 'Zero-Based' ?></span>
                </div>
                <div class="d-flex justify-content-between py-1 small">
                    <span class="text-muted">System status</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">Operational</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard.php';
?>
