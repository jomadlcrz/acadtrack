<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up(): void
    {
        // 1. Upgrade sections table structure
        if (Capsule::schema()->hasTable('sections')) {
            if (!Capsule::schema()->hasColumn('sections', 'updated_at')) {
                Capsule::statement("ALTER TABLE sections ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
            }

            if (!Capsule::schema()->hasColumn('sections', 'department_id')) {
                Capsule::statement("ALTER TABLE sections ADD COLUMN department_id INT UNSIGNED NULL AFTER academic_term_id");
                try {
                    Capsule::statement("ALTER TABLE sections ADD CONSTRAINT fk_sections_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL");
                } catch (\Throwable $e) {
                    // Ignore if constraint exists
                }
            }

            if (!Capsule::schema()->hasColumn('sections', 'status')) {
                Capsule::statement("ALTER TABLE sections ADD COLUMN status ENUM('active', 'inactive') NOT NULL DEFAULT 'active' AFTER department_id");
            }

            $uniqueIndex = Capsule::select("SHOW INDEX FROM sections WHERE Key_name = 'unique_section'");
            if (empty($uniqueIndex)) {
                try {
                    Capsule::statement("ALTER TABLE sections ADD UNIQUE KEY unique_section (name, academic_term_id)");
                } catch (\Throwable $e) {
                    // Ignore if exists
                }
            }
        }

        // 2. Add foreign key from students.section_id to sections.id if not already present
        if (Capsule::schema()->hasTable('students') && Capsule::schema()->hasColumn('students', 'section_id')) {
            $fkExists = Capsule::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'students'
                  AND COLUMN_NAME = 'section_id'
                  AND REFERENCED_TABLE_NAME = 'sections'
            ");
            if (empty($fkExists)) {
                try {
                    Capsule::statement("ALTER TABLE students ADD CONSTRAINT fk_students_section FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL");
                } catch (\Throwable $e) {
                    // Ignore if already set
                }
            }
        }

        // 3. Seed baseline academic sections
        $citDept = Capsule::table('departments')->where('code', 'CIT')->orWhere('code', 'CITE')->first();
        $csDept = Capsule::table('departments')->where('code', 'CS')->first();
        $citDeptId = $citDept ? $citDept->id : null;
        $csDeptId = $csDept ? $csDept->id : null;

        $terms = Capsule::table('academic_terms')->get();
        foreach ($terms as $term) {
            $baselineSections = [
                ['name' => 'BSIT-1A', 'year_level' => 1, 'department_id' => $citDeptId],
                ['name' => 'BSIT-1B', 'year_level' => 1, 'department_id' => $citDeptId],
                ['name' => 'BSIT-2A', 'year_level' => 2, 'department_id' => $citDeptId],
                ['name' => 'BSCS-1A', 'year_level' => 1, 'department_id' => $csDeptId],
            ];

            foreach ($baselineSections as $sec) {
                $exists = Capsule::table('sections')
                    ->where('name', $sec['name'])
                    ->where('academic_term_id', $term->id)
                    ->exists();

                if (!$exists) {
                    Capsule::table('sections')->insert([
                        'name' => $sec['name'],
                        'year_level' => $sec['year_level'],
                        'academic_term_id' => $term->id,
                        'department_id' => $sec['department_id'],
                        'status' => 'active',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        // 4. Backfill any existing students without a section into the first section
        $firstSection = Capsule::table('sections')->orderBy('id', 'asc')->first();
        if ($firstSection) {
            Capsule::table('students')->whereNull('section_id')->update([
                'section_id' => $firstSection->id,
            ]);
        }
    }

    public function down(): void
    {
        if (Capsule::schema()->hasTable('students')) {
            try {
                Capsule::statement("ALTER TABLE students DROP FOREIGN KEY fk_students_section");
            } catch (\Throwable $e) {
            }
        }

        if (Capsule::schema()->hasTable('sections')) {
            try {
                Capsule::statement("ALTER TABLE sections DROP FOREIGN KEY fk_sections_department");
            } catch (\Throwable $e) {
            }
            try {
                Capsule::statement("ALTER TABLE sections DROP INDEX unique_section");
            } catch (\Throwable $e) {
            }
        }
    }
};
