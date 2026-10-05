<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up(): void
    {
        $pdo = Capsule::connection()->getPdo();

        // 1. Add set_id column if it doesn't exist
        $checkCol = $pdo->query("SHOW COLUMNS FROM `faculty_subjects` LIKE 'set_id'");
        if ($checkCol->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `faculty_subjects` ADD COLUMN `set_id` INT NULL DEFAULT NULL AFTER `subject_id`");
        }

        // 2. Add foreign key to sets(id)
        try {
            $checkFk = $pdo->query("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = 'faculty_subjects' 
                  AND CONSTRAINT_NAME = 'fk_faculty_subjects_set'
            ");
            if ($checkFk->rowCount() === 0) {
                $pdo->exec("ALTER TABLE `faculty_subjects` ADD CONSTRAINT `fk_faculty_subjects_set` FOREIGN KEY (`set_id`) REFERENCES `sets` (`id`) ON DELETE CASCADE");
            }
        } catch (\Throwable $e) {
            // Ignore if foreign key already exists or cannot be created
        }

        // 3. Add idx_fs_faculty_term index to satisfy foreign key constraint on faculty_id
        try {
            $checkFacultyIdx = $pdo->query("SHOW INDEX FROM `faculty_subjects` WHERE Key_name = 'idx_fs_faculty_term'");
            if ($checkFacultyIdx->rowCount() === 0) {
                $pdo->exec("ALTER TABLE `faculty_subjects` ADD INDEX `idx_fs_faculty_term` (`faculty_id`, `academic_term_id`)");
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        // 4. Drop old unique key (faculty_id, subject_id, academic_term_id) to allow faculty to handle multiple sections of the same subject
        try {
            $checkOldUnique = $pdo->query("SHOW INDEX FROM `faculty_subjects` WHERE Key_name = 'unique_assignment'");
            if ($checkOldUnique->rowCount() > 0) {
                $pdo->exec("ALTER TABLE `faculty_subjects` DROP INDEX `unique_assignment`");
            }
        } catch (\Throwable $e) {
            // Ignore if not present
        }

        // 4. Add index for set_id
        try {
            $checkSetIndex = $pdo->query("SHOW INDEX FROM `faculty_subjects` WHERE Key_name = 'idx_fs_set'");
            if ($checkSetIndex->rowCount() === 0) {
                $pdo->exec("ALTER TABLE `faculty_subjects` ADD INDEX `idx_fs_set` (`set_id`)");
            }
        } catch (\Throwable $e) {
            // Ignore
        }
    }

    public function down(): void
    {
        $pdo = Capsule::connection()->getPdo();

        try {
            $pdo->exec("ALTER TABLE `faculty_subjects` DROP FOREIGN KEY `fk_faculty_subjects_set`");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE `faculty_subjects` DROP INDEX `idx_fs_set`");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE `faculty_subjects` DROP COLUMN `set_id`");
        } catch (\Throwable $e) {}

        try {
            $pdo->exec("ALTER TABLE `faculty_subjects` ADD UNIQUE KEY `unique_assignment` (`faculty_id`, `subject_id`, `academic_term_id`)");
        } catch (\Throwable $e) {}
    }
};
