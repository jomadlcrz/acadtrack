<?php
$pageTitle = 'Faculty Dashboard';
$subtitle = 'Teaching workload summary, student enrollment overview, and grade submission portal.';
ob_start();
?>

<!-- Metric KPI Cards (Clickable & Dynamic) -->
<div class="dashboard-stats">
    <a href="<?= url('/faculty/subjects') ?>" class="stat-card stat-card-link">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Assigned subjects</span>
                <div class="stat-icon stat-icon-blue"><i class="bi bi-journal-text"></i></div>
            </div>
            <div class="stat-value tabular-nums"><?= number_format($assignedSubjectsCount ?? 0) ?></div>
        </div>
        <span class="stat-subtext">Teaching workload <i class="bi bi-arrow-right"></i></span>
    </a>

    <a href="<?= url('/faculty/students') ?>" class="stat-card stat-card-link">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Total students</span>
                <div class="stat-icon stat-icon-green"><i class="bi bi-people-fill"></i></div>
            </div>
            <div class="stat-value tabular-nums"><?= number_format($totalStudents ?? 0) ?></div>
        </div>
        <span class="stat-subtext">Active class rosters <i class="bi bi-arrow-right"></i></span>
    </a>

    <a href="<?= url('/faculty/grading') ?>" class="stat-card stat-card-link">
        <div>
            <div class="stat-card-top">
                <span class="stat-label">Grading sheets</span>
                <div class="stat-icon stat-icon-amber"><i class="bi bi-file-earmark-check-fill"></i></div>
            </div>
            <div class="stat-value tabular-nums"><?= number_format($submittedCount ?? 0) ?></div>
        </div>
        <span class="stat-subtext">Submitted &amp; confirmed <i class="bi bi-arrow-right"></i></span>
    </a>
</div>

<!-- Quick Administration Shortcuts -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <a href="<?= url('/faculty/grading') ?>" class="quick-action-card">
            <div class="quick-action-icon bg-primary-subtle text-primary">
                <i class="bi bi-pencil-square"></i>
            </div>
            <div>
                <h4 class="h6 mb-0 fw-semibold">Enter grades</h4>
                <p class="text-muted small mb-0">Input Prelim, Midterm, Finals</p>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= url('/faculty/students') ?>" class="quick-action-card">
            <div class="quick-action-icon bg-success-subtle text-success">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <h4 class="h6 mb-0 fw-semibold">Class roster</h4>
                <p class="text-muted small mb-0">Manage enrolled students &amp; sets</p>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= url('/faculty/subjects') ?>" class="quick-action-card">
            <div class="quick-action-icon bg-info-subtle text-info">
                <i class="bi bi-gear-fill"></i>
            </div>
            <div>
                <h4 class="h6 mb-0 fw-semibold">Course setup</h4>
                <p class="text-muted small mb-0">Configure grading weights &amp; nature</p>
            </div>
        </a>
    </div>
</div>

<!-- Workload Courses Table -->
<div class="card shadow-sm border-0" style="border: 1px solid #e2e8f0 !important; border-radius: 6px; overflow: hidden;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h3 class="h6 mb-0 fw-semibold text-dark">Active Term Assigned Courses</h3>
        <a href="<?= url('/faculty/subjects') ?>" class="btn btn-sm btn-outline-secondary">Course catalog</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-white border-bottom">
                <tr>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Subject code</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Descriptive title</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Nature</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Students</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Grading status</th>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="font-size: 11px;">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (empty($workload)): ?>
                    <tr>
                        <td colspan="6" class="p-0">
                            <?php
                            $icon = 'bi-book';
                            $title = 'No assigned courses';
                            $message = 'No assigned subjects found for the current academic term.';
                            include __DIR__ . '/../components/empty-state.php';
                            ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($workload as $item): ?>
                        <tr>
                            <td class="px-3 fw-semibold font-monospace text-dark">
                                <?= htmlspecialchars($item['code']) ?>
                            </td>
                            <td class="px-3 fw-semibold text-dark">
                                <?= htmlspecialchars($item['name']) ?>
                            </td>
                            <td class="px-3">
                                <span class="badge <?= match($item['nature'] ?? 'Lecture') {
                                    'Laboratory' => 'bg-info-subtle text-info border border-info-subtle',
                                    'Combined' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                    default => 'bg-primary-subtle text-primary border border-primary-subtle'
                                } ?> fw-semibold">
                                    <?= htmlspecialchars($item['nature'] ?? 'Lecture') ?>
                                </span>
                            </td>
                            <td class="px-3 text-muted small">
                                <i class="bi bi-person me-1"></i><?= (int) ($item['enrolled_count'] ?? 0) ?> enrolled
                            </td>
                            <td class="px-3">
                                <span class="badge badge-<?= strtolower($item['sheet_status'] ?? 'draft') ?> fw-semibold">
                                    <?= htmlspecialchars(ucfirst(strtolower($item['sheet_status'] ?? 'Draft'))) ?>
                                </span>
                            </td>
                            <td class="px-3 text-end">
                                <a href="<?= url('/faculty/grading?subject_id=' . $item['id']) ?>" class="btn btn-sm btn-primary py-1 px-2 me-1">
                                    <i class="bi bi-pencil-square"></i> Grades
                                </a>
                                <a href="<?= url('/faculty/students?subject_id=' . $item['id']) ?>" class="btn btn-sm btn-outline-secondary py-1 px-2">
                                    <i class="bi bi-people"></i> Roster
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Top Performers & Subject Analytics Section -->
<div class="card shadow-sm border-0 mt-4" style="border: 1px solid #e2e8f0 !important; border-radius: 8px; overflow: hidden;">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2.5">
            <div class="stat-icon stat-icon-amber" style="width: 36px; height: 36px; font-size: 18px; border-radius: 8px;">
                <i class="bi bi-trophy-fill text-warning"></i>
            </div>
            <div>
                <h3 class="h6 mb-0 fw-semibold text-dark">Class Academic Performance &amp; Analytics</h3>
                <p class="text-muted small mb-0">Grade distribution, period leaders, and top student rankings for active classes.</p>
            </div>
        </div>
        <form method="GET" action="<?= url('/faculty/dashboard') ?>" class="d-flex align-items-center gap-2 m-0">
            <?php if (!empty($selectedSemester)): ?>
                <input type="hidden" name="semester" value="<?= htmlspecialchars($selectedSemester) ?>">
            <?php endif; ?>
            <select name="subject_id" class="form-select form-select-sm" style="min-width: 220px;" onchange="this.form.submit()">
                <?php if (empty($assignedSubjects)): ?>
                    <option value="">No subjects assigned</option>
                <?php else: ?>
                    <?php foreach ($assignedSubjects as $sub): ?>
                        <option value="<?= $sub['id'] ?>" <?= ($selectedSubjectId == $sub['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sub['code'] . ' - ' . $sub['name']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <?php if (!empty($sets)): ?>
                <select name="set_id" class="form-select form-select-sm" style="min-width: 140px;" onchange="this.form.submit()">
                    <option value="">All sections</option>
                    <?php foreach ($sets as $set): ?>
                        <option value="<?= $set['id'] ?>" <?= ($selectedSetId == $set['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($set['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </form>
    </div>

    <?php if (!$rankings || empty($rankings['top_performers'])): ?>
        <div class="p-4 text-center text-muted">
            <i class="bi bi-bar-chart fs-1 text-secondary opacity-50 mb-2 d-block"></i>
            <h6 class="fw-semibold text-dark mb-1">No Grade Analytics Available</h6>
            <p class="small text-muted mb-0">Grades have not been entered or finalized yet for the selected subject and section.</p>
        </div>
    <?php else: ?>
        <!-- Analytics KPI Row -->
        <div class="card-body bg-light border-bottom py-3">
            <div class="row g-3 text-center">
                <div class="col-6 col-md-3">
                    <div class="p-2.5 bg-white rounded border" style="border-color: #e2e8f0 !important;">
                        <div class="text-secondary small fw-medium" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">Class Average</div>
                        <div class="fs-4 fw-bold text-dark tabular-nums"><?= number_format($rankings['stats']['average'] ?? 0, 1) ?>%</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-2.5 bg-white rounded border" style="border-color: #e2e8f0 !important;">
                        <div class="text-secondary small fw-medium" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">Passing Rate</div>
                        <div class="fs-4 fw-bold text-success tabular-nums"><?= number_format($rankings['stats']['passing_rate'] ?? 0, 1) ?>%</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-2.5 bg-white rounded border" style="border-color: #e2e8f0 !important;">
                        <div class="text-secondary small fw-medium" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">Passed Students</div>
                        <div class="fs-4 fw-bold text-primary tabular-nums"><?= (int) ($rankings['stats']['passed'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-2.5 bg-white rounded border" style="border-color: #e2e8f0 !important;">
                        <div class="text-secondary small fw-medium" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">Needs Attention</div>
                        <div class="fs-4 fw-bold <?= ($rankings['stats']['failed'] ?? 0) > 0 ? 'text-danger' : 'text-secondary' ?> tabular-nums">
                            <?= (int) ($rankings['stats']['failed'] ?? 0) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top 3 Podium Cards -->
        <?php if (!empty($rankings['top_performers'])): ?>
            <div class="p-3 bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center mb-2.5">
                    <span class="text-uppercase fw-bold text-secondary" style="font-size: 11px; letter-spacing: 0.06em;">
                        <i class="bi bi-award text-warning me-1"></i> Academic Podium &mdash; Top Performers
                    </span>
                    <span class="badge bg-light text-secondary border font-monospace small">Term Rank</span>
                </div>
                <div class="podium-container">
                    <?php 
                    $top3 = array_slice($rankings['top_performers'], 0, 3);
                    foreach ($top3 as $idx => $pStudent): 
                        $rankNum = $idx + 1;
                        $medalClass = match($rankNum) { 1 => 'gold', 2 => 'silver', default => 'bronze' };
                        $initials = strtoupper(substr($pStudent['full_name'] ?? 'S', 0, 1));
                    ?>
                        <div class="podium-card rank-<?= $rankNum ?>">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="podium-medal <?= $medalClass ?>">#<?= $rankNum ?></div>
                                <span class="fs-5 fw-bold font-monospace text-primary tabular-nums"><?= number_format($pStudent['final_grade'], 1) ?>%</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-monogram"><?= $initials ?></div>
                                <div class="overflow-hidden">
                                    <div class="fw-bold text-dark text-truncate" style="font-size: 13px;"><?= htmlspecialchars($pStudent['full_name']) ?></div>
                                    <div class="text-muted font-monospace text-truncate" style="font-size: 11px;">
                                        <?= htmlspecialchars($pStudent['student_number']) ?> &bull; <?= htmlspecialchars($pStudent['set_name'] ?? 'Section') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="row g-0">
            <!-- Left: Top Performers Tabs & Table -->
            <div class="col-lg-7 border-end">
                <div class="p-2.5 bg-light border-bottom d-flex justify-content-between align-items-center">
                    <ul class="nav nav-pills nav-fill gap-1" id="rankingTabs" role="tablist" style="font-size: 12px;">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-1 px-3 fw-semibold" id="tab-overall" data-bs-toggle="pill" data-bs-target="#pane-overall" type="button" role="tab">Overall</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-3 fw-semibold" id="tab-prelim" data-bs-toggle="pill" data-bs-target="#pane-prelim" type="button" role="tab">Prelim</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-3 fw-semibold" id="tab-midterm" data-bs-toggle="pill" data-bs-target="#pane-midterm" type="button" role="tab">Midterm</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-3 fw-semibold" id="tab-semifinal" data-bs-toggle="pill" data-bs-target="#pane-semifinal" type="button" role="tab">Semi-Final</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-3 fw-semibold" id="tab-final" data-bs-toggle="pill" data-bs-target="#pane-final" type="button" role="tab">Final</button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content" id="rankingTabsContent">
                    <!-- Overall Pane -->
                    <div class="tab-pane fade show active" id="pane-overall" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-white border-bottom">
                                    <tr>
                                        <th class="py-2 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px; width: 60px;">Rank</th>
                                        <th class="py-2 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Student</th>
                                        <th class="py-2 px-3 text-secondary text-uppercase fw-semibold text-center" style="font-size: 11px;">Section</th>
                                        <th class="py-2 px-3 text-secondary text-uppercase fw-semibold text-end" style="font-size: 11px;">Final Grade</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <?php foreach ($rankings['top_performers'] as $tp): ?>
                                        <tr>
                                            <td class="px-3">
                                                <?php if ($tp['rank'] === 1): ?>
                                                    <span class="badge rounded-pill bg-warning text-dark px-2 py-1"><i class="bi bi-award-fill me-1"></i>#1</span>
                                                <?php elseif ($tp['rank'] === 2): ?>
                                                    <span class="badge rounded-pill bg-secondary text-white px-2 py-1">#2</span>
                                                <?php elseif ($tp['rank'] === 3): ?>
                                                    <span class="badge rounded-pill text-white px-2 py-1" style="background-color: #cd7f32;">#3</span>
                                                <?php else: ?>
                                                    <span class="text-secondary fw-semibold ps-2 font-monospace">#<?= $tp['rank'] ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-3">
                                                <div class="fw-semibold text-dark"><?= htmlspecialchars($tp['full_name']) ?></div>
                                                <div class="text-muted small font-monospace"><?= htmlspecialchars($tp['student_number']) ?></div>
                                            </td>
                                            <td class="px-3 text-center small text-muted font-monospace">
                                                <?= htmlspecialchars($tp['set_name'] ?? '—') ?>
                                            </td>
                                            <td class="px-3 text-end">
                                                <span class="fw-bold font-monospace tabular-nums <?= $tp['final_grade'] >= 75 ? 'text-success' : 'text-danger' ?>">
                                                    <?= number_format($tp['final_grade'], 1) ?>%
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Period Panes -->
                    <?php 
                    $pKeys = ['prelim' => 'prelim', 'midterm' => 'midterm', 'semifinal' => 'semi_final', 'final' => 'final'];
                    foreach ($pKeys as $paneId => $pk):
                        $pLeaders = $rankings['period_leaders'][$pk] ?? [];
                    ?>
                        <div class="tab-pane fade" id="pane-<?= $paneId ?>" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-white border-bottom">
                                        <tr>
                                            <th class="py-2 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px; width: 60px;">Rank</th>
                                            <th class="py-2 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Student</th>
                                            <th class="py-2 px-3 text-secondary text-uppercase fw-semibold text-center" style="font-size: 11px;">Section</th>
                                            <th class="py-2 px-3 text-secondary text-uppercase fw-semibold text-end" style="font-size: 11px;">Score</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        <?php if (empty($pLeaders)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted small">No scores recorded for this period.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($pLeaders as $pl): ?>
                                                <tr>
                                                    <td class="px-3 font-monospace fw-semibold text-secondary">#<?= $pl['rank'] ?></td>
                                                    <td class="px-3">
                                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($pl['full_name']) ?></div>
                                                        <div class="text-muted small font-monospace"><?= htmlspecialchars($pl['student_number'] ?? '') ?></div>
                                                    </td>
                                                    <td class="px-3 text-center small text-muted font-monospace"><?= htmlspecialchars($pl['set_name'] ?? '—') ?></td>
                                                    <td class="px-3 text-end font-monospace fw-bold text-primary tabular-nums"><?= number_format($pl['score'], 1) ?>%</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right: Grade Distribution Histogram -->
            <div class="col-lg-5 p-4 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-semibold text-dark small text-uppercase mb-0" style="letter-spacing: 0.05em;">Grade Distribution</h6>
                    <span class="text-muted small font-monospace">Graded: <?= array_sum(array_map(fn($b) => is_array($b) ? ($b['count'] ?? 0) : (int)$b, $rankings['distribution'] ?? [])) ?></span>
                </div>
                <?php 
                $dist = $rankings['distribution'] ?? [];
                $totalGraded = array_sum(array_map(fn($b) => is_array($b) ? ($b['count'] ?? 0) : (int)$b, $dist));
                $bars = [
                    '90-100' => ['label' => '90–100% (Outstanding)', 'color' => '#10b981'],
                    '80-89'  => ['label' => '80–89% (Good)', 'color' => '#3b82f6'],
                    '70-79'  => ['label' => '70–79% (Passing)', 'color' => '#f59e0b'],
                    '60-69'  => ['label' => '60–69% (Conditional)', 'color' => '#f97316'],
                    '50-59'  => ['label' => '50–59% (Failing)', 'color' => '#ef4444'],
                    '0-49'   => ['label' => '0–49% (Critical)', 'color' => '#b91c1c'],
                ];
                ?>
                <div class="d-flex flex-column gap-2.5">
                    <?php foreach ($bars as $rangeKey => $barMeta): 
                        $raw = $dist[$rangeKey] ?? 0;
                        $count = is_array($raw) ? ($raw['count'] ?? 0) : (int)$raw;
                        $pct = $totalGraded > 0 ? round(($count / $totalGraded) * 100, 1) : 0;
                    ?>
                        <div>
                            <div class="d-flex justify-content-between small text-secondary mb-1" style="font-size: 11.5px;">
                                <span class="fw-medium"><?= $barMeta['label'] ?></span>
                                <span class="fw-semibold text-dark tabular-nums font-monospace"><?= $count ?> <span class="text-muted fw-normal">(<?= $pct ?>%)</span></span>
                            </div>
                            <div class="progress" style="height: 6px; background-color: #f1f5f9; border-radius: 4px;">
                                <div class="progress-bar rounded" style="width: <?= $pct ?>%; background-color: <?= $barMeta['color'] ?>;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-4 pt-3 border-top text-muted small" style="font-size: 11.5px;">
                    <i class="bi bi-info-circle me-1"></i>
                    Distribution brackets conform to official Golden West Colleges grading evaluation standards.
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/dashboard.php';
?>
