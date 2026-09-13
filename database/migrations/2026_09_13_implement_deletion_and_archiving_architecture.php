<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up(): void
    {
        $schema = Capsule::schema();

        // 1. Add archiving columns to subjects
        if (!$schema->hasColumn('subjects', 'is_archived')) {
            $schema->table('subjects', function (Blueprint $table) {
                $table->boolean('is_archived')->default(false)->after('academic_term_id');
                $table->timestamp('archived_at')->nullable()->default(null)->after('is_archived');
                $table->index('is_archived');
            });
        }

        // 2. Add archiving columns to academic_terms
        if (!$schema->hasColumn('academic_terms', 'is_archived')) {
            $schema->table('academic_terms', function (Blueprint $table) {
                $table->boolean('is_archived')->default(false)->after('is_active');
                $table->timestamp('archived_at')->nullable()->default(null)->after('is_archived');
                $table->index('is_archived');
            });
        }

        $pdo = Capsule::connection()->getPdo();

        // 3. Create grade_history_log table for transparent audit logging
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS grade_history_log (
                log_id INT AUTO_INCREMENT PRIMARY KEY,
                grade_id INT NOT NULL,
                student_id INT NOT NULL,
                old_score DECIMAL(5,2) NULL,
                new_score DECIMAL(5,2) NULL,
                action_performed VARCHAR(20) DEFAULT 'UPDATE',
                changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_grade_id (grade_id),
                INDEX idx_student_id (student_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // 5. Create automated MySQL trigger for grade modifications
        $pdo->exec("DROP TRIGGER IF EXISTS before_grade_evaluation_change");
        $pdo->exec("
            CREATE TRIGGER before_grade_evaluation_change
            BEFORE UPDATE ON grades
            FOR EACH ROW
            BEGIN
                IF (OLD.grade <> NEW.grade) OR (OLD.grade IS NULL AND NEW.grade IS NOT NULL) OR (OLD.grade IS NOT NULL AND NEW.grade IS NULL) THEN
                    INSERT INTO grade_history_log (grade_id, student_id, old_score, new_score, action_performed)
                    VALUES (OLD.id, OLD.student_id, OLD.grade, NEW.grade, 'UPDATE');
                END IF;
            END
        ");

        // 6. Relational Guardrails: Enforce ON DELETE RESTRICT on core academic pillars
        // Drop cascading constraints and re-add with RESTRICT where records must be immutable

        // helper to safely drop foreign key if exists
        $dropFkIfExists = function (string $table, string $fkName) use ($pdo) {
            $dbName = Capsule::connection()->getDatabaseName();
            $stmt = $pdo->prepare("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :tbl AND CONSTRAINT_NAME = :fk AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ");
            $stmt->execute(['db' => $dbName, 'tbl' => $table, 'fk' => $fkName]);
            if ($stmt->fetch()) {
                $pdo->exec("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fkName}`");
            }
        };

        // Grades table guardrails: grades cannot be cascaded when students, subjects, or terms are targeted
        $dropFkIfExists('grades', 'grades_ibfk_1');
        $dropFkIfExists('grades', 'grades_ibfk_2');
        $dropFkIfExists('grades', 'grades_ibfk_4');
        $dropFkIfExists('grades', 'fk_grades_student_restrict');
        $dropFkIfExists('grades', 'fk_grades_subject_restrict');
        $dropFkIfExists('grades', 'fk_grades_term_restrict');

        $pdo->exec("ALTER TABLE grades ADD CONSTRAINT fk_grades_student_restrict FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE RESTRICT");
        $pdo->exec("ALTER TABLE grades ADD CONSTRAINT fk_grades_subject_restrict FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT");
        $pdo->exec("ALTER TABLE grades ADD CONSTRAINT fk_grades_term_restrict FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE RESTRICT");

        // Enrollments table guardrails: enrollments cannot be silently dropped if subjects or terms are targeted
        $dropFkIfExists('enrollments', 'enrollments_ibfk_1');
        $dropFkIfExists('enrollments', 'enrollments_ibfk_2');
        $dropFkIfExists('enrollments', 'enrollments_ibfk_3');
        $dropFkIfExists('enrollments', 'fk_enrollments_student_restrict');
        $dropFkIfExists('enrollments', 'fk_enrollments_student_cascade');
        $dropFkIfExists('enrollments', 'fk_enrollments_subject_restrict');
        $dropFkIfExists('enrollments', 'fk_enrollments_term_restrict');

        $pdo->exec("ALTER TABLE enrollments ADD CONSTRAINT fk_enrollments_student_cascade FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE");
        $pdo->exec("ALTER TABLE enrollments ADD CONSTRAINT fk_enrollments_subject_restrict FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT");
        $pdo->exec("ALTER TABLE enrollments ADD CONSTRAINT fk_enrollments_term_restrict FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE RESTRICT");

        // Grading Sheets guardrails: prevent subject deletion if grading sheets exist
        $dropFkIfExists('grading_sheets', 'grading_sheets_ibfk_2');
        $dropFkIfExists('grading_sheets', 'grading_sheets_ibfk_4');
        $dropFkIfExists('grading_sheets', 'fk_grading_sheets_subject_restrict');
        $dropFkIfExists('grading_sheets', 'fk_grading_sheets_term_restrict');

        $pdo->exec("ALTER TABLE grading_sheets ADD CONSTRAINT fk_grading_sheets_subject_restrict FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT");
        $pdo->exec("ALTER TABLE grading_sheets ADD CONSTRAINT fk_grading_sheets_term_restrict FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE RESTRICT");

        // Faculty Subjects guardrails: prevent subject deletion if faculty assignments exist
        $dropFkIfExists('faculty_subjects', 'faculty_subjects_ibfk_2');
        $dropFkIfExists('faculty_subjects', 'fk_faculty_subjects_subject_restrict');

        $pdo->exec("ALTER TABLE faculty_subjects ADD CONSTRAINT fk_faculty_subjects_subject_restrict FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT");
    }

    public function down(): void
    {
        $schema = Capsule::schema();
        $pdo = Capsule::connection()->getPdo();

        $pdo->exec("DROP TRIGGER IF EXISTS before_grade_evaluation_change");
        $pdo->exec("DROP TABLE IF EXISTS grade_history_log");

        if ($schema->hasColumn('subjects', 'is_archived')) {
            $schema->table('subjects', function (Blueprint $table) {
                $table->dropIndex(['is_archived']);
                $table->dropColumn(['is_archived', 'archived_at']);
            });
        }

        if ($schema->hasColumn('academic_terms', 'is_archived')) {
            $schema->table('academic_terms', function (Blueprint $table) {
                $table->dropIndex(['is_archived']);
                $table->dropColumn(['is_archived', 'archived_at']);
            });
        }
    }
};
