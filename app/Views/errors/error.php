<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'System Notice') ?> - GWC Grading System</title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <style>
        .error-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: #f1f5f9;
        }
        .error-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            border-top: 5px solid #dc2626;
        }
        .error-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #fee2e2;
            color: #dc2626;
            font-size: 28px;
            margin-bottom: 20px;
        }
        .error-title {
            font-size: 22px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 12px;
        }
        .error-message {
            font-size: 15px;
            color: #475569;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .error-actions {
            display: flex;
            gap: 12px;
            margin-top: 25px;
            flex-wrap: wrap;
        }
        .debug-details {
            margin-top: 25px;
            padding: 15px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-family: monospace;
            font-size: 12px;
            color: #334155;
            word-break: break-all;
            max-height: 300px;
            overflow-y: auto;
        }
        .debug-details summary {
            font-weight: 600;
            cursor: pointer;
            color: #2563eb;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="error-wrapper">
        <div class="error-card">
            <div class="error-icon">&#9888;</div>
            <h1 class="error-title"><?= htmlspecialchars($title ?? 'Request Could Not Be Completed') ?></h1>
            <p class="error-message"><?= htmlspecialchars($message ?? 'An unexpected error occurred. Please review your input and try again.') ?></p>

            <?php if (!empty($debug) && !empty($exception)): ?>
                <details class="debug-details">
                    <summary>Technical Details (Debug Mode)</summary>
                    <p><strong>Exception:</strong> <?= htmlspecialchars(get_class($exception)) ?></p>
                    <p><strong>Message:</strong> <?= htmlspecialchars($exception->getMessage()) ?></p>
                    <p><strong>Location:</strong> <?= htmlspecialchars($exception->getFile() . ':' . $exception->getLine()) ?></p>
                    <hr style="margin: 10px 0; border: none; border-top: 1px solid #cbd5e1;">
                    <pre style="white-space: pre-wrap;"><?= htmlspecialchars($exception->getTraceAsString()) ?></pre>
                </details>
            <?php endif; ?>

            <div class="error-actions">
                <button type="button" onclick="window.history.back()" class="btn btn-primary d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i> Go back
                </button>
                <a href="<?= url('/dashboard') ?>" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                    <i class="bi bi-house"></i> Return to dashboard
                </a>
            </div>
        </div>
    </div>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>