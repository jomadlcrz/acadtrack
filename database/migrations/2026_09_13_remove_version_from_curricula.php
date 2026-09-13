<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up(): void
    {
        $pdo = Capsule::connection()->getPdo();

        if (Capsule::schema()->hasTable('curricula')) {
            // Drop composite unique constraint if present
            $stmt = $pdo->query("
                SELECT INDEX_NAME 
                FROM information_schema.STATISTICS 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'curricula' 
                  AND INDEX_NAME IN ('curricula_program_id_version_unique', 'unique_curriculum')
                LIMIT 1
            ");
            $indexRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($indexRow) {
                $idxName = $indexRow['INDEX_NAME'];
                $pdo->exec("ALTER TABLE `curricula` DROP INDEX `{$idxName}`");
            }

            // Ensure unique key on program_id exists
            $stmtProgIdx = $pdo->query("
                SELECT INDEX_NAME 
                FROM information_schema.STATISTICS 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'curricula' 
                  AND INDEX_NAME = 'curricula_program_id_unique'
                LIMIT 1
            ");
            if (!$stmtProgIdx->fetch()) {
                $pdo->exec("ALTER TABLE `curricula` ADD UNIQUE KEY `curricula_program_id_unique` (`program_id`)");
            }

            // Drop version column if present
            if (Capsule::schema()->hasColumn('curricula', 'version')) {
                $pdo->exec("ALTER TABLE `curricula` DROP COLUMN `version`");
            }
        }
    }

    public function down(): void
    {
        $pdo = Capsule::connection()->getPdo();

        if (Capsule::schema()->hasTable('curricula')) {
            if (!Capsule::schema()->hasColumn('curricula', 'version')) {
                $pdo->exec("ALTER TABLE `curricula` ADD COLUMN `version` VARCHAR(50) NOT NULL DEFAULT '2026-2027' AFTER `program_id`");
            }

            $stmtProgIdx = $pdo->query("
                SELECT INDEX_NAME 
                FROM information_schema.STATISTICS 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'curricula' 
                  AND INDEX_NAME = 'curricula_program_id_unique'
                LIMIT 1
            ");
            if ($stmtProgIdx->fetch()) {
                $pdo->exec("ALTER TABLE `curricula` DROP INDEX `curricula_program_id_unique`");
            }

            $stmtVerIdx = $pdo->query("
                SELECT INDEX_NAME 
                FROM information_schema.STATISTICS 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'curricula' 
                  AND INDEX_NAME = 'curricula_program_id_version_unique'
                LIMIT 1
            ");
            if (!$stmtVerIdx->fetch()) {
                $pdo->exec("ALTER TABLE `curricula` ADD UNIQUE KEY `curricula_program_id_version_unique` (`program_id`, `version`)");
            }
        }
    }
};
