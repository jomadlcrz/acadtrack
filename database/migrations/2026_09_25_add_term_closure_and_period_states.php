<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up(): void
    {
        $pdo = Capsule::connection()->getPdo();

        // 1. Add closure columns to academic_terms if not present
        $cols = $pdo->query("SHOW COLUMNS FROM `academic_terms` LIKE 'is_closed'")->fetchAll();
        if (empty($cols)) {
            $pdo->exec("
                ALTER TABLE `academic_terms`
                ADD COLUMN `is_closed` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_active`,
                ADD COLUMN `closed_at` TIMESTAMP NULL DEFAULT NULL AFTER `is_closed`,
                ADD COLUMN `closed_by` INT NULL DEFAULT NULL AFTER `closed_at`,
                ADD COLUMN `closure_reason` VARCHAR(255) NULL DEFAULT NULL AFTER `closed_by`,
                ADD CONSTRAINT `fk_academic_terms_closed_by` FOREIGN KEY (`closed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
                ADD INDEX `idx_terms_closed` (`is_closed`);
            ");
        }

        // 2. Add closure columns to grading_periods if not present
        $gpCols = $pdo->query("SHOW COLUMNS FROM `grading_periods` LIKE 'is_closed'")->fetchAll();
        if (empty($gpCols)) {
            $pdo->exec("
                ALTER TABLE `grading_periods`
                ADD COLUMN `is_closed` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_current`,
                ADD COLUMN `closed_at` TIMESTAMP NULL DEFAULT NULL AFTER `is_closed`,
                ADD COLUMN `closure_reason` VARCHAR(255) NULL DEFAULT NULL AFTER `closed_at`,
                ADD INDEX `idx_gp_closed` (`is_closed`);
            ");
        }
    }

    public function down(): void
    {
        $pdo = Capsule::connection()->getPdo();

        $cols = $pdo->query("SHOW COLUMNS FROM `academic_terms` LIKE 'is_closed'")->fetchAll();
        if (!empty($cols)) {
            $pdo->exec("
                ALTER TABLE `academic_terms`
                DROP FOREIGN KEY `fk_academic_terms_closed_by`,
                DROP COLUMN `closure_reason`,
                DROP COLUMN `closed_by`,
                DROP COLUMN `closed_at`,
                DROP COLUMN `is_closed`;
            ");
        }

        $gpCols = $pdo->query("SHOW COLUMNS FROM `grading_periods` LIKE 'is_closed'")->fetchAll();
        if (!empty($gpCols)) {
            $pdo->exec("
                ALTER TABLE `grading_periods`
                DROP COLUMN `closure_reason`,
                DROP COLUMN `closed_at`,
                DROP COLUMN `is_closed`;
            ");
        }
    }
};
