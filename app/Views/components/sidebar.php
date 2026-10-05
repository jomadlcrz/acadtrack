<aside class="sidebar" id="appSidebar">
    <div class="sidebar-mobile-header d-lg-none">
        <div class="sidebar-drawer-brand">
            <img src="<?= asset('images/cite.png') ?>" alt="CITE Logo" class="sidebar-drawer-logo">
            <div class="sidebar-drawer-text">
                <span class="sidebar-mobile-title">Acadtrack</span>
                <span class="sidebar-drawer-subtitle">Golden West Colleges, Inc.</span>
            </div>
        </div>
        <button type="button" class="btn-sidebar-close" id="sidebarCloseBtn" aria-label="Close navigation menu">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <nav class="sidebar-nav">
        <?php
        $userRole = $_SESSION['user']['role'] ?? '';
        $currentPath = function_exists('current_route_path') ? current_route_path() : parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        ?>

        <?php if ($userRole === 'Admin'): ?>
            <div class="sidebar-group">
                <span class="sidebar-heading">Overview</span>
                <a href="<?= url('/admin/dashboard') ?>" class="sidebar-link <?= $currentPath === '/admin/dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <div class="sidebar-group">
                <span class="sidebar-heading">Academics</span>
                <a href="<?= url('/admin/academic-terms') ?>" class="sidebar-link <?= (str_starts_with($currentPath, '/admin/academic-terms') && !str_starts_with($currentPath, '/admin/academic-terms/closure')) ? 'active' : '' ?>">
                    <i class="bi bi-calendar3"></i>
                    <span>Academic Terms</span>
                </a>
                <a href="<?= url('/admin/academic-terms/closure') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/admin/academic-terms/closure') ? 'active' : '' ?>">
                    <i class="bi bi-lock-fill"></i>
                    <span>Term Closure</span>
                </a>
                <a href="<?= url('/admin/departments') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/admin/departments') ? 'active' : '' ?>">
                    <i class="bi bi-building"></i>
                    <span>Departments</span>
                </a>
                <a href="<?= url('/admin/program-curricula') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/admin/program-curricula') ? 'active' : '' ?>">
                    <i class="bi bi-journal-bookmark-fill"></i>
                    <span>Program Curricula</span>
                </a>
                <a href="<?= url('/admin/sets') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/admin/sets') ? 'active' : '' ?>">
                    <i class="bi bi-collection-fill"></i>
                    <span>Sets</span>
                </a>
                <a href="<?= url('/dean/grade-review') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/dean/grade-review') ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-check-fill"></i>
                    <span>Grade Review &amp; Approval</span>
                </a>
            </div>

            <div class="sidebar-group">
                <span class="sidebar-heading">User Management</span>
                <a href="<?= url('/admin/students') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/admin/students') ? 'active' : '' ?>">
                    <i class="bi bi-mortarboard-fill"></i>
                    <span>Students</span>
                </a>
                <a href="<?= url('/admin/faculty') ?>" class="sidebar-link <?= (str_starts_with($currentPath, '/admin/faculty') || str_starts_with($currentPath, '/admin/staff')) ? 'active' : '' ?>">
                    <i class="bi bi-person-badge-fill"></i>
                    <span>Faculty</span>
                </a>
                <a href="<?= url('/admin/administrators') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/admin/administrators') ? 'active' : '' ?>">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span>Administrators</span>
                </a>
            </div>

            <div class="sidebar-group">
                <span class="sidebar-heading">Administration</span>
                <a href="<?= url('/admin/settings') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/admin/settings') ? 'active' : '' ?>">
                    <i class="bi bi-gear-fill"></i>
                    <span>Institutional Settings</span>
                </a>
                <a href="<?= url('/admin/activity-log') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/admin/activity-log') ? 'active' : '' ?>">
                    <i class="bi bi-clock-history"></i>
                    <span>Activity Log</span>
                </a>
                <a href="<?= url('/admin/archives') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/admin/archives') ? 'active' : '' ?>">
                    <i class="bi bi-archive-fill"></i>
                    <span>Archives</span>
                </a>
            </div>

        <?php elseif ($userRole === 'Dean'): ?>
            <div class="sidebar-group">
                <span class="sidebar-heading">Overview</span>
                <a href="<?= url('/dean/dashboard') ?>" class="sidebar-link <?= $currentPath === '/dean/dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <div class="sidebar-group">
                <span class="sidebar-heading">Academics</span>
                <a href="<?= url('/dean/subjects') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/dean/subjects') ? 'active' : '' ?>">
                    <i class="bi bi-journal-text"></i>
                    <span>Curriculum Subjects</span>
                </a>
                <a href="<?= url('/admin/sets') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/admin/sets') || str_starts_with($currentPath, '/dean/sets') ? 'active' : '' ?>">
                    <i class="bi bi-collection-fill"></i>
                    <span>Sets</span>
                </a>
                <a href="<?= url('/dean/faculty-assignments') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/dean/faculty-assignments') ? 'active' : '' ?>">
                    <i class="bi bi-person-badge-fill"></i>
                    <span>Faculty Assignments</span>
                </a>
            </div>

            <div class="sidebar-group">
                <span class="sidebar-heading">Academic Oversight</span>
                <a href="<?= url('/dean/grade-review') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/dean/grade-review') ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-check-fill"></i>
                    <span>Grade Review &amp; Approval</span>
                </a>
            </div>

        <?php elseif ($userRole === 'Faculty'): ?>
            <div class="sidebar-group">
                <span class="sidebar-heading">Overview</span>
                <a href="<?= url('/faculty/dashboard') ?>" class="sidebar-link <?= $currentPath === '/faculty/dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <div class="sidebar-group">
                <span class="sidebar-heading">Instruction</span>
                <a href="<?= url('/faculty/subjects') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/faculty/subjects') ? 'active' : '' ?>">
                    <i class="bi bi-journal-text"></i>
                    <span>My Assigned Subjects</span>
                </a>
                <a href="<?= url('/faculty/students') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/faculty/students') ? 'active' : '' ?>">
                    <i class="bi bi-people-fill"></i>
                    <span>Student Rosters</span>
                </a>
            </div>

            <div class="sidebar-group">
                <span class="sidebar-heading">Grading &amp; Attendance</span>
                <a href="<?= url('/faculty/grading') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/faculty/grading') ? 'active' : '' ?>">
                    <i class="bi bi-table"></i>
                    <span>Grade Encoding</span>
                </a>
                <a href="<?= url('/faculty/attendance') ?>" class="sidebar-link <?= str_starts_with($currentPath, '/faculty/attendance') ? 'active' : '' ?>">
                    <i class="bi bi-clipboard-check"></i>
                    <span>Attendance</span>
                </a>
            </div>

        <?php elseif ($userRole === 'Student'): ?>
            <div class="sidebar-group">
                <span class="sidebar-heading">Overview</span>
                <a href="<?= url('/student/dashboard') ?>" class="sidebar-link <?= $currentPath === '/student/dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <div class="sidebar-group">
                <span class="sidebar-heading">Academic Records</span>
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

    <?php
    $sidebarUser = $_SESSION['user'] ?? null;
    if ($sidebarUser):
        $userRole = $sidebarUser['role'] ?? 'Student';
        $firstName = $sidebarUser['first_name'] ?? '';
        $lastName = $sidebarUser['last_name'] ?? '';
        $fullName = trim($firstName . ' ' . $lastName) ?: 'User';
        $userEmail = $sidebarUser['email'] ?? '';
    ?>
    <div class="sidebar-footer d-lg-none">
        <div class="dropup w-100">
            <button type="button" class="sidebar-user-trigger w-100" id="sidebarUserDropdownBtn" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false" title="User account options">
                <div class="sidebar-user-info">
                    <span class="sidebar-user-name"><?= htmlspecialchars($fullName) ?></span>
                    <span class="badge badge-role badge-<?= strtolower($userRole) ?>"><?= htmlspecialchars($userRole) ?></span>
                </div>
                <i class="bi bi-chevron-expand sidebar-user-chevron" aria-hidden="true"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end sidebar-user-dropdown-menu shadow" aria-labelledby="sidebarUserDropdownBtn">
                <li class="dropdown-header">
                    <div class="fw-semibold text-dark text-truncate" style="max-width: 220px;"><?= htmlspecialchars($fullName) ?></div>
                    <?php if ($userEmail): ?>
                        <div class="small text-muted text-truncate" style="max-width: 220px;"><?= htmlspecialchars($userEmail) ?></div>
                    <?php endif; ?>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center gap-2 text-danger py-2 sidebar-logout-btn" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Sign out</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>
    <?php endif; ?>
</aside>
<script>
(function() {
    try {
        var sidebar = document.querySelector('.sidebar');
        if (!sidebar) return;

        var savedScroll = sessionStorage.getItem('acadtrack_sidebar_scroll');
        if (savedScroll !== null) {
            sidebar.scrollTop = parseInt(savedScroll, 10);
        } else {
            var activeLink = sidebar.querySelector('.sidebar-link.active');
            if (activeLink) {
                activeLink.scrollIntoView({ block: 'nearest' });
            }
        }

        sidebar.querySelectorAll('.sidebar-link').forEach(function(link) {
            link.addEventListener('click', function() {
                sessionStorage.setItem('acadtrack_sidebar_scroll', sidebar.scrollTop);
            });
        });

        window.addEventListener('beforeunload', function() {
            sessionStorage.setItem('acadtrack_sidebar_scroll', sidebar.scrollTop);
        });
    } catch (e) {}
})();
</script>

