<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up(): void
    {
        $pdo = Capsule::connection()->getPdo();
        $dbName = Capsule::connection()->getDatabaseName();

        // 1. Refine `programs` table: rename `name` to `program_name`
        if (Capsule::schema()->hasTable('programs')) {
            if (Capsule::schema()->hasColumn('programs', 'name') && !Capsule::schema()->hasColumn('programs', 'program_name')) {
                // Drop index on name if exists
                try {
                    $pdo->exec("ALTER TABLE `programs` DROP INDEX `programs_name_unique`");
                } catch (\Throwable $e) {}

                $pdo->exec("ALTER TABLE `programs` CHANGE COLUMN `name` `program_name` VARCHAR(191) NOT NULL");
                
                try {
                    $pdo->exec("ALTER TABLE `programs` ADD UNIQUE KEY `programs_program_name_unique` (`program_name`)");
                } catch (\Throwable $e) {}
            }
        }

        // 2. Refine `subjects` table: rename `code` to `subject_code`, `name` to `descriptive_title`
        if (Capsule::schema()->hasTable('subjects')) {
            // Drop unique_subject index before renaming
            try {
                $pdo->exec("ALTER TABLE `subjects` DROP INDEX `unique_subject`");
            } catch (\Throwable $e) {}

            if (Capsule::schema()->hasColumn('subjects', 'code') && !Capsule::schema()->hasColumn('subjects', 'subject_code')) {
                $pdo->exec("ALTER TABLE `subjects` CHANGE COLUMN `code` `subject_code` VARCHAR(50) NOT NULL");
            }

            if (Capsule::schema()->hasColumn('subjects', 'name') && !Capsule::schema()->hasColumn('subjects', 'descriptive_title')) {
                $pdo->exec("ALTER TABLE `subjects` CHANGE COLUMN `name` `descriptive_title` VARCHAR(200) NOT NULL");
            }

            // Recreate unique_subject with new column name
            try {
                $pdo->exec("ALTER TABLE `subjects` ADD UNIQUE KEY `unique_subject` (`subject_code`, `academic_term_id`)");
            } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        $pdo = Capsule::connection()->getPdo();

        if (Capsule::schema()->hasTable('programs')) {
            if (Capsule::schema()->hasColumn('programs', 'program_name') && !Capsule::schema()->hasColumn('programs', 'name')) {
                try {
                    $pdo->exec("ALTER TABLE `programs` DROP INDEX `programs_program_name_unique`");
                } catch (\Throwable $e) {}

                $pdo->exec("ALTER TABLE `programs` CHANGE COLUMN `program_name` `name` VARCHAR(191) NOT NULL");

                try {
                    $pdo->exec("ALTER TABLE `programs` ADD UNIQUE KEY `programs_name_unique` (`name`)");
                } catch (\Throwable $e) {}
            }
        }

        if (Capsule::schema()->hasTable('subjects')) {
            try {
                $pdo->exec("ALTER TABLE `subjects` DROP INDEX `unique_subject`");
            } catch (\Throwable $e) {}

            if (Capsule::schema()->hasColumn('subjects', 'subject_code') && !Capsule::schema()->hasColumn('subjects', 'code')) {
                $pdo->exec("ALTER TABLE `subjects` CHANGE COLUMN `subject_code` `code` VARCHAR(20) NOT NULL");
            }

            if (Capsule::schema()->hasColumn('subjects', 'descriptive_title') && !Capsule::schema()->hasColumn('subjects', 'name')) {
                $pdo->exec("ALTER TABLE `subjects` CHANGE COLUMN `descriptive_title` `name` VARCHAR(200) NOT NULL");
            }

            try {
                $pdo->exec("ALTER TABLE `subjects` ADD UNIQUE KEY `unique_subject` (`code`, `academic_term_id`)");
            } catch (\Throwable $e) {}
        }
    }
};
