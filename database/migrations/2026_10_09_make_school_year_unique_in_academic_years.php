<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up(): void
    {
        $pdo = Capsule::connection()->getPdo();

        // 1. Deduplicate any existing identical school_year entries if present
        $pdo->exec("
            DELETE ay1 FROM academic_years ay1
            INNER JOIN academic_years ay2 
            WHERE ay1.id > ay2.id AND ay1.school_year = ay2.school_year
        ");

        // 2. Drop existing non-unique index
        try {
            $pdo->exec("ALTER TABLE `academic_years` DROP INDEX `idx_ay_school_year`");
        } catch (\Throwable) {
        }

        // 3. Create UNIQUE index
        try {
            $pdo->exec("ALTER TABLE `academic_years` ADD UNIQUE INDEX `idx_ay_school_year` (`school_year`)");
        } catch (\Throwable) {
        }
    }

    public function down(): void
    {
        $pdo = Capsule::connection()->getPdo();
        try {
            $pdo->exec("ALTER TABLE `academic_years` DROP INDEX `idx_ay_school_year`");
            $pdo->exec("ALTER TABLE `academic_years` ADD INDEX `idx_ay_school_year` (`school_year`)");
        } catch (\Throwable) {
        }
    }
};
