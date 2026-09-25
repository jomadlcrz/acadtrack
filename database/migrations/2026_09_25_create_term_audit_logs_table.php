<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up(): void
    {
        $pdo = Capsule::connection()->getPdo();

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `term_audit_logs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `action` VARCHAR(64) NOT NULL,
                `sy_id` INT NULL,
                `school_year` VARCHAR(20) NULL,
                `semester_number` INT NULL,
                `performed_by` INT NULL,
                `performer_name` VARCHAR(255) NULL,
                `role` VARCHAR(64) NULL,
                `ip_address` VARCHAR(45) NULL,
                `details` TEXT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_tal_action` (`action`),
                INDEX `idx_tal_sy_id` (`sy_id`),
                INDEX `idx_tal_performed_by` (`performed_by`),
                INDEX `idx_tal_created_at` (`created_at`),
                CONSTRAINT `fk_tal_sy_id` FOREIGN KEY (`sy_id`) REFERENCES `academic_years`(`id`) ON DELETE SET NULL,
                CONSTRAINT `fk_tal_performed_by` FOREIGN KEY (`performed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(): void
    {
        $pdo = Capsule::connection()->getPdo();
        $pdo->exec("DROP TABLE IF EXISTS `term_audit_logs`;");
    }
};
