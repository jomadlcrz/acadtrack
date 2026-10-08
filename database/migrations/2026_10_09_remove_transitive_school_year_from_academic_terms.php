<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    /**
     * Remove transitive school_year column from academic_terms to achieve 3NF compliance.
     * academic_term_id -> academic_year_id -> school_year
     */
    public function up(): void
    {
        $pdo = Capsule::connection()->getPdo();

        // 1. Ensure academic_year_id is properly populated for any terms before dropping school_year
        try {
            $pdo->exec("
                UPDATE academic_terms at
                JOIN academic_years ay ON at.school_year = ay.school_year
                SET at.academic_year_id = ay.id
                WHERE at.academic_year_id IS NULL OR at.academic_year_id = 0
            ");
        } catch (\Throwable) {
            // Ignore if columns or matching rows are already aligned
        }

        // 2. Drop the redundant transitive column `school_year` if present
        $checkCol = $pdo->query("SHOW COLUMNS FROM `academic_terms` LIKE 'school_year'");
        if ($checkCol && $checkCol->rowCount() > 0) {
            $pdo->exec("ALTER TABLE `academic_terms` DROP COLUMN `school_year`");
        }
    }

    public function down(): void
    {
        $pdo = Capsule::connection()->getPdo();

        $checkCol = $pdo->query("SHOW COLUMNS FROM `academic_terms` LIKE 'school_year'");
        if ($checkCol && $checkCol->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `academic_terms` ADD COLUMN `school_year` VARCHAR(20) NULL AFTER `academic_year_id`");
            $pdo->exec("
                UPDATE academic_terms at
                JOIN academic_years ay ON at.academic_year_id = ay.id
                SET at.school_year = ay.school_year
            ");
        }
    }
};
