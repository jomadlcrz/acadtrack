<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'GWC Grading System') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <?php if (!empty($additionalCss)): ?>
        <?php foreach ($additionalCss as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <?php include __DIR__ . '/../components/navbar.php'; ?>

    <div class="app-container">
        <?php include __DIR__ . '/../components/sidebar.php'; ?>

        <main class="main-content">
            <?php include __DIR__ . '/../components/alert.php'; ?>

            <div class="content-wrapper">
                <?= $content ?>
            </div>
        </main>
    </div>

    <?php include __DIR__ . '/../components/modal.php'; ?>
    <?php include __DIR__ . '/../components/toast.php'; ?>

    <script src="/assets/js/app.js"></script>
    <?php if (!empty($additionalJs)): ?>
        <?php foreach ($additionalJs as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
