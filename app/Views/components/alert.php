<?php
$flashSuccess = $_SESSION['_flash']['success'] ?? null;
$flashError = $_SESSION['_flash']['error'] ?? null;
unset($_SESSION['_flash']['success'], $_SESSION['_flash']['error']);
?>

<?php if ($flashSuccess): ?>
    <div class="alert alert-success" id="flash-success">
        <?= htmlspecialchars($flashSuccess) ?>
        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
    </div>
<?php endif; ?>

<?php if ($flashError): ?>
    <div class="alert alert-danger" id="flash-error">
        <?= htmlspecialchars($flashError) ?>
        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
    </div>
<?php endif; ?>
