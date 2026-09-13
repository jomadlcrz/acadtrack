<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up(): void
    {
        $pdo = Capsule::connection()->getPdo();
        $dbName = Capsule::connection()->getDatabaseName();

        $dropFkIfExists = function (string $table, string $fkName) use ($pdo, $dbName) {
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

        // 1. If 'sections' table exists and 'sets' does not, rename sections -> sets
        if (Capsule::schema()->hasTable('sections') && !Capsule::schema()->hasTable('sets')) {
            $dropFkIfExists('students', 'fk_students_section');
            $dropFkIfExists('students', 'students_ibfk_2');
            $dropFkIfExists('sections', 'fk_sections_department');
            $dropFkIfExists('sections', 'sections_ibfk_2');

            $pdo->exec("RENAME TABLE sections TO `sets`");
        }

        // 2. Adjust indexes and constraints on 'sets' table
        if (Capsule::schema()->hasTable('sets')) {
            $indexExists = Capsule::select("SHOW INDEX FROM `sets` WHERE Key_name = 'unique_section'");
            if (!empty($indexExists)) {
                try {
                    $pdo->exec("ALTER TABLE `sets` DROP INDEX unique_section");
                } catch (\Throwable $e) {}
            }

            $uniqueSetIndex = Capsule::select("SHOW INDEX FROM `sets` WHERE Key_name = 'unique_set'");
            if (empty($uniqueSetIndex)) {
                try {
                    $pdo->exec("ALTER TABLE `sets` ADD UNIQUE KEY unique_set (name, academic_term_id)");
                } catch (\Throwable $e) {}
            }

            $fkDept = Capsule::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = :db AND TABLE_NAME = 'sets' AND CONSTRAINT_NAME = 'fk_sets_department' AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ", ['db' => $dbName]);
            if (empty($fkDept)) {
                try {
                    $pdo->exec("ALTER TABLE `sets` ADD CONSTRAINT fk_sets_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL");
                } catch (\Throwable $e) {}
            }
        }

        // 3. Rename or add set_id in students table
        if (Capsule::schema()->hasTable('students')) {
            if (Capsule::schema()->hasColumn('students', 'section_id') && !Capsule::schema()->hasColumn('students', 'set_id')) {
                $dropFkIfExists('students', 'fk_students_section');
                $dropFkIfExists('students', 'students_ibfk_2');
                $pdo->exec("ALTER TABLE students CHANGE COLUMN section_id set_id INT NULL");
            } elseif (!Capsule::schema()->hasColumn('students', 'set_id')) {
                Capsule::schema()->table('students', function (Blueprint $table) {
                    $table->unsignedInteger('set_id')->nullable()->after('user_id');
                });
            }

            $fkStudentSet = Capsule::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = :db AND TABLE_NAME = 'students' AND CONSTRAINT_NAME = 'fk_students_set' AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ", ['db' => $dbName]);
            if (empty($fkStudentSet) && Capsule::schema()->hasTable('sets')) {
                try {
                    $pdo->exec("ALTER TABLE students ADD CONSTRAINT fk_students_set FOREIGN KEY (set_id) REFERENCES `sets`(id) ON DELETE SET NULL");
                } catch (\Throwable $e) {}
            }
        }
    }

    public function down(): void
    {
        $pdo = Capsule::connection()->getPdo();
        $dbName = Capsule::connection()->getDatabaseName();

        $dropFkIfExists = function (string $table, string $fkName) use ($pdo, $dbName) {
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

        if (Capsule::schema()->hasTable('sets') && !Capsule::schema()->hasTable('sections')) {
            $dropFkIfExists('students', 'fk_students_set');
            $dropFkIfExists('sets', 'fk_sets_department');

            $pdo->exec("RENAME TABLE `sets` TO sections");

            if (Capsule::schema()->hasColumn('students', 'set_id') && !Capsule::schema()->hasColumn('students', 'section_id')) {
                $pdo->exec("ALTER TABLE students CHANGE COLUMN set_id section_id INT NULL");
                $pdo->exec("ALTER TABLE students ADD CONSTRAINT fk_students_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL");
            }
        }
    }
};
