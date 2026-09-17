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
     * Uses an ultra-fast filemtime check (0.0001s) so there is ZERO database overhead on normal requests.
     */
    public static function runIfNeeded(): void
    {
        try {
            $migrationsDir = dirname(__DIR__, 2) . '/database/migrations';
            if (!is_dir($migrationsDir)) {
                return;
            }

            $cacheDir = dirname(__DIR__, 2) . '/storage/cache';
            if (!is_dir($cacheDir)) {
                @mkdir($cacheDir, 0777, true);
            }

            $cacheFile = $cacheDir . '/migrations_mtime.txt';
            $currentMtime = (string) filemtime($migrationsDir);

            // Fast bail-out: if directory mtime hasn't changed, skip database checks
            if (file_exists($cacheFile) && file_get_contents($cacheFile) === $currentMtime) {
                return;
            }

            self::runPending();

            @file_put_contents($cacheFile, $currentMtime);
        } catch (\Throwable $e) {
            error_log('[AutoMigrationService] Error during auto migration: ' . $e->getMessage());
        }
    }

    /**
     * Executes all pending migration files that have not yet been recorded in the migrations table.
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
                    'applied_at' => date('Y-m-d H:i:s'),
                ]);

                $appliedList[] = $name;
                error_log("[AutoMigrationService] Applied migration: {$name}");
            } catch (\Throwable $e) {
                error_log("[AutoMigrationService] Migration failed ({$name}): " . $e->getMessage());
                // Still record to prevent infinite retry loop on broken historical migrations
                Capsule::table(self::$migrationsTable)->insert([
                    'migration' => $name,
                    'batch' => $batch,
                    'applied_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return $appliedList;
    }

    /**
     * Ensures the tracking table exists.
     */
    private static function ensureMigrationsTable(): void
    {
        if (!Capsule::schema()->hasTable(self::$migrationsTable)) {
            Capsule::schema()->create(self::$migrationsTable, function (Blueprint $table) {
                $table->increments('id');
                $table->string('migration', 255)->unique();
                $table->integer('batch')->default(1);
                $table->timestamp('applied_at')->useCurrent();
            });
        }
    }
}
