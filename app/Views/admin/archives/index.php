<?php
$pageTitle = 'Archives';
$subtitle = 'Review and restore archived curriculum subjects, sections, academic terms, departments, and user accounts.';
$headerActions = '<div class="d-flex align-items-center gap-2">' .
    '<span class="badge bg-light text-secondary border px-3 py-2 fw-medium d-inline-flex align-items-center gap-1.5" style="font-size: 13px;">' .
    '<i class="bi bi-archive text-primary"></i> <strong class="text-dark">' . $totalArchived . '</strong> Total Archived Records' .
    '</span>' .
'</div>';

ob_start();
?>

<!-- Tab Navigation Pills -->
<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="p-3 bg-white border-bottom">
        <ul class="nav nav-pills gap-2" id="archiveTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a href="<?= url('/admin/archives?tab=subjects') ?>" class="nav-link px-3 py-2 fw-medium d-inline-flex align-items-center gap-2 <?= $activeTab === 'subjects' ? 'active' : '' ?>">
                    <i class="bi bi-journal-text"></i>
                    <span>Subjects</span>
                    <span class="badge <?= $activeTab === 'subjects' ? 'bg-white text-primary' : 'bg-light text-secondary border' ?> rounded-pill">
                        <?= $countSubjects ?>
                    </span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="<?= url('/admin/archives?tab=sets') ?>" class="nav-link px-3 py-2 fw-medium d-inline-flex align-items-center gap-2 <?= $activeTab === 'sets' ? 'active' : '' ?>">
                    <i class="bi bi-collection"></i>
                    <span>Sections / Sets</span>
                    <span class="badge <?= $activeTab === 'sets' ? 'bg-white text-primary' : 'bg-light text-secondary border' ?> rounded-pill">
                        <?= $countSets ?>
                    </span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="<?= url('/admin/archives?tab=terms') ?>" class="nav-link px-3 py-2 fw-medium d-inline-flex align-items-center gap-2 <?= $activeTab === 'terms' ? 'active' : '' ?>">
                    <i class="bi bi-calendar3"></i>
                    <span>Academic Terms</span>
                    <span class="badge <?= $activeTab === 'terms' ? 'bg-white text-primary' : 'bg-light text-secondary border' ?> rounded-pill">
                        <?= $countTerms ?>
                    </span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="<?= url('/admin/archives?tab=departments') ?>" class="nav-link px-3 py-2 fw-medium d-inline-flex align-items-center gap-2 <?= $activeTab === 'departments' ? 'active' : '' ?>">
                    <i class="bi bi-building"></i>
                    <span>Departments</span>
                    <span class="badge <?= $activeTab === 'departments' ? 'bg-white text-primary' : 'bg-light text-secondary border' ?> rounded-pill">
                        <?= $countDepartments ?>
                    </span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="<?= url('/admin/archives?tab=users') ?>" class="nav-link px-3 py-2 fw-medium d-inline-flex align-items-center gap-2 <?= $activeTab === 'users' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i>
                    <span>User Accounts</span>
                    <span class="badge <?= $activeTab === 'users' ? 'bg-white text-primary' : 'bg-light text-secondary border' ?> rounded-pill">
                        <?= $countUsers ?>
                    </span>
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Tab 1: Archived Subjects -->
<?php if ($activeTab === 'subjects'): ?>
    <div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
        <div class="p-3 border-bottom bg-white d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="position-relative flex-grow-1" style="max-width: 380px;">
                <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 12px; font-size: 13px;"></i>
                <input type="text" id="archiveSearch" class="form-control form-control-sm ps-4" placeholder="Search archived subjects..." autocomplete="off">
            </div>
            <div class="text-muted small fw-medium">
                <span id="archiveTableCount"><?= count($archivedSubjects) ?></span> archived <?= count($archivedSubjects) === 1 ? 'subject' : 'subjects' ?>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="archiveTable">
                <thead class="bg-light border-bottom">
                    <tr>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 11px;">Subject Code</th>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Descriptive Title</th>
                        <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="width: 160px; font-size: 11px;">Program</th>
                        <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 90px; font-size: 11px;">Units</th>
                        <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="width: 150px; font-size: 11px;">Type</th>
                        <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 11px;">Archived Date</th>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="width: 110px; font-size: 11px;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (empty($archivedSubjects) || $archivedSubjects->isEmpty()): ?>
                        <tr>
                            <td colspan="7" class="p-0">
                                <?php
                                $icon = 'bi-journal-check';
                                $title = 'No archived subjects';
                                $message = 'All curriculum subjects are active. When a subject is archived, it will appear here.';
                                include __DIR__ . '/../../components/empty-state.php';
                                ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($archivedSubjects as $sub): ?>
                            <tr class="archive-row" data-search="<?= strtolower(htmlspecialchars($sub->subject_code . ' ' . $sub->descriptive_title . ' ' . ($sub->program->program_abbrev ?? ''))) ?>">
                                <td class="py-3 px-4 fw-bold text-dark font-monospace">
                                    <?= htmlspecialchars($sub->subject_code) ?>
                                </td>
                                <td class="py-3 px-4 fw-medium text-dark">
                                    <?= htmlspecialchars($sub->descriptive_title) ?>
                                </td>
                                <td class="py-3 px-3 small">
                                    <span class="badge bg-light text-primary border">
                                        <?= htmlspecialchars($sub->program->program_abbrev ?? 'General') ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center tabular-nums fw-semibold text-secondary">
                                    <?= number_format((float) $sub->units, 1) ?>
                                </td>
                                <td class="py-3 px-3 small text-muted">
                                    <?= htmlspecialchars($sub->subject_type ?? 'GenEd Core') ?>
                                </td>
                                <td class="py-3 px-3 small text-muted">
                                    <?= !empty($sub->archived_at) ? date('M j, Y', strtotime($sub->archived_at)) : 'Archived' ?>
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <form method="POST" action="<?= url('/admin/archives/subjects/' . $sub->id . '/restore') ?>" class="m-0" onsubmit="return confirm('Restore subject <?= htmlspecialchars(addslashes($sub->subject_code)) ?> to the active curriculum?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2.5 d-inline-flex align-items-center gap-1" title="Restore Subject">
                                            <i class="bi bi-arrow-counterclockwise"></i> Restore
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<!-- Tab 2: Archived Sets / Sections -->
<?php elseif ($activeTab === 'sets'): ?>
    <div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
        <div class="p-3 border-bottom bg-white d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="position-relative flex-grow-1" style="max-width: 380px;">
                <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 12px; font-size: 13px;"></i>
                <input type="text" id="archiveSearch" class="form-control form-control-sm ps-4" placeholder="Search archived sections..." autocomplete="off">
            </div>
            <div class="text-muted small fw-medium">
                <span id="archiveTableCount"><?= count($archivedSets) ?></span> archived <?= count($archivedSets) === 1 ? 'section' : 'sections' ?>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="archiveTable">
                <thead class="bg-light border-bottom">
                    <tr>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="width: 160px; font-size: 11px;">Section Name</th>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Program</th>
                        <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 120px; font-size: 11px;">Year Level</th>
                        <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 120px; font-size: 11px;">Enrolled Students</th>
                        <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 11px;">Status</th>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="width: 110px; font-size: 11px;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (empty($archivedSets) || $archivedSets->isEmpty()): ?>
                        <tr>
                            <td colspan="6" class="p-0">
                                <?php
                                $icon = 'bi-collection';
                                $title = 'No archived sections';
                                $message = 'All student sections and sets are currently active.';
                                include __DIR__ . '/../../components/empty-state.php';
                                ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($archivedSets as $st): ?>
                            <tr class="archive-row" data-search="<?= strtolower(htmlspecialchars($st->name . ' ' . ($st->program->program_abbrev ?? ''))) ?>">
                                <td class="py-3 px-4 fw-bold text-dark font-monospace">
                                    <?= htmlspecialchars($st->name) ?>
                                </td>
                                <td class="py-3 px-4 small">
                                    <span class="fw-medium text-dark"><?= htmlspecialchars($st->program->program_abbrev ?? '') ?></span>
                                    <span class="text-muted small ms-1"><?= htmlspecialchars($st->program->program_name ?? '') ?></span>
                                </td>
                                <td class="py-3 px-3 text-center small text-secondary">
                                    Year <?= htmlspecialchars((string) $st->year_level) ?>
                                </td>
                                <td class="py-3 px-3 text-center tabular-nums fw-semibold text-secondary">
                                    <?= (int) ($st->students_count ?? 0) ?>
                                </td>
                                <td class="py-3 px-3 small">
                                    <span class="badge bg-secondary-subtle text-secondary border">Archived / Inactive</span>
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <form method="POST" action="<?= url('/admin/archives/sets/' . $st->id . '/restore') ?>" class="m-0" onsubmit="return confirm('Restore section <?= htmlspecialchars(addslashes($st->name)) ?> to active status?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2.5 d-inline-flex align-items-center gap-1" title="Restore Section">
                                            <i class="bi bi-arrow-counterclockwise"></i> Restore
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<!-- Tab 3: Archived Academic Terms -->
<?php elseif ($activeTab === 'terms'): ?>
    <div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
        <div class="p-3 border-bottom bg-white d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="position-relative flex-grow-1" style="max-width: 380px;">
                <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 12px; font-size: 13px;"></i>
                <input type="text" id="archiveSearch" class="form-control form-control-sm ps-4" placeholder="Search archived terms..." autocomplete="off">
            </div>
            <div class="text-muted small fw-medium">
                <span id="archiveTableCount"><?= count($archivedTerms) ?></span> archived <?= count($archivedTerms) === 1 ? 'term' : 'terms' ?>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="archiveTable">
                <thead class="bg-light border-bottom">
                    <tr>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="width: 180px; font-size: 11px;">School Year</th>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Semester</th>
                        <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="width: 150px; font-size: 11px;">Archived Date</th>
                        <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="width: 130px; font-size: 11px;">Status</th>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="width: 110px; font-size: 11px;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (empty($archivedTerms)): ?>
                        <tr>
                            <td colspan="5" class="p-0">
                                <?php
                                $icon = 'bi-calendar-check';
                                $title = 'No archived academic terms';
                                $message = 'All academic terms are currently in active index.';
                                include __DIR__ . '/../../components/empty-state.php';
                                ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($archivedTerms as $term): ?>
                            <?php
                            $semLabel = match ((int) ($term['semester'] ?? 1)) {
                                1 => '1st Semester',
                                2 => '2nd Semester',
                                3 => 'Summer',
                                default => 'Semester ' . $term['semester'],
                            };
                            $syDisplay = !empty($term['school_year']) ? $term['school_year'] : ($term['academic_year_name'] ?? 'N/A');
                            ?>
                            <tr class="archive-row" data-search="<?= strtolower(htmlspecialchars($syDisplay . ' ' . $semLabel)) ?>">
                                <td class="py-3 px-4 fw-bold text-dark font-monospace">
                                    <?= htmlspecialchars($syDisplay) ?>
                                </td>
                                <td class="py-3 px-4 fw-medium text-dark">
                                    <?= htmlspecialchars($semLabel) ?>
                                </td>
                                <td class="py-3 px-3 small text-muted">
                                    <?= !empty($term['archived_at']) ? date('M j, Y', strtotime($term['archived_at'])) : 'Archived' ?>
                                </td>
                                <td class="py-3 px-3 small">
                                    <span class="badge bg-secondary-subtle text-secondary border">Archived</span>
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <form method="POST" action="<?= url('/admin/archives/academic-terms/' . $term['id'] . '/restore') ?>" class="m-0" onsubmit="return confirm('Restore academic term <?= htmlspecialchars(addslashes($syDisplay)) ?> (<?= $semLabel ?>)?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2.5 d-inline-flex align-items-center gap-1" title="Restore Term">
                                            <i class="bi bi-arrow-counterclockwise"></i> Restore
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<!-- Tab 4: Archived Departments -->
<?php elseif ($activeTab === 'departments'): ?>
    <div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
        <div class="p-3 border-bottom bg-white d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="position-relative flex-grow-1" style="max-width: 380px;">
                <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 12px; font-size: 13px;"></i>
                <input type="text" id="archiveSearch" class="form-control form-control-sm ps-4" placeholder="Search archived departments..." autocomplete="off">
            </div>
            <div class="text-muted small fw-medium">
                <span id="archiveTableCount"><?= count($archivedDepartments) ?></span> archived <?= count($archivedDepartments) === 1 ? 'department' : 'departments' ?>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="archiveTable">
                <thead class="bg-light border-bottom">
                    <tr>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 11px;">Code</th>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Department Name</th>
                        <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 11px;">Faculty Members</th>
                        <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="width: 130px; font-size: 11px;">Status</th>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="width: 110px; font-size: 11px;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (empty($archivedDepartments)): ?>
                        <tr>
                            <td colspan="5" class="p-0">
                                <?php
                                $icon = 'bi-building-check';
                                $title = 'No archived departments';
                                $message = 'All academic departments are active.';
                                include __DIR__ . '/../../components/empty-state.php';
                                ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($archivedDepartments as $dept): ?>
                            <tr class="archive-row" data-search="<?= strtolower(htmlspecialchars(($dept['code'] ?? '') . ' ' . ($dept['name'] ?? ''))) ?>">
                                <td class="py-3 px-4 fw-bold text-dark font-monospace">
                                    <?= htmlspecialchars($dept['code'] ?? '') ?>
                                </td>
                                <td class="py-3 px-4 fw-medium text-dark">
                                    <?= htmlspecialchars($dept['name'] ?? '') ?>
                                </td>
                                <td class="py-3 px-3 text-center tabular-nums fw-semibold text-secondary">
                                    <?= (int) ($dept['faculty_count'] ?? 0) ?>
                                </td>
                                <td class="py-3 px-3 small">
                                    <span class="badge bg-secondary-subtle text-secondary border">Inactive</span>
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <form method="POST" action="<?= url('/admin/archives/departments/' . $dept['id'] . '/restore') ?>" class="m-0" onsubmit="return confirm('Restore department <?= htmlspecialchars(addslashes($dept['name'])) ?> to active status?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2.5 d-inline-flex align-items-center gap-1" title="Restore Department">
                                            <i class="bi bi-arrow-counterclockwise"></i> Restore
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<!-- Tab 5: Archived User Accounts -->
<?php elseif ($activeTab === 'users'): ?>
    <div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
        <div class="p-3 border-bottom bg-white d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="position-relative flex-grow-1" style="max-width: 380px;">
                <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 12px; font-size: 13px;"></i>
                <input type="text" id="archiveSearch" class="form-control form-control-sm ps-4" placeholder="Search deactivated users..." autocomplete="off">
            </div>
            <div class="text-muted small fw-medium">
                <span id="archiveTableCount"><?= count($archivedUsers) ?></span> deactivated <?= count($archivedUsers) === 1 ? 'user' : 'users' ?>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="archiveTable">
                <thead class="bg-light border-bottom">
                    <tr>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">User Name</th>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Email</th>
                        <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 11px;">Role</th>
                        <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="width: 130px; font-size: 11px;">Status</th>
                        <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="width: 120px; font-size: 11px;">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php if (empty($archivedUsers)): ?>
                        <tr>
                            <td colspan="5" class="p-0">
                                <?php
                                $icon = 'bi-people';
                                $title = 'No deactivated users';
                                $message = 'All user accounts are currently active.';
                                include __DIR__ . '/../../components/empty-state.php';
                                ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($archivedUsers as $u): ?>
                            <tr class="archive-row" data-search="<?= strtolower(htmlspecialchars(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '') . ' ' . ($u['email'] ?? '') . ' ' . ($u['role'] ?? ''))) ?>">
                                <td class="py-3 px-4 fw-semibold text-dark">
                                    <?= htmlspecialchars(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) ?>
                                </td>
                                <td class="py-3 px-4 text-muted small">
                                    <?= htmlspecialchars($u['email'] ?? '') ?>
                                </td>
                                <td class="py-3 px-3 small">
                                    <span class="badge badge-<?= strtolower($u['role'] ?? '') ?>"><?= htmlspecialchars($u['role'] ?? '') ?></span>
                                </td>
                                <td class="py-3 px-3 small">
                                    <span class="badge bg-secondary-subtle text-secondary border">Deactivated</span>
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <form method="POST" action="<?= url('/admin/archives/users/' . $u['id'] . '/restore') ?>" class="m-0" onsubmit="return confirm('Activate user account <?= htmlspecialchars(addslashes(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''))) ?>?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2.5 d-inline-flex align-items-center gap-1" title="Activate Account">
                                            <i class="bi bi-person-check"></i> Activate
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('archiveSearch');
    const table = document.getElementById('archiveTable');
    const counter = document.getElementById('archiveTableCount');

    if (searchInput && table) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            const rows = table.querySelectorAll('.archive-row');
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

            if (counter) {
                counter.textContent = visibleCount;
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
