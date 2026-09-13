<?php
/**
 * Acadtrack UI Component: Filter Bar
 * 
 * Variables:
 * @var string|null $searchInputId      Input ID for search field (e.g. 'tableSearch')
 * @var string|null $searchPlaceholder  Placeholder text (e.g. 'Search records...')
 * @var string|null $filtersHtml        HTML markup for dropdown filters
 * @var string|null $counterText        Display text for visible records count
 * @var string|null $counterId          DOM element ID for dynamic JS counter updates
 * @var bool|null   $card               Whether to wrap in .filter-bar-card (default false)
 * @var string|null $content            Custom arbitrary markup to inject
 */
$searchInputId = $searchInputId ?? null;
$searchPlaceholder = $searchPlaceholder ?? 'Search records...';
$filtersHtml = $filtersHtml ?? null;
$counterText = $counterText ?? null;
$counterId = $counterId ?? null;
$card = $card ?? false;
$content = $content ?? null;
?>

<div class="filter-bar <?= $card ? 'filter-bar-card' : '' ?>">
    <?php if (!empty($content)): ?>
        <?= $content ?>
    <?php else: ?>
        <div class="filter-group flex-grow-1" style="max-width: 760px;">
            <?php if (!empty($searchInputId)): ?>
                <div class="filter-search">
                    <i class="bi bi-search filter-search-icon"></i>
                    <input type="text" 
                           id="<?= htmlspecialchars($searchInputId) ?>" 
                           class="form-control form-control-sm" 
                           placeholder="<?= htmlspecialchars($searchPlaceholder) ?>" 
                           autocomplete="off">
                </div>
            <?php endif; ?>

            <?php if (!empty($filtersHtml)): ?>
                <?= $filtersHtml ?>
            <?php endif; ?>
        </div>

        <?php if (!empty($counterText) || !empty($counterId)): ?>
            <div class="filter-counter" <?= !empty($counterId) ? 'id="' . htmlspecialchars($counterId) . '"' : '' ?>>
                <?= htmlspecialchars($counterText ?? '') ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
