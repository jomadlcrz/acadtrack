<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up(): void
    {
        $pdo = Capsule::connection()->getPdo();

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `attendance_records` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `academic_term_id` INT NOT NULL,
                `subject_id` INT NOT NULL,
                `student_id` INT NOT NULL,
                `faculty_id` INT NOT NULL,
                `attendance_date` DATE NOT NULL,
                `status` ENUM('Present', 'Absent', 'Excused', 'Late') NOT NULL DEFAULT 'Present',
                `remarks` VARCHAR(255) NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`academic_term_id`) REFERENCES `academic_terms`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`faculty_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                UNIQUE KEY `uq_attendance_student_subject_date` (`academic_term_id`, `subject_id`, `student_id`, `attendance_date`),
                INDEX `idx_att_sub_term_date` (`subject_id`, `academic_term_id`, `attendance_date`),
                INDEX `idx_att_stud_term` (`student_id`, `academic_term_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function down(): void
    {
        $pdo = Capsule::connection()->getPdo();
        $pdo->exec("DROP TABLE IF EXISTS `attendance_records`");
    }
};
