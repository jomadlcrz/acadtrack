<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Acadtrack — College of Information Technology') ?></title>
    <link rel="icon" type="image/x-icon" href="<?= url('favicon.ico') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= asset('images/favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= asset('images/favicon-16x16.png') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= asset('images/apple-touch-icon.png') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components/button.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components/input.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components/select.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components/card.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components/table.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components/modal.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components/badge.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components/navbar.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components/sidebar.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components/alert.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components/pagination.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components/empty-state.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components/page-header.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/components/filter-bar.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/layouts/app-shell.css') ?>">
    <?php if (!empty($additionalCss)): ?>
        <?php foreach ($additionalCss as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <?php include __DIR__ . '/../components/navbar.php'; ?>

    <div class="app-container">
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
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

    <script src="<?= asset('vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
    <?php if (!empty($additionalJs)): ?>
        <?php foreach ($additionalJs as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
