<?php
$pag = $pagination ?? $users ?? $records ?? [];
$total = (int) ($pag['total'] ?? 0);
$perPage = (int) ($pag['per_page'] ?? $pag['perPage'] ?? 20);
$page = max(1, (int) ($pag['page'] ?? $pag['current_page'] ?? 1));
$lastPage = max(1, (int) ($pag['last_page'] ?? $pag['lastPage'] ?? ceil($total / max(1, $perPage))));

$startRecord = $total > 0 ? (($page - 1) * $perPage) + 1 : 0;
$endRecord = min($total, $page * $perPage);

$pageParam = $pageParam ?? 'page';
$makePageUrl = function (int $targetPage) use ($pageParam): string {
    $params = $_GET;
    $params[$pageParam] = $targetPage;
    return '?' . http_build_query($params);
};
?>

<?php if ($total > 0): ?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 py-3 border-top bg-white px-3 mt-2 rounded-bottom">
    <div class="text-muted small">
        <?php if ($lastPage > 1): ?>
            Showing <span class="fw-semibold text-dark"><?= $startRecord ?></span> to <span class="fw-semibold text-dark"><?= $endRecord ?></span> of <span class="fw-semibold text-dark"><?= $total ?></span> records (Page <?= $page ?> of <?= $lastPage ?>)
        <?php else: ?>
            Showing all <span class="fw-semibold text-dark"><?= $total ?></span> records
        <?php endif; ?>
    </div>

    <?php if ($lastPage > 1): ?>
    <nav aria-label="Table navigation">
        <ul class="pagination pagination-sm mb-0">
            <!-- First Page -->
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a href="<?= $makePageUrl(1) ?>" class="page-link" aria-label="First page" title="First page">
                        <i class="bi bi-chevron-double-left"></i>
                    </a>
                </li>
                <li class="page-item">
                    <a href="<?= $makePageUrl($page - 1) ?>" class="page-link" aria-label="Previous page">
                        <i class="bi bi-chevron-left me-1"></i> Prev
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link"><i class="bi bi-chevron-double-left"></i></span>
                </li>
                <li class="page-item disabled">
                    <span class="page-link"><i class="bi bi-chevron-left me-1"></i> Prev</span>
                </li>
            <?php endif; ?>

            <!-- Page Number Links -->
            <?php
            $startPage = max(1, $page - 2);
            $endPage = min($lastPage, $page + 2);

            if ($startPage > 1) {
                echo '<li class="page-item"><a href="' . $makePageUrl(1) . '" class="page-link">1</a></li>';
                if ($startPage > 2) {
                    echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                }
            }

            for ($i = $startPage; $i <= $endPage; $i++):
            ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a href="<?= $makePageUrl($i) ?>" class="page-link"><?= $i ?></a>
                </li>
            <?php
            endfor;

            if ($endPage < $lastPage) {
                if ($endPage < $lastPage - 1) {
                    echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                }
                echo '<li class="page-item"><a href="' . $makePageUrl($lastPage) . '" class="page-link">' . $lastPage . '</a></li>';
            }
            ?>

            <!-- Next Page -->
            <?php if ($page < $lastPage): ?>
                <li class="page-item">
                    <a href="<?= $makePageUrl($page + 1) ?>" class="page-link" aria-label="Next page">
                        Next <i class="bi bi-chevron-right ms-1"></i>
                    </a>
                </li>
                <li class="page-item">
                    <a href="<?= $makePageUrl($lastPage) ?>" class="page-link" aria-label="Last page" title="Last page">
                        <i class="bi bi-chevron-double-right"></i>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link">Next <i class="bi bi-chevron-right ms-1"></i></span>
                </li>
                <li class="page-item disabled">
                    <span class="page-link"><i class="bi bi-chevron-double-right"></i></span>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>
<?php endif; ?>
