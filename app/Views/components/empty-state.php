<?php
/**
 * Acadtrack UI Component: Empty State
 * 
 * Variables:
 * @var string|null $icon       Bootstrap icon name (e.g. 'bi-inbox', 'bi-journal-x')
 * @var string|null $iconColor  Color variant: 'blue', 'green', 'amber', 'red', 'purple', or null
 * @var string|null $title      Primary empty state heading
 * @var string|null $message    Secondary descriptive text
 * @var string|null $actionHtml Optional button or link HTML
 * @var bool|null   $card       Whether to wrap in .empty-state-card container (default false)
 */
$icon = $icon ?? 'bi-inbox';
$iconColorClass = !empty($iconColor) ? 'empty-state-icon-' . htmlspecialchars($iconColor) : '';
$title = $title ?? 'No records found';
$message = $message ?? 'There are no items matching the selected criteria.';
$card = $card ?? false;
?>

<div class="empty-state <?= $card ? 'empty-state-card' : '' ?>">
    <div class="empty-state-icon <?= $iconColorClass ?>">
        <i class="bi <?= htmlspecialchars($icon) ?>"></i>
    </div>
    <h4 class="empty-state-title"><?= htmlspecialchars($title) ?></h4>
    <p class="empty-state-text"><?= htmlspecialchars($message) ?></p>
    <?php if (!empty($actionHtml)): ?>
        <div class="empty-state-actions">
            <?= $actionHtml ?>
        </div>
    <?php endif; ?>
</div>
