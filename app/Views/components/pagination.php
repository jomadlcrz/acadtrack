<?php
$total = $pagination['total'] ?? 0;
$page = $pagination['page'] ?? 1;
$lastPage = $pagination['lastPage'] ?? 1;
?>

<?php if ($lastPage > 1): ?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 py-2">
    <span class="text-muted small">
        Showing page <?= $page ?> of <?= $lastPage ?> (<?= $total ?> total records)
    </span>
    <nav aria-label="Table page navigation">
        <ul class="pagination pagination-sm mb-0">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a href="?page=<?= $page - 1 ?>" class="page-link" aria-label="Previous">
                        <span aria-hidden="true">&laquo; Previous</span>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link">&laquo; Previous</span>
                </li>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($lastPage, $page + 2); $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a href="?page=<?= $i ?>" class="page-link"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $lastPage): ?>
                <li class="page-item">
                    <a href="?page=<?= $page + 1 ?>" class="page-link" aria-label="Next">
                        <span aria-hidden="true">Next &raquo;</span>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <span class="page-link">Next &raquo;</span>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
<?php endif; ?>
