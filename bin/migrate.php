<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap/app.php';

echo "Running migrations...\n";

$migrationsDir = __DIR__ . '/../database/migrations';
$files = glob($migrationsDir . '/*.php');

foreach ($files as $file) {
    $migration = require $file;
    if (is_object($migration) && method_exists($migration, 'up')) {
        echo "Applying: " . basename($file) . "\n";
        $migration->up();
        echo "Applied: " . basename($file) . "\n";
    }
}

echo "Migrations completed successfully.\n";
