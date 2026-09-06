<?php if (!empty($modalId)): ?>
<div id="<?= htmlspecialchars($modalId) ?>" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="closeModal('<?= htmlspecialchars($modalId) ?>')"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3><?= htmlspecialchars($modalTitle ?? 'Modal') ?></h3>
            <button class="modal-close" onclick="closeModal('<?= htmlspecialchars($modalId) ?>')">&times;</button>
        </div>
        <div class="modal-body">
            <?= $modalContent ?? '' ?>
        </div>
    </div>
</div>
<?php endif; ?>
