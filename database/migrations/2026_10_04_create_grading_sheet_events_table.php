<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Grade review audit trail.
 *
 *  - grading_sheet_events: one row per step in a grading sheet's life (submitted, review started, approved,
 *    returned with reason, grades adjusted, finalized). Append-only, so repeated return/resubmit cycles keep
 *    their full history instead of overwriting a single remarks column.
 *  - grading_sheets.reviewed_by / reviewed_at: who opened the sheet for review (UNDER_REVIEW).
 *  - grade_history_log.changed_by / reason: who changed a mark and why.
 *
 * Safe to run more than once.
 */
return new class {
    public function up(): void
    {
        $pdo = Capsule::connection()->getPdo();

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `grading_sheet_events` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `grading_sheet_id` INT NOT NULL,
                `action` VARCHAR(30) NOT NULL,
                `from_status` VARCHAR(20) NULL,
                `to_status` VARCHAR(20) NULL,
                `actor_id` INT NULL,
                `actor_name` VARCHAR(120) NOT NULL DEFAULT '',
                `remarks` TEXT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_sheet_events_sheet` (`grading_sheet_id`, `id`),
                CONSTRAINT `fk_sheet_events_sheet` FOREIGN KEY (`grading_sheet_id`) REFERENCES `grading_sheets`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        if (empty($pdo->query("SHOW COLUMNS FROM `grading_sheets` LIKE 'reviewed_by'")->fetchAll())) {
            $pdo->exec("
                ALTER TABLE `grading_sheets`
                ADD COLUMN `reviewed_by` INT NULL DEFAULT NULL AFTER `submitted_at`,
                ADD COLUMN `reviewed_at` TIMESTAMP NULL DEFAULT NULL AFTER `reviewed_by`
            ");
        }

        if (empty($pdo->query("SHOW COLUMNS FROM `grade_history_log` LIKE 'changed_by'")->fetchAll())) {
            $pdo->exec("
                ALTER TABLE `grade_history_log`
                ADD COLUMN `changed_by` INT NULL DEFAULT NULL AFTER `action_performed`,
                ADD COLUMN `reason` VARCHAR(255) NULL DEFAULT NULL AFTER `changed_by`
            ");
        }
    }

    public function down(): void
    {
        $pdo = Capsule::connection()->getPdo();

        $pdo->exec("DROP TABLE IF EXISTS `grading_sheet_events`");

        if (!empty($pdo->query("SHOW COLUMNS FROM `grading_sheets` LIKE 'reviewed_by'")->fetchAll())) {
            $pdo->exec("ALTER TABLE `grading_sheets` DROP COLUMN `reviewed_by`, DROP COLUMN `reviewed_at`");
        }
        if (!empty($pdo->query("SHOW COLUMNS FROM `grade_history_log` LIKE 'changed_by'")->fetchAll())) {
            $pdo->exec("ALTER TABLE `grade_history_log` DROP COLUMN `changed_by`, DROP COLUMN `reason`");
        }
    }
};
