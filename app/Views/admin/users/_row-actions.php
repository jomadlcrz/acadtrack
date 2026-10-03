<?php
/** Expects $user (array with id, status, role). */
?>
<?php if ($user['role'] === 'Admin'): ?>
    <span class="badge bg-light text-secondary border py-1.5 px-2.5 d-inline-flex align-items-center gap-1.5" title="Administrator accounts are protected and cannot be edited or deactivated">
        <i class="bi bi-shield-check text-primary"></i> Protected
    </span>
<?php else: ?>
    <div class="dropdown d-inline-block">
        <button class="btn btn-sm btn-action-trigger" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false" title="Actions">
            <i class="bi bi-three-dots-vertical"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end action-dropdown-menu shadow-sm">
            <li>
                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editUserModal<?= (int) $user['id'] ?>">
                    <i class="bi bi-pencil text-muted"></i> Edit account
                </button>
            </li>
            <li><hr class="dropdown-divider"></li>
            <?php if (($user['status'] ?? 'active') === 'inactive'): ?>
                <li>
                    <form method="POST" action="<?= url('/admin/users/' . $user['id'] . '/activate') ?>" class="m-0" onsubmit="return confirm('Are you sure you want to activate this user account?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="dropdown-item text-success">
                            <i class="bi bi-person-check"></i> Activate account
                        </button>
                    </form>
                </li>
            <?php else: ?>
                <li>
                    <form method="POST" action="<?= url('/admin/users/' . $user['id'] . '/deactivate') ?>" class="m-0" onsubmit="return confirm('Are you sure you want to deactivate this user account? The user will immediately be unable to sign in.');">
                        <?= csrf_field() ?>
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-person-x"></i> Deactivate account
                        </button>
                    </form>
                </li>
            <?php endif; ?>
        </ul>
    </div>
<?php endif; ?>
