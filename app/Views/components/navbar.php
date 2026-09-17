<?php
$currentUser = $_SESSION['user'] ?? null;
$userRole = $currentUser['role'] ?? 'Student';
$firstName = $currentUser['first_name'] ?? '';
$lastName = $currentUser['last_name'] ?? '';
$fullName = trim($firstName . ' ' . $lastName) ?: 'User';
$firstLetter = strtoupper(substr(trim($firstName) !== '' ? trim($firstName) : (trim($lastName) !== '' ? trim($lastName) : 'U'), 0, 1));
?>
<header class="app-navbar">
    <div class="navbar-left">
        <a href="<?= url('/dashboard') ?>" class="navbar-brand-link" title="Acadtrack Dashboard">
            <img src="<?= asset('images/gwc.png') ?>" alt="GWC Logo" class="navbar-brand-logo">
            <div class="navbar-brand-text">
                <span class="navbar-brand-title">Golden West Colleges, Inc.</span>
                <span class="navbar-brand-system">Acadtrack</span>
            </div>
        </a>
    </div>

    <div class="navbar-right">
        <?php if ($currentUser): ?>
            <div class="navbar-user-card">
                <div class="navbar-avatar" title="<?= htmlspecialchars($fullName) ?>">
                    <?= htmlspecialchars($firstLetter) ?>
                </div>
                <div class="navbar-user-meta d-none d-sm-flex">
                    <span class="navbar-user-name"><?= htmlspecialchars($fullName) ?></span>
                    <span class="badge badge-role badge-<?= strtolower($userRole) ?>"><?= htmlspecialchars($userRole) ?></span>
                </div>
            </div>

            <form method="POST" action="<?= url('/logout') ?>" class="navbar-logout-form m-0">
                <?= csrf_field() ?>
                <button type="submit" class="btn-navbar-logout" title="Sign out of your session">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Sign out</span>
                </button>
            </form>
        <?php endif; ?>
    </div>
</header>
