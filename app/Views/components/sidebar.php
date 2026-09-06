<aside class="sidebar">
    <nav class="sidebar-nav">
        <?php
        $userRole = $_SESSION['user']['role'] ?? '';
        $currentPath = function_exists('current_route_path') ? current_route_path() : parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        ?>

        <?php if ($userRole === 'Admin'): ?>
            <a href="/admin/dashboard" class="sidebar-link <?= $currentPath === '/admin/dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="/admin/users" class="sidebar-link <?= str_starts_with($currentPath, '/admin/users') ? 'active' : '' ?>">Users</a>
            <a href="/admin/settings" class="sidebar-link <?= $currentPath === '/admin/settings' ? 'active' : '' ?>">Settings</a>

        <?php elseif ($userRole === 'Dean'): ?>
            <a href="/dean/dashboard" class="sidebar-link <?= $currentPath === '/dean/dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="/dean/subjects" class="sidebar-link <?= str_starts_with($currentPath, '/dean/subjects') ? 'active' : '' ?>">Subjects</a>
            <a href="/dean/faculty-assignments" class="sidebar-link <?= str_starts_with($currentPath, '/dean/faculty-assignments') ? 'active' : '' ?>">Faculty Assignments</a>
            <a href="/dean/grade-review" class="sidebar-link <?= str_starts_with($currentPath, '/dean/grade-review') ? 'active' : '' ?>">Grade Review</a>

        <?php elseif ($userRole === 'Faculty'): ?>
            <a href="/faculty/dashboard" class="sidebar-link <?= $currentPath === '/faculty/dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="/faculty/subjects" class="sidebar-link <?= str_starts_with($currentPath, '/faculty/subjects') ? 'active' : '' ?>">My Subjects</a>
            <a href="/faculty/grading" class="sidebar-link <?= str_starts_with($currentPath, '/faculty/grading') ? 'active' : '' ?>">Grading</a>

        <?php elseif ($userRole === 'Student'): ?>
            <a href="/student/dashboard" class="sidebar-link <?= $currentPath === '/student/dashboard' ? 'active' : '' ?>">Dashboard</a>
            <a href="/student/grades" class="sidebar-link <?= str_starts_with($currentPath, '/student/grades') ? 'active' : '' ?>">My Grades</a>
            <a href="/student/evaluation" class="sidebar-link <?= str_starts_with($currentPath, '/student/evaluation') ? 'active' : '' ?>">Evaluation</a>
        <?php endif; ?>
    </nav>
</aside>
