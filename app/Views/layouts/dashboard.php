<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> &mdash; Acadtrack</title>
    <link rel="icon" type="image/x-icon" href="<?= url('favicon.ico') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= url('favicon.ico') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/pages/dashboard.css') ?>">
</head>
<body>
    <?php include __DIR__ . '/../components/navbar.php'; ?>

    <div class="app-container">
        <?php include __DIR__ . '/../components/sidebar.php'; ?>

        <main class="main-content">
            <?php include __DIR__ . '/../components/alert.php'; ?>

            <div class="dashboard-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h1><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
                    <?php if (!empty($subtitle)): ?>
                        <p><?= htmlspecialchars($subtitle) ?></p>
                    <?php endif; ?>
                </div>
                <?php if (!empty($headerActions)): ?>
                    <div class="dashboard-actions d-flex align-items-center gap-2">
                        <?= $headerActions ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="dashboard-content">
                <?= $content ?>
            </div>
        </main>
    </div>

    <script src="<?= asset('vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
