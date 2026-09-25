<?php

declare(strict_types=1);

/**
 * CLI Migration Runner for Acadtrack (XAMPP & Local Environment).
 *
 * Usage:
 *   php database/migrate.php           # Run all pending migrations
 *   php database/migrate.php status    # Check migration status
 */

use Dotenv\Dotenv;
use App\Core\Database;
use App\Services\AutoMigrationService;
use Illuminate\Database\Capsule\Manager as Capsule;

$baseDir = dirname(__DIR__);

// 1. Require Composer Autoloader
if (!file_exists($baseDir . '/vendor/autoload.php')) {
    fwrite(STDERR, "[ERROR] Composer autoloader not found. Please run 'composer install'.\n");
    exit(1);
}
require_once $baseDir . '/vendor/autoload.php';

// 2. Load Environment Variables
if (file_exists($baseDir . '/.env')) {
    $dotenv = Dotenv::createImmutable($baseDir);
    $dotenv->load();
}

$dbHost = $_ENV['DB_HOST'] ?? '127.0.0.1';
$dbPort = (int) ($_ENV['DB_PORT'] ?? 3306);
$dbName = $_ENV['DB_DATABASE'] ?? 'acadtrack';
$dbUser = $_ENV['DB_USERNAME'] ?? 'root';
$dbPass = (string) ($_ENV['DB_PASSWORD'] ?? '');

echo "====================================================\n";
echo "  Acadtrack Database Migration Tool (CLI)\n";
echo "====================================================\n";
echo " Host:     {$dbHost}:{$dbPort}\n";
echo " Database: {$dbName}\n";
echo " User:     {$dbUser}\n";
echo "----------------------------------------------------\n";

// 3. Connect to Database
try {
    $database = new Database($dbHost, $dbName, $dbUser, $dbPass);
    $pdo = Capsule::connection()->getPdo();
    echo "[OK] Connected to database successfully.\n";
} catch (\Throwable $e) {
    fwrite(STDERR, "[ERROR] Failed to connect to database: " . $e->getMessage() . "\n");
    exit(1);
}

// 4. Ensure migrations table exists
if (!Capsule::schema()->hasTable('migrations')) {
    Capsule::schema()->create('migrations', function ($table) {
        $table->increments('id');
        $table->string('migration', 255)->unique();
        $table->integer('batch')->default(1);
    });
    echo "[INFO] Created 'migrations' table.\n";
}

// 5. Gather Migration Files
$migrationsDir = $baseDir . '/database/migrations';
$files = glob($migrationsDir . '/*.php');
if (!$files) {
    echo "[INFO] No migration files found in database/migrations.\n";
    exit(0);
}
sort($files);

$applied = Capsule::table('migrations')->pluck('migration')->toArray();
$pending = [];

foreach ($files as $file) {
    $name = basename($file);
    if (!in_array($name, $applied, true)) {
        $pending[] = ['name' => $name, 'path' => $file];
    }
}

$command = $argv[1] ?? 'migrate';

if ($command === 'status') {
    echo "\nApplied Migrations (" . count($applied) . "):\n";
    foreach ($applied as $m) {
        echo "  [x] {$m}\n";
    }
    echo "\nPending Migrations (" . count($pending) . "):\n";
    if (empty($pending)) {
        echo "  (None - database is up to date)\n";
    } else {
        foreach ($pending as $p) {
            echo "  [ ] {$p['name']}\n";
        }
    }
    echo "\n";
    exit(0);
}

// 6. Execute Pending Migrations
if (empty($pending)) {
    echo "[OK] Nothing to migrate. All migrations are already applied.\n";
    exit(0);
}

$batch = (int) Capsule::table('migrations')->max('batch') + 1;
$successCount = 0;
$errorCount = 0;

echo "[INFO] Found " . count($pending) . " pending migration(s). Running batch {$batch}...\n\n";

foreach ($pending as $p) {
    $name = $p['name'];
    $path = $p['path'];
    echo " Migrating: {$name} ... ";

    try {
        $migration = require $path;
        if (is_object($migration) && method_exists($migration, 'up')) {
            $migration->up();
        }

        Capsule::table('migrations')->insert([
            'migration' => $name,
            'batch' => $batch,
        ]);

        echo "[DONE]\n";
        $successCount++;
    } catch (\Throwable $e) {
        echo "[FAILED]\n";
        echo "   Error: " . $e->getMessage() . "\n";
        $errorCount++;
    }
}

// 7. Update fingerprint cache
$cacheDir = $baseDir . '/storage/cache';
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0777, true);
}
$fileFingerprint = md5(implode('|', array_map('basename', $files)));
@file_put_contents($cacheDir . '/migrations_fingerprint.txt', $fileFingerprint);

echo "----------------------------------------------------\n";
echo " Result: {$successCount} applied, {$errorCount} failed.\n";
echo "====================================================\n";

exit($errorCount > 0 ? 1 : 0);
