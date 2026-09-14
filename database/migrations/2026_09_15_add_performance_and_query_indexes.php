<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up(): void
    {
        $pdo = Capsule::connection()->getPdo();

        $addIndex = function (string $table, string $indexName, string $columns) use ($pdo): void {
            try {
                // Check if table exists
                $checkTable = $pdo->query("SHOW TABLES LIKE '{$table}'");
                if ($checkTable->rowCount() === 0) {
                    return;
                }

                // Check if index already exists
                $stmt = $pdo->prepare("SHOW INDEX FROM `{$table}` WHERE Key_name = :key_name");
                $stmt->execute(['key_name' => $indexName]);
                if ($stmt->rowCount() === 0) {
                    $pdo->exec("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` ({$columns})");
                }
            } catch (\Throwable $e) {
                // Log or ignore if constraint/index conflicts
            }
        };

        // 1. Academic Terms
        $addIndex('academic_terms', 'idx_terms_active_sem', '`is_active`, `semester`');
        $addIndex('academic_terms', 'idx_terms_year_sem', '`academic_year_id`, `semester`');

        // 2. Academic Years
        $addIndex('academic_years', 'idx_ay_school_year', '`school_year`');
        $addIndex('academic_years', 'idx_ay_active', '`is_active`');

        // 3. Users
        $addIndex('users', 'idx_users_status', '`status`');

        // 4. Student Details
        $addIndex('student_details', 'idx_sd_name', '`last_name`, `first_name`');
        $addIndex('student_details', 'idx_sd_year_level', '`year_level`');
        $addIndex('student_details', 'idx_sd_status', '`status`');
        $addIndex('student_details', 'idx_sd_set_prog', '`set_id`, `program_id`');

        // 5. Faculty Details
        $addIndex('faculty_details', 'idx_fd_name', '`last_name`, `first_name`');
        $addIndex('faculty_details', 'idx_fd_dept_type', '`department_id`, `faculty_type`');

        // 6. Subjects
        $addIndex('subjects', 'idx_subjects_term_archived', '`academic_term_id`, `is_archived`, `semester`');
        $addIndex('subjects', 'idx_subjects_term_year_sem', '`academic_term_id`, `year_level`, `semester`');

        // 7. Enrollments
        $addIndex('enrollments', 'idx_enrollments_subject_term', '`subject_id`, `academic_term_id`');
        $addIndex('enrollments', 'idx_enrollments_student_term', '`student_id`, `academic_term_id`');

        // 8. Grades
        $addIndex('grades', 'idx_grades_subject_term_period', '`subject_id`, `academic_term_id`, `grading_period_id`');
        $addIndex('grades', 'idx_grades_student_term', '`student_id`, `academic_term_id`');

        // 9. Grading Sheets
        $addIndex('grading_sheets', 'idx_sheets_term_status', '`academic_term_id`, `status`');
        $addIndex('grading_sheets', 'idx_sheets_faculty_term', '`faculty_id`, `academic_term_id`');

        // 10. Sets
        $addIndex('sets', 'idx_sets_term_year', '`academic_term_id`, `year_level`');
        $addIndex('sets', 'idx_sets_term_status', '`academic_term_id`, `status`');

        // 11. Notifications
        $addIndex('notifications', 'idx_notifications_user_status_date', '`user_id`, `status`, `created_at`');

        // 12. Departments
        $addIndex('departments', 'idx_departments_status', '`status`');
    }

    public function down(): void
    {
        $pdo = Capsule::connection()->getPdo();

        $dropIndex = function (string $table, string $indexName) use ($pdo): void {
            try {
                $stmt = $pdo->prepare("SHOW INDEX FROM `{$table}` WHERE Key_name = :key_name");
                $stmt->execute(['key_name' => $indexName]);
                if ($stmt->rowCount() > 0) {
                    $pdo->exec("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
                }
            } catch (\Throwable $e) {
                // Ignore
            }
        };

        $dropIndex('academic_terms', 'idx_terms_active_sem');
        $dropIndex('academic_terms', 'idx_terms_year_sem');
        $dropIndex('academic_years', 'idx_ay_school_year');
        $dropIndex('academic_years', 'idx_ay_active');
        $dropIndex('users', 'idx_users_status');
        $dropIndex('student_details', 'idx_sd_name');
        $dropIndex('student_details', 'idx_sd_year_level');
        $dropIndex('student_details', 'idx_sd_status');
        $dropIndex('student_details', 'idx_sd_set_prog');
        $dropIndex('faculty_details', 'idx_fd_name');
        $dropIndex('faculty_details', 'idx_fd_dept_type');
        $dropIndex('subjects', 'idx_subjects_term_archived');
        $dropIndex('subjects', 'idx_subjects_term_year_sem');
        $dropIndex('enrollments', 'idx_enrollments_subject_term');
        $dropIndex('enrollments', 'idx_enrollments_student_term');
        $dropIndex('grades', 'idx_grades_subject_term_period');
        $dropIndex('grades', 'idx_grades_student_term');
        $dropIndex('grading_sheets', 'idx_sheets_term_status');
        $dropIndex('grading_sheets', 'idx_sheets_faculty_term');
        $dropIndex('sets', 'idx_sets_term_year');
        $dropIndex('sets', 'idx_sets_term_status');
        $dropIndex('notifications', 'idx_notifications_user_status_date');
        $dropIndex('departments', 'idx_departments_status');
    }
};
