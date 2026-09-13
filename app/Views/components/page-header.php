<?php
/**
 * Acadtrack UI Component: Page Header
 * 
 * Variables:
 * @var string|null $title      Page title heading
 * @var string|null $subtitle   Optional explanatory subtext
 * @var string|null $actions    Optional action buttons HTML string
 * @var string|null $badge      Optional status or category badge HTML string
 */
$title = $title ?? $pageTitle ?? 'Dashboard';
$subtitle = $subtitle ?? null;
$actions = $actions ?? $headerActions ?? null;
$badge = $badge ?? null;
?>

<div class="page-header">
    <div class="page-header-content">
        <div class="d-flex align-items-center gap-2 mb-1">
            <h1 class="page-header-title"><?= htmlspecialchars($title) ?></h1>
            <?php if (!empty($badge)): ?>
                <?= $badge ?>
            <?php endif; ?>
        </div>
        <?php if (!empty($subtitle)): ?>
            <p class="page-header-subtitle"><?= htmlspecialchars($subtitle) ?></p>
        <?php endif; ?>
    </div>
    <?php if (!empty($actions)): ?>
        <div class="page-header-actions">
            <?= $actions ?>
        </div>
    <?php endif; ?>
</div>
