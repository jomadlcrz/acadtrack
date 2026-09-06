<?php
$total = $pagination['total'] ?? 0;
$page = $pagination['page'] ?? 1;
$lastPage = $pagination['lastPage'] ?? 1;
?>

<?php if ($lastPage > 1): ?>
<nav class="pagination">
    <span class="pagination-info">
        Showing page <?= $page ?> of <?= $lastPage ?> (<?= $total ?> total)
    </span>
    <div class="pagination-links">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>" class="pagination-link">&laquo; Previous</a>
        <?php endif; ?>

        <?php for ($i = max(1, $page - 2); $i <= min($lastPage, $page + 2); $i++): ?>
            <a href="?page=<?= $i ?>" class="pagination-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($page < $lastPage): ?>
            <a href="?page=<?= $page + 1 ?>" class="pagination-link">Next &raquo;</a>
        <?php endif; ?>
    </div>
</nav>
<?php endif; ?>
