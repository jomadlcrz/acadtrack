<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up(): void
    {
        $pdo = Capsule::connection()->getPdo();

        // One row per student per academic term: where the student stands that term
        // (year level, set, Regular/Irregular). Subject enrollments hang off this per term.
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `student_term_registrations` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `student_id` INT NOT NULL,
                `academic_term_id` INT NOT NULL,
                `program_id` INT UNSIGNED NULL,
                `set_id` INT NULL,
                `year_level` INT NOT NULL DEFAULT 1,
                `status` ENUM('Regular', 'Irregular') NOT NULL DEFAULT 'Regular',
                `registration_status` ENUM('enrolled', 'withdrawn', 'completed') NOT NULL DEFAULT 'enrolled',
                `registered_by` INT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uq_student_term` (`student_id`, `academic_term_id`),
                INDEX `idx_str_term_set` (`academic_term_id`, `set_id`),
                INDEX `idx_str_term_status` (`academic_term_id`, `status`),
                CONSTRAINT `fk_str_student` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_str_term` FOREIGN KEY (`academic_term_id`) REFERENCES `academic_terms`(`id`) ON DELETE RESTRICT,
                CONSTRAINT `fk_str_set` FOREIGN KEY (`set_id`) REFERENCES `sets`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Backfill: every term a student already has subject enrollments in, using their current profile.
        $pdo->exec("
            INSERT IGNORE INTO student_term_registrations
                (student_id, academic_term_id, program_id, set_id, year_level, status)
            SELECT DISTINCT s.id, e.academic_term_id,
                   COALESCE(sd.program_id, st.program_id), s.set_id, s.year_level, s.status
            FROM students s
            JOIN enrollments e ON e.student_id = s.id
            LEFT JOIN sets st ON st.id = s.set_id
            LEFT JOIN student_details sd ON sd.user_id = s.user_id
        ");

        // Students without any enrollment are registered for the active term.
        $pdo->exec("
            INSERT IGNORE INTO student_term_registrations
                (student_id, academic_term_id, program_id, set_id, year_level, status)
            SELECT s.id, t.id, COALESCE(sd.program_id, st.program_id), s.set_id, s.year_level, s.status
            FROM students s
            JOIN academic_terms t ON t.is_active = 1
            LEFT JOIN sets st ON st.id = s.set_id
            LEFT JOIN student_details sd ON sd.user_id = s.user_id
        ");
    }

    public function down(): void
    {
        Capsule::connection()->getPdo()->exec("DROP TABLE IF EXISTS `student_term_registrations`;");
    }
};
