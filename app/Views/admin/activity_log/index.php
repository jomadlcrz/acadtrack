<?php
use App\Services\ActivityLogService;

$pageTitle = 'Activity Log';
$subtitle = 'A permanent record of who did what, to which record, and when. Entries cannot be edited or deleted.';
$total = (int) ($logs['total'] ?? 0);
$tabUrl = static function (string $category) use ($filters): string {
    $params = array_filter(['search' => $filters['search'], 'start_date' => $filters['start_date'], 'end_date' => $filters['end_date']], static fn($v) => $v !== '');
    if ($category !== '') {
        $params = ['category' => $category] + $params;
    }
    return url('/admin/activity-log') . ($params ? '?' . http_build_query($params) : '');
};
ob_start();
?>

<div class="mb-3">
    <ul class="nav nav-pills p-1 bg-light rounded-3 d-inline-flex flex-wrap border" style="border-color: #e2e8f0 !important;">
        <li class="nav-item">
            <a class="nav-link py-2 px-3.5 fw-medium rounded-2 <?= $filters['category'] === '' ? 'active shadow-sm' : 'text-secondary' ?>" href="<?= $tabUrl('') ?>">All activity</a>
        </li>
        <?php foreach (ActivityLogService::CATEGORY_LABELS as $key => $label): ?>
            <li class="nav-item">
                <a class="nav-link py-2 px-3.5 fw-medium rounded-2 <?= $filters['category'] === $key ? 'active shadow-sm' : 'text-secondary' ?>" href="<?= $tabUrl($key) ?>"><?= htmlspecialchars($label) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<form method="GET" action="<?= url('/admin/activity-log') ?>" class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between mb-4">
    <?php if ($filters['category'] !== ''): ?>
        <input type="hidden" name="category" value="<?= htmlspecialchars($filters['category']) ?>">
    <?php endif; ?>
    <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
        <div class="position-relative flex-grow-1" style="min-width: 220px; max-width: 360px;">
            <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted" style="left: 14px;"></i>
            <input type="text" name="search" class="form-control ps-5" placeholder="Search by person, action, or record..."
                   value="<?= htmlspecialchars($filters['search']) ?>" autocomplete="off">
        </div>
        <input type="date" name="start_date" class="form-control w-auto" value="<?= htmlspecialchars($filters['start_date']) ?>" aria-label="From date" onchange="this.form.submit()">
        <span class="text-muted small">to</span>
        <input type="date" name="end_date" class="form-control w-auto" value="<?= htmlspecialchars($filters['end_date']) ?>" aria-label="To date" onchange="this.form.submit()">
        <?php if ($filters['search'] !== '' || $filters['start_date'] !== '' || $filters['end_date'] !== ''): ?>
            <a href="<?= url('/admin/activity-log') . ($filters['category'] !== '' ? '?category=' . urlencode($filters['category']) : '') ?>" class="btn btn-outline-secondary btn-sm">Clear filters</a>
        <?php endif; ?>
    </div>
    <div class="text-muted small fw-medium text-nowrap"><?= number_format($total) ?> <?= $total === 1 ? 'entry' : 'entries' ?></div>
</form>

<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 6px;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-white border-bottom">
                <tr>
                    <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="width: 170px; font-size: 11px;">When</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="width: 200px; font-size: 11px;">Who</th>
                    <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">What happened</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs['data'])): ?>
                    <tr>
                        <td colspan="3" class="p-0">
                            <?php
                            $icon = 'bi-clock-history';
                            $title = 'No activity found';
                            $message = 'No recorded activity matches the current filters.';
                            include __DIR__ . '/../../components/empty-state.php';
                            ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($logs['data'] as $log):
                    $href = ActivityLogService::targetUrl((string) $log['target_type'], (string) $log['target_id']);
                    $categoryLabel = ActivityLogService::CATEGORY_LABELS[$log['category']] ?? ucfirst((string) $log['category']);
                    ?>
                    <tr>
                        <td class="px-4 text-muted small text-nowrap"><?= htmlspecialchars(date('M j, Y g:i A', strtotime((string) $log['created_at']))) ?></td>
                        <td class="px-3">
                            <div class="fw-semibold text-dark"><?= htmlspecialchars($log['actor_name']) ?></div>
                            <?php if ($log['actor_role'] !== ''): ?>
                                <div class="small text-muted"><?= htmlspecialchars($log['actor_role']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-3">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="badge bg-light text-secondary border"><?= htmlspecialchars($categoryLabel) ?></span>
                                <span class="fw-semibold text-dark"><?= htmlspecialchars($log['action']) ?></span>
                                <?php if ($log['target_label'] !== ''): ?>
                                    <?php if ($href): ?>
                                        <a href="<?= url($href) ?>" class="small"><?= htmlspecialchars($log['target_label']) ?></a>
                                    <?php else: ?>
                                        <span class="small text-muted"><?= htmlspecialchars($log['target_label']) ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div class="small text-muted mt-1"><?= htmlspecialchars($log['summary']) ?></div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
if (!empty($logs['data'])) {
    $pagination = $logs;
    include __DIR__ . '/../../components/pagination.php';
}
?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
