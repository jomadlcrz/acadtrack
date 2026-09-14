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
                'code' => 'CITE',
                'name' => 'College of Information Technology Education',
                'description' => 'Academic department managing Information Technology, Computer Science, and computing education programs.',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'COC',
                'name' => 'College of Criminology',
                'description' => 'Academic department delivering criminal justice, law enforcement administration, and criminology curricula.',
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
                'code' => 'COED',
                'name' => 'College of Education',
                'description' => 'Academic department preparing professional educators for elementary and secondary grade levels.',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $codeCol = Capsule::schema()->hasColumn('departments', 'dept_abbrev') ? 'dept_abbrev' : 'code';
        $nameCol = Capsule::schema()->hasColumn('departments', 'dept_name') ? 'dept_name' : 'name';
        foreach ($baselineDepartments as $dept) {
            $insertData = [
                $codeCol => $dept['code'],
                $nameCol => $dept['name'],
                'description' => $dept['description'],
                'status' => $dept['status'],
                'created_at' => $dept['created_at'],
                'updated_at' => $dept['updated_at'],
            ];
            if (!Capsule::table('departments')->where($codeCol, $dept['code'])->exists()) {
                Capsule::table('departments')->insert($insertData);
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
