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

        // 1. Update academic_terms: add school_year column if missing, broaden semester enum
        if (Capsule::schema()->hasTable('academic_terms')) {
            if (!Capsule::schema()->hasColumn('academic_terms', 'school_year')) {
                Capsule::schema()->table('academic_terms', function (Blueprint $table) {
                    $table->string('school_year', 20)->nullable()->after('academic_year_id');
                });
            }

            // Sync school_year from academic_years if present
            if (Capsule::schema()->hasTable('academic_years')) {
                $pdo->exec("
                    UPDATE academic_terms at
                    JOIN academic_years ay ON at.academic_year_id = ay.id
                    SET at.school_year = ay.name
                    WHERE at.school_year IS NULL OR at.school_year = ''
                ");
            }

            // Expand semester column to allow 'Summer'
            try {
                $pdo->exec("ALTER TABLE `academic_terms` MODIFY COLUMN `semester` VARCHAR(50) NOT NULL DEFAULT '1'");
            } catch (\Throwable $e) {}
        }

        // 2. Create programs table
        if (!Capsule::schema()->hasTable('programs')) {
            Capsule::schema()->create('programs', function (Blueprint $table) {
                $table->increments('id');
                $table->string('program_abbrev', 30)->unique();
                $table->string('name', 191)->unique();
                $table->string('program_type', 100)->default('Bachelors Degree');
                $table->string('program_length', 50)->default("4 Years");
                $table->unsignedInteger('department_id')->nullable();
                $table->enum('status', ['draft', 'active', 'archived'])->default('active');
                $table->text('description')->nullable();
                $table->timestamps();

                $table->foreign('department_id')
                    ->references('id')
                    ->on('departments')
                    ->onDelete('set null');
                $table->index(['department_id', 'status']);
            });
        }

        // 3. Create curricula table
        if (!Capsule::schema()->hasTable('curricula')) {
            Capsule::schema()->create('curricula', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('program_id');
                $table->string('version', 50)->default('2026-2027');
                $table->enum('status', ['draft', 'active', 'archived'])->default('active');
                $table->timestamps();

                $table->foreign('program_id')
                    ->references('id')
                    ->on('programs')
                    ->onDelete('restrict');
                $table->unique(['program_id', 'version']);
                $table->index(['program_id', 'status']);
            });
        }

        // 4. Create curriculum_subjects table
        if (!Capsule::schema()->hasTable('curriculum_subjects')) {
            Capsule::schema()->create('curriculum_subjects', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('curriculum_id');
                $table->integer('subject_id')->nullable();
                $table->string('year_level', 30);
                $table->string('semester', 50);
                $table->string('subject_code', 50);
                $table->string('descriptive_title', 200);
                $table->decimal('units', 4, 1)->default(3.0);
                $table->string('subject_type', 50)->default('GenEd Core');
                $table->string('prerequisites', 255)->nullable();
                $table->integer('display_order')->default(0);
                $table->timestamps();

                $table->foreign('curriculum_id')
                    ->references('id')
                    ->on('curricula')
                    ->onDelete('cascade');
                $table->index(['curriculum_id', 'year_level', 'semester']);
            });
        }

        // 5. Update sets table: add program_id and set_code if missing
        if (Capsule::schema()->hasTable('sets')) {
            if (!Capsule::schema()->hasColumn('sets', 'program_id')) {
                Capsule::schema()->table('sets', function (Blueprint $table) {
                    $table->unsignedInteger('program_id')->nullable()->after('name');
                    $table->string('set_code', 20)->nullable()->after('year_level');
                    $table->foreign('program_id')
                        ->references('id')
                        ->on('programs')
                        ->onDelete('set null');
                    $table->index(['program_id', 'year_level']);
                });
            }
        }

        // 6. Seed default programs if table is empty
        $programCount = Capsule::table('programs')->count();
        if ($programCount === 0) {
            $deptCite = Capsule::table('departments')->where('code', 'CITE')->orWhere('code', 'CIT')->first();
            $deptIdCite = $deptCite ? $deptCite->id : 1;

            $deptCoe = Capsule::table('departments')->where('code', 'COE')->first();
            $deptIdCoe = $deptCoe ? $deptCoe->id : $deptIdCite;

            $defaultPrograms = [
                [
                    'program_abbrev' => 'BSIT',
                    'name' => 'Bachelor of Science in Information Technology',
                    'program_type' => "Bachelor's Degree",
                    'program_length' => '4 Years',
                    'department_id' => $deptIdCite,
                    'status' => 'active',
                    'description' => 'Prepares students to be IT professionals who are able to perform installation, operation, programming, and maintenance of computer systems.',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'program_abbrev' => 'BSCS',
                    'name' => 'Bachelor of Science in Computer Science',
                    'program_type' => "Bachelor's Degree",
                    'program_length' => '4 Years',
                    'department_id' => $deptIdCoe,
                    'status' => 'active',
                    'description' => 'Study of computing concepts, algorithmic foundations, software design, and machine intelligence.',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'program_abbrev' => 'BSCrim',
                    'name' => 'Bachelor of Science in Criminology',
                    'program_type' => "Bachelor's Degree",
                    'program_length' => '4 Years',
                    'department_id' => null,
                    'status' => 'active',
                    'description' => 'Study of crime causation, criminal law, law enforcement administration, and correctional institutions.',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'program_abbrev' => 'BSBA',
                    'name' => 'Bachelor of Science in Business Administration major in Marketing Management',
                    'program_type' => "Bachelor's Degree",
                    'program_length' => '4 Years',
                    'department_id' => null,
                    'status' => 'active',
                    'description' => 'Equips students with principles of modern marketing, consumer behavior, and business development.',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'program_abbrev' => 'BEEd',
                    'name' => 'Bachelor of Elementary Education',
                    'program_type' => "Bachelor's Degree",
                    'program_length' => '4 Years',
                    'department_id' => null,
                    'status' => 'active',
                    'description' => 'Designed to prepare future teachers for early childhood and elementary grade levels.',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
                [
                    'program_abbrev' => 'BSEd',
                    'name' => 'Bachelor of Secondary Education',
                    'program_type' => "Bachelor's Degree",
                    'program_length' => '4 Years',
                    'department_id' => null,
                    'status' => 'active',
                    'description' => 'Prepares educators equipped with professional pedagogical skills for high school instruction.',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
            ];

            Capsule::table('programs')->insert($defaultPrograms);
        }

        // 7. Seed baseline curriculum for BSIT if empty
        $bsit = Capsule::table('programs')->where('program_abbrev', 'BSIT')->first();
        if ($bsit && Capsule::table('curricula')->where('program_id', $bsit->id)->count() === 0) {
            $curriculumId = Capsule::table('curricula')->insertGetId([
                'program_id' => $bsit->id,
                'version' => '2026-2027',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $bsitSubjects = [
                ['year_level' => 'First Year', 'semester' => '1st Semester', 'subject_code' => 'IT101', 'descriptive_title' => 'Introduction to Computing', 'units' => 3.0, 'subject_type' => 'GenEd Core', 'prerequisites' => 'None', 'display_order' => 1],
                ['year_level' => 'First Year', 'semester' => '1st Semester', 'subject_code' => 'IT102', 'descriptive_title' => 'Computer Programming 1', 'units' => 3.0, 'subject_type' => 'Major with Lab', 'prerequisites' => 'None', 'display_order' => 2],
                ['year_level' => 'First Year', 'semester' => '2nd Semester', 'subject_code' => 'IT103', 'descriptive_title' => 'Data Structures and Algorithms', 'units' => 3.0, 'subject_type' => 'Major with Lab', 'prerequisites' => 'IT102', 'display_order' => 3],
                ['year_level' => 'First Year', 'semester' => '2nd Semester', 'subject_code' => 'IT104', 'descriptive_title' => 'Discrete Mathematics', 'units' => 3.0, 'subject_type' => 'GenEd Core', 'prerequisites' => 'None', 'display_order' => 4],
                ['year_level' => 'Second Year', 'semester' => '1st Semester', 'subject_code' => 'IT201', 'descriptive_title' => 'Database Management Systems 1', 'units' => 3.0, 'subject_type' => 'Major with Lab', 'prerequisites' => 'IT103', 'display_order' => 5],
                ['year_level' => 'Second Year', 'semester' => '1st Semester', 'subject_code' => 'IT202', 'descriptive_title' => 'Web Systems and Technologies 1', 'units' => 3.0, 'subject_type' => 'Major with Lab', 'prerequisites' => 'IT102', 'display_order' => 6],
                ['year_level' => 'Second Year', 'semester' => '2nd Semester', 'subject_code' => 'IT203', 'descriptive_title' => 'Information Management', 'units' => 3.0, 'subject_type' => 'Major with Lab', 'prerequisites' => 'IT201', 'display_order' => 7],
                ['year_level' => 'Third Year', 'semester' => '1st Semester', 'subject_code' => 'IT301', 'descriptive_title' => 'Systems Analysis and Design', 'units' => 3.0, 'subject_type' => 'Major without Lab', 'prerequisites' => 'IT203', 'display_order' => 8],
                ['year_level' => 'Third Year', 'semester' => '2nd Semester', 'subject_code' => 'IT302', 'descriptive_title' => 'Information Assurance and Security', 'units' => 3.0, 'subject_type' => 'Major with Lab', 'prerequisites' => 'IT201', 'display_order' => 9],
                ['year_level' => 'Fourth Year', 'semester' => '1st Semester', 'subject_code' => 'IT401', 'descriptive_title' => 'Capstone Project 1', 'units' => 3.0, 'subject_type' => 'Research/Thesis', 'prerequisites' => 'IT301', 'display_order' => 10],
                ['year_level' => 'Fourth Year', 'semester' => '2nd Semester', 'subject_code' => 'IT402', 'descriptive_title' => 'Capstone Project 2', 'units' => 3.0, 'subject_type' => 'Research/Thesis', 'prerequisites' => 'IT401', 'display_order' => 11],
            ];

            foreach ($bsitSubjects as $s) {
                Capsule::table('curriculum_subjects')->insert(array_merge($s, [
                    'curriculum_id' => $curriculumId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]));
            }
        }

        // 8. Update existing sets to attach program_id and set_code
        if (Capsule::schema()->hasTable('sets')) {
            $allSets = Capsule::table('sets')->get();
            $programsMap = Capsule::table('programs')->pluck('id', 'program_abbrev')->toArray();

            foreach ($allSets as $set) {
                // E.g., 'BSIT-1A' -> program 'BSIT', year 1, code 'A'
                if (preg_match('/^([A-Za-z]+)-(\d+)([A-Za-z0-9]+)$/', $set->name, $matches)) {
                    $abbrev = $matches[1];
                    $year = (int)$matches[2];
                    $code = strtoupper($matches[3]);
                    $progId = $programsMap[$abbrev] ?? null;

                    Capsule::table('sets')->where('id', $set->id)->update([
                        'program_id' => $progId,
                        'set_code' => $code,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Capsule::schema()->dropIfExists('curriculum_subjects');
        Capsule::schema()->dropIfExists('curricula');
        if (Capsule::schema()->hasTable('sets')) {
            if (Capsule::schema()->hasColumn('sets', 'program_id')) {
                Capsule::schema()->table('sets', function (Blueprint $table) {
                    $table->dropForeign(['program_id']);
                    $table->dropColumn(['program_id', 'set_code']);
                });
            }
        }
        Capsule::schema()->dropIfExists('programs');
    }
};
