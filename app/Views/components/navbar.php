<?php
$currentUser = $_SESSION['user'] ?? null;
$userRole = $currentUser['role'] ?? 'Student';
$firstName = $currentUser['first_name'] ?? '';
$lastName = $currentUser['last_name'] ?? '';
$fullName = trim($firstName . ' ' . $lastName) ?: 'User';
?>
<header class="app-navbar">
    <div class="navbar-left">
        <a href="<?= url('/dashboard') ?>" class="navbar-brand-link" title="Acadtrack Dashboard">
            <img src="<?= asset('images/cite.png') ?>" alt="CITE Logo" class="navbar-brand-logo">
            <div class="navbar-brand-text">
                <span class="navbar-brand-title">College of Information Technology</span>
                <span class="navbar-brand-system">Acadtrack &bull; GWC</span>
            </div>
        </a>
    </div>

    <div class="navbar-right">
        <?php if ($currentUser): ?>
            <div class="navbar-user-card">
                <div class="navbar-user-meta d-flex flex-column text-end">
                    <span class="navbar-user-name"><?= htmlspecialchars($fullName) ?></span>
                    <span class="badge badge-role badge-<?= strtolower($userRole) ?> align-self-end"><?= htmlspecialchars($userRole) ?></span>
                </div>
            </div>

            <button type="button" class="btn-navbar-logout" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal" title="Sign out of your session">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign out</span>
            </button>
        <?php endif; ?>
    </div>
</header>

<?php if ($currentUser): ?>
<!-- Sign Out Confirmation Modal -->
<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-start">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="d-flex align-items-center justify-content-center rounded-circle text-danger" style="width: 42px; height: 42px; background-color: #fef2f2; flex-shrink: 0;">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark fs-6 mb-0" id="logoutConfirmModalLabel">Sign Out Confirmation</h5>
                        <div class="text-muted" style="font-size: 12px;">Active portal session</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-3 text-secondary" style="font-size: 13.5px; line-height: 1.55;">
                Are you sure you want to sign out of <strong>Acadtrack</strong>? Any unsaved changes on the current page will be lost.
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary px-3 py-1.5 fw-medium" data-bs-dismiss="modal">
                    Cancel
                </button>
                <form method="POST" action="<?= url('/logout') ?>" class="m-0" id="logoutConfirmedForm">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger px-3 py-1.5 d-inline-flex align-items-center gap-1.5 fw-semibold" style="background-color: #dc2626; border-color: #dc2626;">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Yes, Sign Out</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
