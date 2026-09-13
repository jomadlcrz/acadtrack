<?php
$pageTitle = 'Curricular Subjects';
$subtitle = 'Academic course offerings organized by year level and semester placement.';
$currentFilter = $statusFilter ?? 'all';
$headerActions = '<span class="badge bg-light text-secondary border px-2.5 py-1.5 fs-7 d-inline-flex align-items-center gap-1.5"><i class="bi bi-shield-lock"></i> Read-Only View</span>';

// Organize subjects into Year Level & Semester groupings
$yearLabels = [
    1 => '1st Year',
    2 => '2nd Year',
    3 => '3rd Year',
    4 => '4th Year',
];
$semLabels = [
    1 => '1st Semester',
    2 => '2nd Semester',
    3 => 'Summer',
];

$groups = [];
for ($y = 1; $y <= 4; $y++) {
    for ($s = 1; $s <= 2; $s++) {
        $key = "{$y}-{$s}";
        $groups[$key] = [
            'year_level' => $y,
            'semester' => $s,
            'year_label' => $yearLabels[$y],
            'sem_label' => $semLabels[$s],
            'title' => "{$yearLabels[$y]} · {$semLabels[$s]}",
            'subjects' => [],
            'total_units' => 0.0,
        ];
    }
}

$otherGroups = [];
$totalCatalogUnits = 0.0;
$activeCount = 0;
$archivedCount = 0;

foreach ($subjects as $subject) {
    $y = (int) ($subject['year_level'] ?? 1);
    $s = (int) ($subject['semester'] ?? 1);
    $key = "{$y}-{$s}";
    $units = (float) ($subject['units'] ?? 3.0);
    $totalCatalogUnits += $units;

    if (!empty($subject['is_archived'])) {
        $archivedCount++;
    } else {
        $activeCount++;
    }

    if (isset($groups[$key])) {
        $groups[$key]['subjects'][] = $subject;
        $groups[$key]['total_units'] += $units;
    } else {
        $yl = $yearLabels[$y] ?? ($y > 0 ? "{$y}th Year" : 'General / Electives');
        $sl = $semLabels[$s] ?? ($s === 3 ? 'Summer' : "Semester {$s}");
        if (!isset($otherGroups[$key])) {
            $otherGroups[$key] = [
                'year_level' => $y,
                'semester' => $s,
                'year_label' => $yl,
                'sem_label' => $sl,
                'title' => "{$yl} · {$sl}",
                'subjects' => [],
                'total_units' => 0.0,
            ];
        }
        $otherGroups[$key]['subjects'][] = $subject;
        $otherGroups[$key]['total_units'] += $units;
    }
}

$allGroups = array_merge($groups, $otherGroups);

ob_start();
?>

<!-- Metric KPI Cards (Matching Dashboard Standard) -->
<div class="dashboard-stats mb-4">
    <div class="stat-card">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Total courses</span>
                <div class="stat-icon stat-icon-blue"><i class="bi bi-journal-bookmark-fill"></i></div>
            </div>
            <div class="stat-value tabular-nums" id="statTotalCourses"><?= count($subjects) ?></div>
        </div>
        <span class="stat-subtext">Curriculum catalog</span>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Active offerings</span>
                <div class="stat-icon stat-icon-green"><i class="bi bi-check-circle-fill"></i></div>
            </div>
            <div class="stat-value tabular-nums text-success"><?= $activeCount ?></div>
        </div>
        <span class="stat-subtext">Catalog active</span>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Archived courses</span>
                <div class="stat-icon stat-icon-purple"><i class="bi bi-archive-fill"></i></div>
            </div>
            <div class="stat-value tabular-nums"><?= $archivedCount ?></div>
        </div>
        <span class="stat-subtext">Inactive historical</span>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Total credit units</span>
                <div class="stat-icon stat-icon-amber"><i class="bi bi-award-fill"></i></div>
            </div>
            <div class="stat-value tabular-nums"><?= number_format($totalCatalogUnits, 1) ?></div>
        </div>
        <span class="stat-subtext">Cumulative units</span>
    </div>
</div>

<!-- Search & Filtering Controls Bar -->
<div class="d-flex flex-column flex-md-row gap-3 align-items-md-center justify-content-between mb-4">
    <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1" style="max-width: 720px;">
        <div class="position-relative flex-grow-1" style="min-width: 240px;">
            <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 14px;"></i>
            <input type="text" 
                   id="subjectSearch" 
                   class="form-control ps-5" 
                   placeholder="Search course code or descriptive title..." 
                   autocomplete="off">
        </div>

        <select class="form-select w-auto" id="yearLevelFilter" onchange="applyFilters()">
            <option value="ALL">All Year Levels</option>
            <option value="1">1st Year Only</option>
            <option value="2">2nd Year Only</option>
            <option value="3">3rd Year Only</option>
            <option value="4">4th Year Only</option>
        </select>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2">
        <div class="btn-group btn-group-sm" role="group" aria-label="Status filter">
            <a href="<?= url('/dean/subjects?status=all') ?>" class="btn <?= $currentFilter === 'all' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                All
            </a>
            <a href="<?= url('/dean/subjects?status=active') ?>" class="btn <?= $currentFilter === 'active' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                Active Only
            </a>
            <a href="<?= url('/dean/subjects?status=archived') ?>" class="btn <?= $currentFilter === 'archived' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                Archived Only
            </a>
        </div>
        <div class="text-muted small fw-medium text-nowrap ms-1" id="visibleCounter">
            <?= count($subjects) ?> <?= count($subjects) === 1 ? 'course' : 'courses' ?>
        </div>
    </div>
</div>

<!-- Year Level & Semester Group Containers -->
<div class="space-y-4" id="groupsContainer">
    <?php $anyGroupHasSubjects = false; ?>
    <?php foreach ($allGroups as $groupKey => $group): ?>
        <?php 
            $subList = $group['subjects'];
            if (empty($subList) && $currentFilter !== 'all') {
                continue; // Skip completely empty containers when filtering
            }
            if (!empty($subList)) {
                $anyGroupHasSubjects = true;
            }
        ?>
        <div class="card shadow-sm border-0 mb-4 subject-group-container" 
             id="group-<?= htmlspecialchars($groupKey) ?>"
             data-year="<?= $group['year_level'] ?>"
             data-sem="<?= $group['semester'] ?>"
             style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
            
            <!-- Group Container Header -->
            <div class="card-header bg-light py-2.5 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <h3 class="h6 fw-bold text-dark mb-0">
                        <?= htmlspecialchars($group['title']) ?>
                    </h3>
                </div>
                <div class="small text-muted d-flex align-items-center gap-2">
                    <span class="badge bg-white text-dark border px-2 py-0.5">
                        <span class="group-count" id="count-<?= htmlspecialchars($groupKey) ?>"><?= count($subList) ?></span> <?= count($subList) === 1 ? 'course' : 'courses' ?>
                    </span>
                    <span class="text-secondary">&middot;</span>
                    <span class="fw-semibold text-dark tabular-nums"><?= number_format($group['total_units'], 1) ?> units</span>
                </div>
            </div>

            <!-- Group Subjects Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-white border-bottom">
                        <tr>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 11px;">Subject Code</th>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Descriptive Title</th>
                            <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 90px; font-size: 11px;">Units</th>
                            <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 150px; font-size: 11px;">Course Type</th>
                            <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 150px; font-size: 11px;">Assigned Faculty</th>
                            <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 110px; font-size: 11px;">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php if (empty($subList)): ?>
                            <tr class="group-empty-row">
                                <td colspan="6" class="text-center py-4 text-muted small">
                                    <i class="bi bi-dash-circle me-1 text-secondary"></i>
                                    No courses registered for this semester placement.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($subList as $subject): ?>
                                <?php 
                                    $isArchived = !empty($subject['is_archived']);
                                    $facultyCount = (int) ($subject['assigned_faculty_count'] ?? 0);
                                    $units = (float) ($subject['units'] ?? 3.0);
                                    $sType = (string) ($subject['subject_type'] ?? ($subject['nature'] ?? 'Lecture'));
                                ?>
                                <tr class="subject-row <?= $isArchived ? 'table-light text-muted' : '' ?>"
                                    data-code="<?= strtolower(htmlspecialchars($subject['subject_code'] ?? $subject['code'] ?? '')) ?>"
                                    data-title="<?= strtolower(htmlspecialchars($subject['descriptive_title'] ?? $subject['name'] ?? '')) ?>"
                                    data-type="<?= strtolower(htmlspecialchars($sType)) ?>">
                                    <td class="py-3 px-4">
                                        <span class="badge bg-light <?= $isArchived ? 'text-secondary' : 'text-primary' ?> border font-monospace px-2.5 py-1 fw-bold">
                                            <?= htmlspecialchars($subject['subject_code'] ?? $subject['code']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 fw-medium <?= $isArchived ? 'text-secondary' : 'text-dark' ?>">
                                        <?= htmlspecialchars($subject['descriptive_title'] ?? $subject['name']) ?>
                                        <?php if ($isArchived && !empty($subject['archived_at'])): ?>
                                            <small class="d-block text-muted fw-normal" style="font-size: 0.75rem;">
                                                <i class="bi bi-archive me-1"></i>Archived on <?= date('M d, Y', strtotime($subject['archived_at'])) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-3 text-center tabular-nums fw-semibold text-secondary">
                                        <?= number_format($units, 1) ?>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="badge bg-light text-secondary border px-2 py-0.5 small fw-normal">
                                            <?= htmlspecialchars($sType) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <?php if ($facultyCount > 0): ?>
                                            <span class="badge bg-light text-dark border px-2 py-1 small">
                                                <i class="bi bi-person-badge text-primary me-1"></i><?= $facultyCount ?> <?= $facultyCount === 1 ? 'faculty' : 'faculties' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted small">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <?php if ($isArchived): ?>
                                            <span class="badge rounded-pill fw-medium" style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">
                                                <i class="bi bi-archive me-1"></i>Archived
                                            </span>
                                        <?php else: ?>
                                            <span class="badge rounded-pill fw-medium" style="background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;">
                                                <i class="bi bi-check-circle me-1"></i>Active
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Global Empty Search Result Card -->
    <div id="noSearchResultsCard" class="card shadow-sm border-0 text-center py-5 d-none mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
        <div class="card-body">
            <i class="bi bi-search d-block fs-2 mb-2 text-secondary"></i>
            <h4 class="h6 fw-bold text-dark mb-1">No matching courses found</h4>
            <p class="text-muted small mb-3">Try adjusting your search keywords or year level filter.</p>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filters
            </button>
        </div>
    </div>
</div>

<script>
function applyFilters() {
    const q = (document.getElementById('subjectSearch').value || '').trim().toLowerCase();
    const yearLevel = document.getElementById('yearLevelFilter').value;
    const containers = document.querySelectorAll('.subject-group-container');
    const noResultsCard = document.getElementById('noSearchResultsCard');
    let totalVisibleCourses = 0;

    containers.forEach(container => {
        const containerYear = container.getAttribute('data-year') || '';
        const matchYear = yearLevel === 'ALL' || containerYear === yearLevel;

        if (!matchYear) {
            container.style.display = 'none';
            return;
        }

        const rows = container.querySelectorAll('.subject-row');
        let visibleInContainer = 0;

        rows.forEach(row => {
            const code = row.getAttribute('data-code') || '';
            const title = row.getAttribute('data-title') || '';
            const type = row.getAttribute('data-type') || '';
            const matches = !q || code.includes(q) || title.includes(q) || type.includes(q);

            if (matches) {
                row.style.display = '';
                visibleInContainer++;
                totalVisibleCourses++;
            } else {
                row.style.display = 'none';
            }
        });

        const countBadge = container.querySelector('.group-count');
        if (countBadge) {
            countBadge.textContent = visibleInContainer;
        }

        // Hide container if it has no matching subjects under the search filter
        if (q && visibleInContainer === 0) {
            container.style.display = 'none';
        } else {
            container.style.display = '';
        }
    });

    const visibleCounter = document.getElementById('visibleCounter');
    if (visibleCounter) {
        visibleCounter.textContent = totalVisibleCourses + (totalVisibleCourses === 1 ? ' course' : ' courses');
    }

    if (noResultsCard) {
        if (totalVisibleCourses === 0) {
            noResultsCard.classList.remove('d-none');
        } else {
            noResultsCard.classList.add('d-none');
        }
    }
}

function resetFilters() {
    const searchInput = document.getElementById('subjectSearch');
    const yearSelect = document.getElementById('yearLevelFilter');
    if (searchInput) searchInput.value = '';
    if (yearSelect) yearSelect.value = 'ALL';
    applyFilters();
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('subjectSearch');
    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
