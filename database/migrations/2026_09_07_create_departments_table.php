<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up(): void
    {
        // 1. Create departments table if it doesn't already exist
        if (!Capsule::schema()->hasTable('departments')) {
            Capsule::schema()->create('departments', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code', 50)->unique();
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
            });
        }

        // 2. Seed baseline academic departments
        $baselineDepartments = [
            [
                'code' => 'CIT',
                'name' => 'College of Information Technology',
                'description' => 'Academic department managing Computer Science, Information Technology, and computing programs.',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'CS',
                'name' => 'Department of Computer Studies',
                'description' => 'Department providing core computer science, software engineering, and programming curricula.',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'CBA',
                'name' => 'College of Business Administration',
                'description' => 'Academic department covering Business Administration, Management, and Accountancy programs.',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'CAS',
                'name' => 'College of Arts and Sciences',
                'description' => 'Academic department delivering General Education, Humanities, Social Sciences, and Natural Sciences.',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'COE',
                'name' => 'College of Engineering',
                'description' => 'Academic department overseeing Computer Engineering and applied technical disciplines.',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($baselineDepartments as $dept) {
            if (!Capsule::table('departments')->where('code', $dept['code'])->exists()) {
                Capsule::table('departments')->insert($dept);
            }
        }

        // 3. Add department_id to faculty table
        if (Capsule::schema()->hasTable('faculty')) {
            if (!Capsule::schema()->hasColumn('faculty', 'department_id')) {
                Capsule::statement("ALTER TABLE faculty ADD COLUMN department_id INT UNSIGNED NULL AFTER user_id");
                Capsule::statement("ALTER TABLE faculty ADD CONSTRAINT fk_faculty_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL");
            }

            // 4. Migrate any existing legacy text data from faculty.department to department_id
            if (Capsule::schema()->hasColumn('faculty', 'department')) {
                $facultyRecords = Capsule::table('faculty')
                    ->whereNotNull('department')
                    ->where('department', '!=', '')
                    ->whereNull('department_id')
                    ->get();

                foreach ($facultyRecords as $record) {
                    $rawName = trim((string) $record->department);
                    $dept = Capsule::table('departments')
                        ->where('name', $rawName)
                        ->orWhere('name', 'LIKE', "%{$rawName}%")
                        ->first();

                    if (!$dept) {
                        $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $rawName), 0, 8));
                        if (empty($code)) {
                            $code = 'DEPT' . rand(100, 999);
                        }
                        $deptId = Capsule::table('departments')->insertGetId([
                            'code' => $code,
                            'name' => $rawName,
                            'description' => 'Imported from legacy faculty record',
                            'status' => 'active',
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    } else {
                        $deptId = $dept->id;
                    }

                    Capsule::table('faculty')->where('id', $record->id)->update([
                        'department_id' => $deptId,
                    ]);
                }

                // Drop legacy department text column
                Capsule::statement("ALTER TABLE faculty DROP COLUMN department");
            }
        }
    }

    public function down(): void
    {
        if (Capsule::schema()->hasTable('faculty')) {
            if (Capsule::schema()->hasColumn('faculty', 'department_id')) {
                try {
                    Capsule::statement("ALTER TABLE faculty DROP FOREIGN KEY fk_faculty_department");
                } catch (\Throwable $e) {
                    // Ignore if foreign key was already dropped
                }
                Capsule::statement("ALTER TABLE faculty DROP COLUMN department_id");
            }

            if (!Capsule::schema()->hasColumn('faculty', 'department')) {
                Capsule::statement("ALTER TABLE faculty ADD COLUMN department VARCHAR(100) NULL AFTER user_id");
            }
        }

        if (Capsule::schema()->hasTable('departments')) {
            Capsule::schema()->drop('departments');
        }
    }
};
