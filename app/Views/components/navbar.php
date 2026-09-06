<nav class="navbar">
    <div class="navbar-brand">
        <a href="/dashboard" class="d-flex align-items-center gap-2">
            <img src="/assets/images/gwc.png" height="28" alt="GWC Logo">
            <span>GWC Grading System</span>
        </a>
    </div>
    <div class="navbar-menu">
        <?php if (isset($_SESSION['user'])): ?>
            <span class="navbar-user">
                <?= htmlspecialchars($_SESSION['user']['first_name'] ?? '') ?>
                <?= htmlspecialchars($_SESSION['user']['last_name'] ?? '') ?>
            </span>
            <form method="POST" action="/logout" class="navbar-logout">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-link">Logout</button>
            </form>
        <?php endif; ?>
    </div>
</nav>
