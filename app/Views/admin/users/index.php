<?php
$pageTitle = 'User Management';
$subtitle = 'Manage institutional accounts, role assignments, and system access.';
$headerActions = '<div class="d-flex align-items-center gap-2">
    <a href="' . url('/admin/users/import-template') . '" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5"><i class="bi bi-download"></i> Download Template</a>
    <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#importExcelModal"><i class="bi bi-file-earmark-excel"></i> Import Spreadsheet</button>
    <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#selectRoleModal" style="background-color: #1e3a8a; border-color: #1e3a8a;"><i class="bi bi-plus-lg"></i> Add User</button>
</div>';
ob_start();
?>

<!-- Navigation Tabs (Adopted from class-scheduling) -->
<div class="mb-4">
    <ul class="nav nav-pills p-1 bg-light rounded-3 d-inline-flex border" id="usersTabs" role="tablist" style="border-color: #e2e8f0 !important;">
        <li class="nav-item" role="presentation">
            <a class="nav-link py-2 px-3.5 fw-medium d-inline-flex align-items-center gap-2 rounded-2 <?= ($activeTab ?? 'all') === 'all' ? 'active shadow-sm' : 'text-secondary' ?>" 
               href="<?= url('/admin/users?tab=all') ?>">
                <i class="bi bi-people"></i> All Accounts
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link py-2 px-3.5 fw-medium d-inline-flex align-items-center gap-2 rounded-2 <?= ($activeTab ?? '') === 'regular' ? 'active shadow-sm' : 'text-secondary' ?>" 
               href="<?= url('/admin/users?tab=regular') ?>">
                <i class="bi bi-mortarboard"></i> Regular Students
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link py-2 px-3.5 fw-medium d-inline-flex align-items-center gap-2 rounded-2 <?= ($activeTab ?? '') === 'irregular' ? 'active shadow-sm' : 'text-secondary' ?>" 
               href="<?= url('/admin/users?tab=irregular') ?>">
                <i class="bi bi-shuffle"></i> Irregular Students
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link py-2 px-3.5 fw-medium d-inline-flex align-items-center gap-2 rounded-2 <?= ($activeTab ?? '') === 'faculty' ? 'active shadow-sm' : 'text-secondary' ?>" 
               href="<?= url('/admin/users?tab=faculty') ?>">
                <i class="bi bi-person-badge"></i> Faculty &amp; Deans
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link py-2 px-3.5 fw-medium d-inline-flex align-items-center gap-2 rounded-2 <?= ($activeTab ?? '') === 'admin' ? 'active shadow-sm' : 'text-secondary' ?>" 
               href="<?= url('/admin/users?tab=admin') ?>">
                <i class="bi bi-shield-lock"></i> Admins
            </a>
        </li>
    </ul>
</div>

<!-- Search, Filter & Statistics Bar -->
<div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between mb-4">
    <form method="GET" action="<?= url('/admin/users') ?>" class="d-flex flex-wrap align-items-center gap-2 flex-grow-1" style="max-width: 760px;" id="usersFilterForm">
        <input type="hidden" name="tab" value="<?= htmlspecialchars($activeTab ?? 'all') ?>">
        <?php if (!empty($currentEnrollmentStatus)): ?>
            <input type="hidden" name="enrollment_status" value="<?= htmlspecialchars($currentEnrollmentStatus) ?>">
        <?php endif; ?>
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
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.04em;">Full Name</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.04em;">Email Address</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px; letter-spacing: 0.04em;">Role &amp; Details</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 120px; font-size: 11px; letter-spacing: 0.04em;">Status</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="width: 190px; font-size: 11px; letter-spacing: 0.04em;">Actions</th>
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
                                <div class="small text-muted mt-1 font-monospace d-flex align-items-center flex-wrap gap-1.5">
                                    <span><?= !empty($user['student_number']) ? htmlspecialchars($user['student_number']) : 'No ID' ?></span>
                                    <?php if (!empty($user['student_year_level'])): ?>
                                        <span class="badge bg-light text-secondary border font-sans" style="font-family: inherit;">Yr <?= (int)$user['student_year_level'] ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($user['student_status'])): ?>
                                        <span class="badge <?= $user['student_status'] === 'Regular' ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' ?>" style="font-family: inherit;">
                                            <?= htmlspecialchars($user['student_status']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php $displaySet = $user['set_name'] ?? ''; ?>
                                    <?php if (!empty($displaySet)): ?>
                                        <span class="badge bg-light text-dark border" style="font-family: inherit;"><?= htmlspecialchars($displaySet) ?></span>
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
        let targetUrl = '<?= url("/admin/users/create") ?>?role=' + encodeURIComponent(role);
        <?php if (($activeTab ?? '') === 'regular'): ?>
        if (role === 'Student') targetUrl += '&enrollment_status=Regular';
        <?php elseif (($activeTab ?? '') === 'irregular'): ?>
        if (role === 'Student') targetUrl += '&enrollment_status=Irregular';
        <?php endif; ?>
        window.location.href = targetUrl;
    });

    updateRoleHighlight();
});
</script>

<!-- Modal: Select Account Role -->
<div class="modal fade" id="selectRoleModal" tabindex="-1" aria-labelledby="selectRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content border-0 shadow" style="border: 1px solid #e2e8f0 !important; border-radius: 10px;">
            <div class="modal-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="modal-title h6 fw-semibold text-dark mb-0" id="selectRoleModalLabel">Select User Role</h5>
                    <small class="text-muted">Choose the account type to create.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex flex-column gap-2.5" id="roleOptionsList">
                    <!-- Student -->
                    <div class="role-select-item p-3 rounded-3 border d-flex align-items-center gap-3" style="cursor: pointer; border-color: #cbd5e1; transition: all 0.15s ease;">
                        <input type="radio" class="form-check-input flex-shrink-0" name="account_role" value="Student" checked>
                        <div class="d-flex align-items-center justify-content-center rounded-2 flex-shrink-0" style="width: 38px; height: 38px; background-color: #eff6ff; color: #1e3a8a;">
                            <i class="bi bi-mortarboard-fill" style="font-size: 18px;"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-center justify-content-between mb-0.5">
                                <span class="fw-semibold text-dark" style="font-size: 13.5px;">Student</span>
                                <span class="badge badge-student">Student</span>
                            </div>
                            <div class="text-muted" style="font-size: 12px; line-height: 1.4;">Class enrollment, grades, and academic records.</div>
                        </div>
                    </div>

                    <!-- Faculty -->
                    <div class="role-select-item p-3 rounded-3 border d-flex align-items-center gap-3" style="cursor: pointer; border-color: #cbd5e1; transition: all 0.15s ease;">
                        <input type="radio" class="form-check-input flex-shrink-0" name="account_role" value="Faculty">
                        <div class="d-flex align-items-center justify-content-center rounded-2 flex-shrink-0" style="width: 38px; height: 38px; background-color: #f0fdf4; color: #166534;">
                            <i class="bi bi-person-badge-fill" style="font-size: 18px;"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-center justify-content-between mb-0.5">
                                <span class="fw-semibold text-dark" style="font-size: 13.5px;">Faculty / Instructor</span>
                                <span class="badge badge-faculty">Faculty</span>
                            </div>
                            <div class="text-muted" style="font-size: 12px; line-height: 1.4;">Teaching assignments, grading sheets, and student rosters.</div>
                        </div>
                    </div>

                    <!-- Dean -->
                    <div class="role-select-item p-3 rounded-3 border d-flex align-items-center gap-3" style="cursor: pointer; border-color: #cbd5e1; transition: all 0.15s ease;">
                        <input type="radio" class="form-check-input flex-shrink-0" name="account_role" value="Dean">
                        <div class="d-flex align-items-center justify-content-center rounded-2 flex-shrink-0" style="width: 38px; height: 38px; background-color: #faf5ff; color: #6b21a8;">
                            <i class="bi bi-award-fill" style="font-size: 18px;"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-center justify-content-between mb-0.5">
                                <span class="fw-semibold text-dark" style="font-size: 13.5px;">Academic Dean</span>
                                <span class="badge badge-dean">Dean</span>
                            </div>
                            <div class="text-muted" style="font-size: 12px; line-height: 1.4;">Collegiate department management and grading audit reviews.</div>
                        </div>
                    </div>

                    <!-- Admin -->
                    <div class="role-select-item p-3 rounded-3 border d-flex align-items-center gap-3" style="cursor: pointer; border-color: #cbd5e1; transition: all 0.15s ease;">
                        <input type="radio" class="form-check-input flex-shrink-0" name="account_role" value="Admin">
                        <div class="d-flex align-items-center justify-content-center rounded-2 flex-shrink-0" style="width: 38px; height: 38px; background-color: #f1f5f9; color: #334155;">
                            <i class="bi bi-shield-lock-fill" style="font-size: 18px;"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-center justify-content-between mb-0.5">
                                <span class="fw-semibold text-dark" style="font-size: 13.5px;">Administrator</span>
                                <span class="badge badge-admin">Admin</span>
                            </div>
                            <div class="text-muted" style="font-size: 12px; line-height: 1.4;">System governance, academic terms, and user provisioning.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4 border-top d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="btnProceedRole" class="btn btn-primary d-inline-flex align-items-center gap-1.5" style="background-color: #1e3a8a; border-color: #1e3a8a;">
                    Continue <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Batch Excel / CSV Import -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow" style="border: 1px solid #cbd5e1 !important; border-radius: 8px;">
            <div class="modal-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="modal-title h6 fw-bold text-dark mb-0" id="importExcelModalLabel">
                        Batch Student Registration
                    </h5>
                    <small class="text-muted" style="font-size: 12px;">Upload a student roster spreadsheet (.xlsx, .xls, or .csv) to register multiple accounts.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Step 1: Template and File Picker -->
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 pb-3 border-bottom">
                    <div>
                        <div class="fw-semibold text-dark small">Spreadsheet Template Format</div>
                        <div class="text-muted" style="font-size: 12px;">Required columns: Student Number, First Name, Last Name, Email, Enrollment Status, Year Level, Section</div>
                    </div>
                    <a href="<?= url('/admin/users/import-template') ?>" class="btn btn-outline-secondary btn-sm text-nowrap d-inline-flex align-items-center gap-1.5">
                        <i class="bi bi-download"></i> Download Template (.csv)
                    </a>
                </div>

                <!-- Dropzone / File Selector -->
                <div id="indexImportDropzone" class="border border-2 border-dashed rounded-3 p-4 text-center mb-3" style="border-color: #cbd5e1 !important; background-color: #f8fafc; cursor: pointer; transition: all 0.2s ease;">
                    <i class="bi bi-file-earmark-excel text-primary d-block mb-2" style="font-size: 32px;"></i>
                    <div class="fw-semibold text-dark mb-1">Click to select or drag and drop your spreadsheet here</div>
                    <div class="text-muted small">Supports Excel (.xlsx, .xls) and Comma-Separated Values (.csv)</div>
                    <input type="file" id="indexExcelFileInput" accept=".xlsx,.xls,.csv" class="d-none">
                </div>

                <!-- Progress / Error Banner -->
                <div id="indexImportStatusAlert" class="alert d-none py-2 px-3 small mb-3"></div>

                <!-- Step 2: Parsed Rows Preview Table -->
                <div id="indexPreviewContainer" class="d-none">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="fw-semibold text-dark small" id="indexPreviewSummary">
                            Verification Preview: 0 records found
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary" id="indexPreviewBadge">Ready to import</span>
                    </div>

                    <div class="table-responsive border rounded-2" style="max-height: 280px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12px;">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th class="py-2 px-2.5">#</th>
                                    <th class="py-2 px-2.5">Student ID</th>
                                    <th class="py-2 px-2.5">Full Name</th>
                                    <th class="py-2 px-2.5">Email Address</th>
                                    <th class="py-2 px-2.5">Classification</th>
                                    <th class="py-2 px-2.5">Year</th>
                                    <th class="py-2 px-2.5">Section</th>
                                    <th class="py-2 px-2.5 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="indexPreviewTableBody">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light py-2.5 px-4 border-top d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="indexBtnSubmitImport" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5" disabled style="background-color: #1e3a8a; border-color: #1e3a8a;">
                    <i class="bi bi-cloud-arrow-up"></i> Register Verified Records
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= url('/assets/js/xlsx.full.min.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var dropzone = document.getElementById('indexImportDropzone');
    var fileInput = document.getElementById('indexExcelFileInput');
    var previewContainer = document.getElementById('indexPreviewContainer');
    var previewTableBody = document.getElementById('indexPreviewTableBody');
    var previewSummary = document.getElementById('indexPreviewSummary');
    var previewBadge = document.getElementById('indexPreviewBadge');
    var btnSubmitImport = document.getElementById('indexBtnSubmitImport');
    var statusAlert = document.getElementById('indexImportStatusAlert');
    var parsedStudents = [];

    if (dropzone && fileInput) {
        dropzone.addEventListener('click', function() {
            fileInput.click();
        });

        dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropzone.style.borderColor = '#1e3a8a';
            dropzone.style.backgroundColor = '#eff6ff';
        });

        dropzone.addEventListener('dragleave', function() {
            dropzone.style.borderColor = '#cbd5e1';
            dropzone.style.backgroundColor = '#f8fafc';
        });

        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropzone.style.borderColor = '#cbd5e1';
            dropzone.style.backgroundColor = '#f8fafc';
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                processFile(e.dataTransfer.files[0]);
            }
        });

        fileInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                processFile(e.target.files[0]);
            }
        });
    }

    function processFile(file) {
        statusAlert.className = 'alert alert-info py-2 px-3 small mb-3';
        statusAlert.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Processing file: ' + file.name + '...';
        statusAlert.classList.remove('d-none');

        var reader = new FileReader();
        reader.onload = function(e) {
            try {
                var data = new Uint8Array(e.target.result);
                var workbook = XLSX.read(data, { type: 'array' });
                var firstSheetName = workbook.SheetNames[0];
                var worksheet = workbook.Sheets[firstSheetName];
                var rawRows = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

                if (!rawRows || rawRows.length < 2) {
                    showError('Spreadsheet contains no data rows.');
                    return;
                }

                var headerRow = rawRows[0].map(function(h) { return String(h || '').trim().toLowerCase(); });
                var colMap = {
                    studentNumber: findCol(headerRow, ['student number', 'student id', 'id', 'student_number']),
                    firstName: findCol(headerRow, ['first name', 'given name', 'firstname', 'first_name']),
                    lastName: findCol(headerRow, ['last name', 'surname', 'lastname', 'family name', 'last_name']),
                    email: findCol(headerRow, ['email', 'email address', 'e-mail']),
                    status: findCol(headerRow, ['enrollment status', 'status', 'classification']),
                    yearLevel: findCol(headerRow, ['year level', 'year', 'year_level', 'level']),
                    section: findCol(headerRow, ['section', 'class section', 'set', 'class set'])
                };

                parsedStudents = [];
                var validCount = 0;
                var errorCount = 0;

                for (var r = 1; r < rawRows.length; r++) {
                    var row = rawRows[r];
                    if (!row || row.length === 0 || row.every(function(cell) { return cell === '' || cell == null; })) {
                        continue;
                    }

                    var student = {
                        student_number: colMap.studentNumber !== -1 ? String(row[colMap.studentNumber] || '').trim() : '',
                        first_name: colMap.firstName !== -1 ? String(row[colMap.firstName] || '').trim() : '',
                        last_name: colMap.lastName !== -1 ? String(row[colMap.lastName] || '').trim() : '',
                        email: colMap.email !== -1 ? String(row[colMap.email] || '').trim() : '',
                        student_status: colMap.status !== -1 ? String(row[colMap.status] || 'Regular').trim() : 'Regular',
                        year_level: colMap.yearLevel !== -1 ? String(row[colMap.yearLevel] || '1').trim() : '1',
                        section: colMap.section !== -1 ? String(row[colMap.section] || '').trim() : ''
                    };

                    var normStatus = student.student_status.toLowerCase();
                    student.student_status = (normStatus.indexOf('irreg') !== -1) ? 'Irregular' : 'Regular';
                    var numYear = parseInt(student.year_level, 10);
                    student.year_level = (isNaN(numYear) || numYear < 1 || numYear > 4) ? 1 : numYear;

                    var errors = [];
                    if (!student.first_name) errors.push('Missing first name');
                    if (!student.last_name) errors.push('Missing last name');
                    if (!student.email || student.email.indexOf('@') === -1) errors.push('Invalid email');

                    student._valid = errors.length === 0;
                    student._errors = errors;
                    if (student._valid) validCount++; else errorCount++;

                    parsedStudents.push(student);
                }

                if (parsedStudents.length === 0) {
                    showError('No valid student rows found in file.');
                    return;
                }

                renderPreview(parsedStudents, validCount, errorCount);
            } catch (err) {
                showError('Failed to parse spreadsheet: ' + err.message);
            }
        };
        reader.readAsArrayBuffer(file);
    }

    function findCol(headers, aliases) {
        for (var i = 0; i < headers.length; i++) {
            var h = headers[i];
            for (var a = 0; a < aliases.length; a++) {
                if (h === aliases[a] || h.indexOf(aliases[a]) !== -1) {
                    return i;
                }
            }
        }
        return -1;
    }

    function renderPreview(students, validCount, errorCount) {
        previewTableBody.innerHTML = '';
        students.forEach(function(s, idx) {
            var tr = document.createElement('tr');
            tr.className = s._valid ? '' : 'table-danger';
            tr.innerHTML = 
                '<td class="py-1.5 px-2.5 text-muted">' + (idx + 1) + '</td>' +
                '<td class="py-1.5 px-2.5 font-monospace">' + (s.student_number || '<span class="text-muted">—</span>') + '</td>' +
                '<td class="py-1.5 px-2.5 fw-medium">' + s.last_name + ', ' + s.first_name + '</td>' +
                '<td class="py-1.5 px-2.5">' + s.email + '</td>' +
                '<td class="py-1.5 px-2.5"><span class="badge ' + (s.student_status === 'Regular' ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning-emphasis') + '">' + s.student_status + '</span></td>' +
                '<td class="py-1.5 px-2.5">Yr ' + s.year_level + '</td>' +
                '<td class="py-1.5 px-2.5">' + (s.student_status === 'Regular' ? (s.section || 'Auto') : '<span class="text-muted">None (Irreg)</span>') + '</td>' +
                '<td class="py-1.5 px-2.5 text-center">' + 
                    (s._valid 
                        ? '<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check"></i> Ready</span>' 
                        : '<span class="badge bg-danger-subtle text-danger border border-danger-subtle" title="' + s._errors.join(', ') + '"><i class="bi bi-x"></i> Invalid</span>') + 
                '</td>';
            previewTableBody.appendChild(tr);
        });

        previewSummary.textContent = 'Found ' + students.length + ' records (' + validCount + ' valid, ' + errorCount + ' with issues)';
        previewBadge.textContent = validCount > 0 ? validCount + ' ready to register' : 'No valid records';
        previewBadge.className = 'badge ' + (validCount > 0 ? 'bg-primary text-white' : 'bg-danger text-white');

        statusAlert.className = 'alert alert-success py-2 px-3 small mb-3';
        statusAlert.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Parsed ' + students.length + ' student records. Review the preview below before submitting.';

        previewContainer.classList.remove('d-none');
        btnSubmitImport.disabled = (validCount === 0);
        btnSubmitImport.innerHTML = '<i class="bi bi-cloud-arrow-up"></i> Register ' + validCount + ' Verified Students';
    }

    function showError(msg) {
        statusAlert.className = 'alert alert-danger py-2 px-3 small mb-3';
        statusAlert.innerHTML = '<i class="bi bi-exclamation-octagon me-1"></i> ' + msg;
        statusAlert.classList.remove('d-none');
        btnSubmitImport.disabled = true;
    }

    if (btnSubmitImport) {
        btnSubmitImport.addEventListener('click', function() {
            var validToSubmit = parsedStudents.filter(function(s) { return s._valid; });
            if (validToSubmit.length === 0) return;

            btnSubmitImport.disabled = true;
            btnSubmitImport.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Submitting Registration...';

            var csrfToken = '<?= csrf_token() ?>';

            fetch('<?= url("/admin/users/import") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({
                    _token: csrfToken,
                    students: validToSubmit
                })
            })
            .then(function(res) {
                return res.json().then(function(data) {
                    return { ok: res.ok, data: data };
                });
            })
            .then(function(result) {
                if (result.ok && result.data.success) {
                    statusAlert.className = 'alert alert-success py-2 px-3 small mb-3';
                    statusAlert.innerHTML = '<i class="bi bi-check2-circle me-1"></i> ' + result.data.message;
                    setTimeout(function() {
                        window.location.reload();
                    }, 1200);
                } else {
                    var errorMsg = result.data.message || 'Import failed.';
                    if (result.data.errors && result.data.errors.length > 0) {
                        errorMsg += '<ul class="mb-0 mt-1 ps-3">' + result.data.errors.map(function(e) {
                            return '<li>' + (e.name ? e.name + ': ' : '') + e.message + '</li>';
                        }).join('') + '</ul>';
                    }
                    showError(errorMsg);
                    btnSubmitImport.disabled = false;
                    btnSubmitImport.innerHTML = '<i class="bi bi-cloud-arrow-up"></i> Retry Registration';
                }
            })
            .catch(function(err) {
                showError('Network error: ' + err.message);
                btnSubmitImport.disabled = false;
                btnSubmitImport.innerHTML = '<i class="bi bi-cloud-arrow-up"></i> Retry Registration';
            });
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
