<?php
$pageTitle = 'User Management';
$subtitle = 'Manage institutional accounts, role assignments, and system access.';
$headerActions = '<a href="' . url('/admin/users/create') . '" class="btn btn-primary d-inline-flex align-items-center gap-2"><i class="bi bi-person-plus"></i> Add user</a>';
ob_start();
?>

<!-- Search, Filter & Statistics Bar -->
<div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between mb-4">
    <form method="GET" action="<?= url('/admin/users') ?>" class="d-flex flex-wrap align-items-center gap-2 flex-grow-1" style="max-width: 760px;" id="usersFilterForm">
        <div class="position-relative flex-grow-1" style="min-width: 240px; max-width: 380px;">
            <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 14px;"></i>
            <input type="text" 
                   id="userSearch" 
                   name="search"
                   class="form-control ps-5" 
                   placeholder="Search by name, email, or student ID..." 
                   value="<?= htmlspecialchars($currentSearch ?? '') ?>"
                   autocomplete="off">
        </div>

        <select id="filter_role" name="role" class="form-select w-auto" onchange="window.fetchUsers()">
            <option value="">All Roles</option>
            <option value="Admin" <?= ($currentRole ?? '') === 'Admin' ? 'selected' : '' ?>>Admin</option>
            <option value="Dean" <?= ($currentRole ?? '') === 'Dean' ? 'selected' : '' ?>>Dean</option>
            <option value="Faculty" <?= ($currentRole ?? '') === 'Faculty' ? 'selected' : '' ?>>Faculty</option>
            <option value="Student" <?= ($currentRole ?? '') === 'Student' ? 'selected' : '' ?>>Student</option>
        </select>

        <select id="filter_status" name="status" class="form-select w-auto" onchange="window.fetchUsers()">
            <option value="">All Statuses</option>
            <option value="active" <?= ($currentStatus ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= ($currentStatus ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
    </form>

    <div class="text-muted small fw-medium text-nowrap" id="userCounter">
        <?php $totalCount = (int) ($users['total'] ?? count($users['data'] ?? [])); ?>
        <?= number_format($totalCount) ?> <?= $totalCount === 1 ? 'user account' : 'user accounts' ?>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="usersTable">
            <thead class="bg-white border-bottom">
                <tr>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Full name</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Email address</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Role &amp; affiliation</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 120px; font-size: 11px;">Status</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="width: 190px; font-size: 11px;">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (empty($users['data'])): ?>
                    <tr id="emptyUserRow">
                        <td colspan="5" class="p-0">
                            <?php
                            $icon = 'bi-people';
                            $title = 'No user accounts found';
                            $message = 'No user accounts found matching the current search or filter criteria.';
                            include __DIR__ . '/../../components/empty-state.php';
                            ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <tr id="emptySearchRow" style="display: none;">
                        <td colspan="5" class="p-0">
                            <?php
                            $icon = 'bi-search';
                            $title = 'No matching users found';
                            $message = 'No user accounts found matching the current search query.';
                            include __DIR__ . '/../../components/empty-state.php';
                            ?>
                        </td>
                    </tr>
                    <?php foreach ($users['data'] as $user): ?>
                    <tr class="user-row" data-search="<?= strtolower(htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ' ' . $user['email'] . ' ' . ($user['student_number'] ?? '') . ' ' . ($user['department_name'] ?? '') . ' ' . ($user['set_name'] ?? ''))) ?>">
                        <td class="px-3 fw-semibold text-dark"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                        <td class="px-3 text-muted small"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="px-3">
                            <span class="badge badge-<?= strtolower($user['role']) ?>"><?= htmlspecialchars($user['role']) ?></span>
                            <?php if (in_array($user['role'], ['Faculty', 'Dean'], true) && !empty($user['department_name'])): ?>
                                <div class="small text-muted mt-1">
                                    <i class="bi bi-building me-1 text-primary"></i><?= htmlspecialchars($user['department_name']) ?> (<?= htmlspecialchars($user['department_code']) ?>)
                                </div>
                            <?php elseif ($user['role'] === 'Student'): ?>
                                <div class="small text-muted mt-1 font-monospace d-flex align-items-center gap-2">
                                    <span><?= !empty($user['student_number']) ? htmlspecialchars($user['student_number']) : 'No ID' ?></span>
                                    <?php $displaySet = $user['set_name'] ?? ''; ?>
                                    <?php if (!empty($displaySet)): ?>
                                        <span class="badge bg-light text-dark border"><i class="bi bi-collection me-1"></i><?= htmlspecialchars($displaySet) ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 text-center">
                            <?php if (($user['status'] ?? 'active') === 'inactive'): ?>
                                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                    <i class="bi bi-dash-circle me-1"></i>Inactive
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="bi bi-check-circle me-1"></i>Active
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 text-end text-nowrap">
                            <a href="<?= url('/admin/users/' . $user['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary py-1 px-2 d-inline-flex align-items-center gap-1">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <?php if ($user['role'] === 'Admin'): ?>
                                <span class="badge bg-light text-secondary border py-1 px-2 d-inline-flex align-items-center gap-1" title="Administrator accounts cannot be deactivated to prevent system lockout">
                                    <i class="bi bi-shield-check text-primary"></i> Protected
                                </span>
                            <?php elseif (($user['status'] ?? 'active') === 'inactive'): ?>
                                <form method="POST" action="<?= url('/admin/users/' . $user['id'] . '/activate') ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to activate this user account?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2 d-inline-flex align-items-center gap-1" title="Activate account">
                                        <i class="bi bi-person-check"></i> Activate
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="<?= url('/admin/users/' . $user['id'] . '/deactivate') ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to deactivate this user account? The user will immediately be unable to sign in.');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-warning py-1 px-2 d-inline-flex align-items-center gap-1" title="Deactivate account">
                                        <i class="bi bi-person-x"></i> Deactivate
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="usersPagination" class="mt-2">
    <?php if (!empty($users['data'])): ?>
        <?php 
        $pagination = $users;
        include __DIR__ . '/../../components/pagination.php'; 
        ?>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('userSearch');
    const roleSelect = document.getElementById('filter_role');
    const statusSelect = document.getElementById('filter_status');
    const filterForm = document.getElementById('usersFilterForm');

    let debounceTimer = null;
    let abortCtrl = null;

    function applyClientFilter() {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        const rows = document.querySelectorAll('.user-row');
        const emptySearchRow = document.getElementById('emptySearchRow');
        let visibleCount = 0;

        rows.forEach(function(row) {
            const searchData = row.getAttribute('data-search') || '';
            if (!query || searchData.includes(query)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (emptySearchRow && rows.length > 0) {
            emptySearchRow.style.display = (visibleCount === 0 && query) ? '' : 'none';
        }
    }

    window.fetchUsers = function(pageUrl) {
        if (abortCtrl) {
            abortCtrl.abort();
        }
        abortCtrl = new AbortController();

        let targetUrl;
        if (pageUrl) {
            targetUrl = pageUrl;
        } else {
            const formData = new FormData(filterForm);
            const params = new URLSearchParams();
            for (const [k, v] of formData.entries()) {
                const val = typeof v === 'string' ? v.trim() : v;
                if (val) params.set(k, val);
            }
            targetUrl = filterForm.action + (params.toString() ? '?' + params.toString() : '');
        }

        fetch(targetUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: abortCtrl.signal
        })
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newTable = doc.getElementById('usersTable');
            const currentTable = document.getElementById('usersTable');
            if (newTable && currentTable) {
                currentTable.innerHTML = newTable.innerHTML;
            }

            const newCounter = doc.getElementById('userCounter');
            const currentCounter = document.getElementById('userCounter');
            if (newCounter && currentCounter) {
                currentCounter.innerHTML = newCounter.innerHTML;
            }

            const newPagination = doc.getElementById('usersPagination');
            const currentPagination = document.getElementById('usersPagination');
            if (currentPagination) {
                currentPagination.innerHTML = newPagination ? newPagination.innerHTML : '';
            }

            window.history.replaceState(null, '', targetUrl);
        })
        .catch(err => {
            if (err.name !== 'AbortError') {
                console.error('Fetch error:', err);
            }
        });
    };

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            applyClientFilter();
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() {
                window.fetchUsers();
            }, 300);
        });
    }

    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearTimeout(debounceTimer);
            window.fetchUsers();
        });
    }

    // Intercept pagination clicks for seamless page changes
    document.addEventListener('click', function(e) {
        const link = e.target.closest('#usersPagination a.page-link');
        if (link && link.getAttribute('href') && !link.getAttribute('href').startsWith('#')) {
            e.preventDefault();
            window.fetchUsers(link.getAttribute('href'));
        }
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
