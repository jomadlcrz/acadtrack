<aside class="sidebar">
    <nav class="sidebar-nav">
        <?php
        $userRole = $_SESSION['user']['role'] ?? '';
        $currentPath = function_exists('current_route_path') ? current_route_path() : parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        ?>

        <?php if ($userRole === 'Admin'): ?>
            <div class="sidebar-group">
                <span class="sidebar-heading">Administration</span>
                <a href="<?= url('/admin/dashboard') ?>" class="sidebar-link <?= $currentPath === '/admin/dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?= url('/admin/users') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/admin/users') ? 'active' : '' ?>">
                    <i class="bi bi-people-fill"></i>
                    <span>User Management</span>
                </a>
                <a href="<?= url('/admin/departments') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/admin/departments') ? 'active' : '' ?>">
                    <i class="bi bi-building"></i>
                    <span>Departments</span>
                </a>
                <a href="<?= url('/dean/sections') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/dean/sections') ? 'active' : '' ?>">
                    <i class="bi bi-collection-fill"></i>
                    <span>Sections</span>
                </a>
                <a href="<?= url('/admin/settings') ?>" class="sidebar-link <?= $currentPath === '/admin/settings' ? 'active' : '' ?>">
                    <i class="bi bi-gear-fill"></i>
                    <span>Institutional Settings</span>
                </a>
                <a href="<?= url('/dean/grade-review') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/dean/grade-review') ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-check-fill"></i>
                    <span>Grade Review &amp; Approval</span>
                </a>
            </div>

        <?php elseif ($userRole === 'Dean'): ?>
            <div class="sidebar-group">
                <span class="sidebar-heading">Academic Oversight</span>
                <a href="<?= url('/dean/dashboard') ?>" class="sidebar-link <?= $currentPath === '/dean/dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?= url('/dean/subjects') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/dean/subjects') ? 'active' : '' ?>">
                    <i class="bi bi-journal-bookmark-fill"></i>
                    <span>Curriculum Subjects</span>
                </a>
                <a href="<?= url('/dean/sections') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/dean/sections') ? 'active' : '' ?>">
                    <i class="bi bi-collection-fill"></i>
                    <span>Sections</span>
                </a>
                <a href="<?= url('/dean/faculty-assignments') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/dean/faculty-assignments') ? 'active' : '' ?>">
                    <i class="bi bi-person-badge-fill"></i>
                    <span>Faculty Assignments</span>
                </a>
                <a href="<?= url('/dean/grade-review') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/dean/grade-review') ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-check-fill"></i>
                    <span>Grade Review &amp; Approval</span>
                </a>
            </div>

        <?php elseif ($userRole === 'Faculty'): ?>
            <div class="sidebar-group">
                <span class="sidebar-heading">Teaching &amp; Grading</span>
                <a href="<?= url('/faculty/dashboard') ?>" class="sidebar-link <?= $currentPath === '/faculty/dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?= url('/faculty/subjects') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/faculty/subjects') ? 'active' : '' ?>">
                    <i class="bi bi-journal-text"></i>
                    <span>My Assigned Subjects</span>
                </a>
                <a href="<?= url('/faculty/students') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/faculty/students') ? 'active' : '' ?>">
                    <i class="bi bi-people-fill"></i>
                    <span>Student Rosters</span>
                </a>
                <a href="<?= url('/faculty/grading') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/faculty/grading') ? 'active' : '' ?>">
                    <i class="bi bi-table"></i>
                    <span>Grade Encoding</span>
                </a>
            </div>

        <?php elseif ($userRole === 'Student'): ?>
            <div class="sidebar-group">
                <span class="sidebar-heading">Academic Records</span>
                <a href="<?= url('/student/dashboard') ?>" class="sidebar-link <?= $currentPath === '/student/dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?= url('/student/grades') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/student/grades') ? 'active' : '' ?>">
                    <i class="bi bi-award-fill"></i>
                    <span>My Academic Grades</span>
                </a>
                <a href="<?= url('/student/evaluation') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/student/evaluation') ? 'active' : '' ?>">
                    <i class="bi bi-mortarboard-fill"></i>
                    <span>Whole Evaluation</span>
                </a>
            </div>
        <?php endif; ?>
    </nav>
</aside>
