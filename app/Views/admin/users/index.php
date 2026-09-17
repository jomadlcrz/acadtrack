<?php
$pageTitle = 'User Management';
$subtitle = 'Manage institutional accounts, role assignments, and system access.';
$headerActions = '<button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#selectRoleModal"><i class="bi bi-person-plus"></i> Add user</button>';
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

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
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
                                    <?= htmlspecialchars($user['department_name']) ?> (<?= htmlspecialchars($user['department_code']) ?>)
                                </div>
                            <?php elseif ($user['role'] === 'Student'): ?>
                                <div class="small text-muted mt-1 font-monospace d-flex align-items-center gap-2">
                                    <span><?= !empty($user['student_number']) ? htmlspecialchars($user['student_number']) : 'No ID' ?></span>
                                    <?php $displaySet = $user['set_name'] ?? ''; ?>
                                    <?php if (!empty($displaySet)): ?>
                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($displaySet) ?></span>
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
                                            <a class="dropdown-item" href="<?= url('/admin/users/' . $user['id'] . '/edit') ?>">
                                                <i class="bi bi-pencil text-muted"></i> Edit user
                                            </a>
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

    // Role selection modal handlers
    const roleItems = document.querySelectorAll('.role-select-item');
    const updateRoleHighlight = function() {
        roleItems.forEach(el => {
            const radio = el.querySelector('input[type="radio"]');
            if (radio && radio.checked) {
                el.style.borderColor = 'var(--primary, #1e3a8a)';
                el.style.backgroundColor = '#f8fafc';
            } else {
                el.style.borderColor = '#cbd5e1';
                el.style.backgroundColor = '#ffffff';
            }
        });
    };

    roleItems.forEach(item => {
        item.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
                updateRoleHighlight();
            }
        });
        item.addEventListener('dblclick', function() {
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                window.location.href = '<?= url("/admin/users/create") ?>?role=' + encodeURIComponent(radio.value);
            }
        });
    });

    document.getElementById('btnProceedRole')?.addEventListener('click', function() {
        const checked = document.querySelector('input[name="account_role"]:checked');
        const role = checked ? checked.value : 'Student';
        window.location.href = '<?= url("/admin/users/create") ?>?role=' + encodeURIComponent(role);
    });

    updateRoleHighlight();
});
</script>

<!-- Modal: Select Account Role -->
<div class="modal fade" id="selectRoleModal" tabindex="-1" aria-labelledby="selectRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content border-0 shadow" style="border: 1px solid #e2e8f0 !important; border-radius: 8px;">
            <div class="modal-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="modal-title h6 fw-semibold text-dark mb-0" id="selectRoleModalLabel">Select User Role</h5>
                    <small class="text-muted">Choose the account type to proceed to the creation form.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex flex-column gap-2" id="roleOptionsList">
                    <!-- Student -->
                    <div class="role-select-item p-3 rounded-2 border d-flex align-items-start gap-3" style="cursor: pointer; border-color: #cbd5e1; transition: border-color 0.15s ease, background-color 0.15s ease;">
                        <input type="radio" class="form-check-input mt-1 flex-shrink-0" name="account_role" value="Student" checked>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-semibold text-dark">Student</span>
                                <span class="badge badge-student">Student</span>
                            </div>
                            <div class="text-muted small" style="font-size: 12.5px; line-height: 1.45;">Academic enrollment, class cohort placement, and grade tracking.</div>
                        </div>
                    </div>

                    <!-- Faculty -->
                    <div class="role-select-item p-3 rounded-2 border d-flex align-items-start gap-3" style="cursor: pointer; border-color: #cbd5e1; transition: border-color 0.15s ease, background-color 0.15s ease;">
                        <input type="radio" class="form-check-input mt-1 flex-shrink-0" name="account_role" value="Faculty">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-semibold text-dark">Faculty / Instructor</span>
                                <span class="badge badge-faculty">Faculty</span>
                            </div>
                            <div class="text-muted small" style="font-size: 12.5px; line-height: 1.45;">Teaching assignment, subject grade encoding, and attendance rosters.</div>
                        </div>
                    </div>

                    <!-- Dean -->
                    <div class="role-select-item p-3 rounded-2 border d-flex align-items-start gap-3" style="cursor: pointer; border-color: #cbd5e1; transition: border-color 0.15s ease, background-color 0.15s ease;">
                        <input type="radio" class="form-check-input mt-1 flex-shrink-0" name="account_role" value="Dean">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-semibold text-dark">Academic Dean</span>
                                <span class="badge badge-dean">Dean</span>
                            </div>
                            <div class="text-muted small" style="font-size: 12.5px; line-height: 1.45;">Collegiate division leadership, curriculum oversight, and grade approval.</div>
                        </div>
                    </div>

                    <!-- Admin -->
                    <div class="role-select-item p-3 rounded-2 border d-flex align-items-start gap-3" style="cursor: pointer; border-color: #cbd5e1; transition: border-color 0.15s ease, background-color 0.15s ease;">
                        <input type="radio" class="form-check-input mt-1 flex-shrink-0" name="account_role" value="Admin">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-semibold text-dark">Administrator</span>
                                <span class="badge badge-admin">Admin</span>
                            </div>
                            <div class="text-muted small" style="font-size: 12.5px; line-height: 1.45;">Full institutional governance over users, terms, and system logs.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4 border-top d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="btnProceedRole" class="btn btn-primary d-inline-flex align-items-center gap-1.5">
                    Proceed to form <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
