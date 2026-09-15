<?php
use App\Models\AcademicTerm;

$currentUser = $_SESSION['user'] ?? null;
$userRole = $currentUser['role'] ?? 'Student';
$firstName = $currentUser['first_name'] ?? '';
$lastName = $currentUser['last_name'] ?? '';
$fullName = trim($firstName . ' ' . $lastName) ?: 'User';
$firstLetter = strtoupper(substr(trim($firstName) !== '' ? trim($firstName) : (trim($lastName) !== '' ? trim($lastName) : 'U'), 0, 1));

$activeTerm = null;
try {
    $activeTerm = AcademicTerm::getActive();
} catch (\Throwable $e) {
    // Gracefully handle if DB is unavailable during render
}
$termLabel = '1st Semester • A.Y. 2026-2027';
if ($activeTerm) {
    $sem = (int)($activeTerm['semester'] ?? 1);
    $semLabel = match ($sem) {
        1 => '1st Semester',
        2 => '2nd Semester',
        3 => 'Summer Term',
        default => "Semester {$sem}",
    };
    $yearLabel = $activeTerm['school_year'] ?? $activeTerm['academic_year_name'] ?? '2026-2027';
    $termLabel = $semLabel . ' • A.Y. ' . htmlspecialchars((string)$yearLabel);
}
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

    <div class="navbar-center d-none d-md-flex">
        <div class="navbar-term-badge" title="Current Academic Term">
            <i class="bi bi-calendar3 me-1"></i>
            <span><?= $termLabel ?></span>
        </div>
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
