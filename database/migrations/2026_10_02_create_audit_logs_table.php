<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up(): void
    {
        // Append-only staff activity log: who did what, to which record, and when.
        // The actor's name/email/role are copied in so entries stay readable if the account is later removed.
        Capsule::connection()->getPdo()->exec("
            CREATE TABLE IF NOT EXISTS `audit_logs` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `actor_id` INT NULL,
                `actor_name` VARCHAR(120) NOT NULL,
                `actor_email` VARCHAR(255) NOT NULL DEFAULT '',
                `actor_role` VARCHAR(30) NOT NULL DEFAULT '',
                `category` VARCHAR(30) NOT NULL,
                `action` VARCHAR(80) NOT NULL,
                `target_type` VARCHAR(40) NOT NULL DEFAULT '',
                `target_id` VARCHAR(64) NOT NULL DEFAULT '',
                `target_label` VARCHAR(120) NOT NULL DEFAULT '',
                `summary` VARCHAR(500) NOT NULL,
                `ip_address` VARCHAR(45) NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_audit_created` (`created_at`),
                INDEX `idx_audit_category` (`category`, `created_at`),
                INDEX `idx_audit_actor` (`actor_id`, `created_at`),
                INDEX `idx_audit_target` (`target_type`, `target_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(): void
    {
        Capsule::connection()->getPdo()->exec("DROP TABLE IF EXISTS `audit_logs`;");
    }
};
