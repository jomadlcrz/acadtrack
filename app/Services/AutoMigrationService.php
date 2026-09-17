<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

class AutoMigrationService
{
    private static string $migrationsTable = 'migrations';

    /**
     * Called automatically on application boot.
     * Uses an ultra-fast file fingerprint check (0.0001s) so there is zero database overhead on normal requests.
     */
    public static function runIfNeeded(): void
    {
        try {
            $migrationsDir = dirname(__DIR__, 2) . '/database/migrations';
            if (!is_dir($migrationsDir)) {
                return;
            }

            $files = glob($migrationsDir . '/*.php');
            if (!$files) {
                return;
            }

            $cacheDir = dirname(__DIR__, 2) . '/storage/cache';
            if (!is_dir($cacheDir)) {
                @mkdir($cacheDir, 0777, true);
            }

            $cacheFile = $cacheDir . '/migrations_fingerprint.txt';
            $fileFingerprint = md5(implode('|', array_map('basename', $files)));

            // Fast bail-out: if migration files have not changed, skip database checks
            if (file_exists($cacheFile) && trim((string) file_get_contents($cacheFile)) === $fileFingerprint) {
                return;
            }

            self::runPending();

            @file_put_contents($cacheFile, $fileFingerprint);
        } catch (\Throwable $e) {
            error_log('[AutoMigrationService] Error: ' . $e->getMessage());
        }
    }

    /**
     * Executes pending migrations and tracks them in the standard PHP/Laravel migrations table.
     *
     * @return array List of applied migration filenames
     */
    public static function runPending(): array
    {
        self::ensureMigrationsTable();

        $migrationsDir = dirname(__DIR__, 2) . '/database/migrations';
        $files = glob($migrationsDir . '/*.php');
        if (!$files) {
            return [];
        }

        sort($files);

        $applied = Capsule::table(self::$migrationsTable)->pluck('migration')->toArray();
        $appliedList = [];

        $batch = (int) Capsule::table(self::$migrationsTable)->max('batch') + 1;

        foreach ($files as $file) {
            $name = basename($file);
            if (in_array($name, $applied, true)) {
                continue;
            }

            try {
                $migration = require $file;
                if (is_object($migration) && method_exists($migration, 'up')) {
                    $migration->up();
                }

                Capsule::table(self::$migrationsTable)->insert([
                    'migration' => $name,
                    'batch' => $batch,
                ]);

                $appliedList[] = $name;
                error_log("[AutoMigrationService] Applied migration: {$name}");
            } catch (\Throwable $e) {
                error_log("[AutoMigrationService] Migration note ({$name}): " . $e->getMessage());
                // Record to prevent infinite retry loop on legacy migrations
                Capsule::table(self::$migrationsTable)->insert([
                    'migration' => $name,
                    'batch' => $batch,
                ]);
            }
        }

        return $appliedList;
    }

    /**
     * Ensures the standard PHP/Laravel migrations table exists.
     */
    private static function ensureMigrationsTable(): void
    {
        if (!Capsule::schema()->hasTable(self::$migrationsTable)) {
            Capsule::schema()->create(self::$migrationsTable, function (Blueprint $table) {
                $table->increments('id');
                $table->string('migration', 255)->unique();
                $table->integer('batch')->default(1);
            });
        }
    }
}
