<?php
$pageTitle = 'Settings';
ob_start();
?>

<div class="page-header">
    <h2>System Settings</h2>
</div>

<form method="POST" action="/admin/settings" class="form">
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="grading_method">Default Grading Method</label>
        <select id="grading_method" name="grading_method">
            <option value="zero_based" <?= (($settings['grading_method'] ?? 'zero_based') === 'zero_based') ? 'selected' : '' ?>>Zero-based (0-100)</option>
            <option value="fifty_based" <?= (($settings['grading_method'] ?? '') === 'fifty_based') ? 'selected' : '' ?>>Fifty-based (50-100)</option>
        </select>
    </div>

    <div class="form-group">
        <label for="academic_year">Active Academic Year</label>
        <input type="text" id="academic_year" name="academic_year" value="<?= htmlspecialchars($term['academic_year_name'] ?? '2026-2027') ?>" placeholder="e.g., 2026-2027">
    </div>

    <button type="submit" class="btn btn-primary">Save Settings</button>
</form>

<?php
$content = ob_get_clean();
$pageTitle = 'Settings';
include __DIR__ . '/../../layouts/dashboard.php';
?>
