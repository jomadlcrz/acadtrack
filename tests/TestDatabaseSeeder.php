<?php

declare(strict_types=1);

namespace Tests;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\GradingPeriod;
use App\Models\GradingSetting;
use App\Models\Program;
use App\Models\Role;
use App\Models\Set;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;

class TestDatabaseSeeder
{
    public static function seedIfNeeded(): void
    {
        // Ensure audit_logs and student_term_registrations exist if running against fresh or legacy test DB
        if (!DB::schema()->hasTable('audit_logs')) {
            DB::connection()->getPdo()->exec("
                CREATE TABLE IF NOT EXISTS `audit_logs` (
                    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                    `actor_id` INT NULL,
                    `actor_name` VARCHAR(120) NOT NULL,
                    `actor_email` VARCHAR(255) NOT NULL DEFAULT '',
                    `actor_role` VARCHAR(30) NOT NULL DEFAULT '',
                    `category` VARCHAR(30) NOT NULL,
                    `action` VARCHAR(80) NOT NULL,
                    `target_type` VARCHAR(40) NOT NULL DEFAULT '',
                    `target_id` VARCHAR(64) NOT NULL DEFAULT '',
                    `target_label` VARCHAR(120) NOT NULL DEFAULT '',
                    `summary` VARCHAR(500) NOT NULL,
                    `ip_address` VARCHAR(45) NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_audit_created` (`created_at`),
                    INDEX `idx_audit_category` (`category`, `created_at`),
                    INDEX `idx_audit_actor` (`actor_id`, `created_at`),
                    INDEX `idx_audit_target` (`target_type`, `target_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        if (!DB::schema()->hasTable('student_term_registrations')) {
            DB::connection()->getPdo()->exec("
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
        }

        // 1. Ensure Academic Years & Terms
        $term1 = AcademicTerm::find(1);
        $term2 = AcademicTerm::find(2);

        if (!$term1) {
            $year = AcademicYear::firstOrCreate(['id' => 1], ['school_year' => '2026-2027', 'is_active' => 1]);
            $term1 = AcademicTerm::firstOrCreate(['id' => 1], [
                'academic_year_id' => $year->id,
                'school_year' => '2026-2027',
                'semester' => 1,
                'is_active' => 1,
                'is_archived' => 0,
            ]);
        }

        if (!$term2) {
            $term2 = AcademicTerm::firstOrCreate(['id' => 2], [
                'academic_year_id' => 1,
                'school_year' => '2026-2027',
                'semester' => 2,
                'is_active' => 0,
                'is_archived' => 0,
            ]);
        }

        $termId = (int) $term1->id;

        // 2. Ensure Roles
        $roles = [
            ['id' => 1, 'role_name' => 'Admin', 'description' => 'System Administrator with full institutional management access'],
            ['id' => 2, 'role_name' => 'Dean', 'description' => 'College Dean overseeing curriculum, faculty assignments, and grade verification'],
            ['id' => 3, 'role_name' => 'Faculty', 'description' => 'Instructor encoding student scores and submitting period grading sheets'],
            ['id' => 4, 'role_name' => 'Student', 'description' => 'Enrolled student viewing official grades and whole evaluation'],
        ];
        foreach ($roles as $r) {
            Role::firstOrCreate(['id' => $r['id']], $r);
        }

        // 3. Ensure Baseline Users (Admin, Dean, Faculty, Student)
        if (!User::find(1)) {
            DB::table('users')->insert([
                'id' => 1,
                'email' => 'admin@gwc.edu',
                'password' => password_hash('GWC_acadtrack@2026', PASSWORD_BCRYPT),
                'status' => 'active',
                'is_temp_password' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            DB::table('user_roles')->insert(['user_id' => 1, 'role_id' => 1]);
            DB::table('admin_details')->insert([
                'user_id' => 1,
                'first_name' => 'System',
                'last_name' => 'Admin',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        if (!User::find(2)) {
            DB::table('users')->insert([
                'id' => 2,
                'email' => 'dean@gwc.edu',
                'password' => password_hash('dean123', PASSWORD_BCRYPT),
                'status' => 'active',
                'is_temp_password' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            DB::table('user_roles')->insert(['user_id' => 2, 'role_id' => 2]);
            DB::table('faculty_details')->insert([
                'user_id' => 2,
                'first_name' => 'College',
                'last_name' => 'Dean',
                'faculty_type' => 'dean',
                'department_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            DB::table('faculty')->insert([
                'id' => 1,
                'user_id' => 2,
                'department_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        if (!User::find(3)) {
            DB::table('users')->insert([
                'id' => 3,
                'email' => 'faculty@gwc.edu',
                'password' => password_hash('faculty123', PASSWORD_BCRYPT),
                'status' => 'active',
                'is_temp_password' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            DB::table('user_roles')->insert(['user_id' => 3, 'role_id' => 3]);
            DB::table('faculty_details')->insert([
                'user_id' => 3,
                'first_name' => 'John',
                'last_name' => 'Teacher',
                'faculty_type' => 'instructor',
                'department_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            DB::table('faculty')->insert([
                'id' => 2,
                'user_id' => 3,
                'department_id' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        if (!User::find(4)) {
            DB::table('users')->insert([
                'id' => 4,
                'email' => 'student@gwc.edu',
                'password' => password_hash('student123', PASSWORD_BCRYPT),
                'status' => 'active',
                'is_temp_password' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            DB::table('user_roles')->insert(['user_id' => 4, 'role_id' => 4]);
            DB::table('student_details')->insert([
                'user_id' => 4,
                'student_number' => '2026-0001',
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'program_id' => 1,
                'set_id' => null,
                'year_level' => 1,
                'status' => 'Regular',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            DB::table('students')->insert([
                'id' => 1,
                'user_id' => 4,
                'set_id' => null,
                'year_level' => 1,
                'status' => 'Regular',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // 4. Ensure Baseline Departments
        $departments = [
            ['dept_abbrev' => 'CITE', 'dept_name' => 'College of Information Technology Education', 'status' => 'active'],
        ];
        foreach ($departments as $d) {
            Department::updateOrCreate(['dept_abbrev' => $d['dept_abbrev']], $d);
        }

        // 5. Ensure Baseline Programs
        $cite = Department::where('dept_abbrev', 'CITE')->first();

        $programs = [
            ['program_abbrev' => 'BSIT', 'program_name' => 'Bachelor of Science in Information Technology', 'department_id' => $cite ? $cite->id : 1, 'status' => 'active'],
        ];
        foreach ($programs as $p) {
            Program::updateOrCreate(['program_abbrev' => $p['program_abbrev']], $p);
        }

        // 6. Ensure Grading Periods
        if (GradingPeriod::count() === 0) {
            $periods = [
                ['name' => 'Prelim', 'order_num' => 1, 'weight' => 20.00, 'is_current' => 0, 'academic_term_id' => 1],
                ['name' => 'Midterm', 'order_num' => 2, 'weight' => 20.00, 'is_current' => 0, 'academic_term_id' => 1],
                ['name' => 'Semi-Final', 'order_num' => 3, 'weight' => 20.00, 'is_current' => 0, 'academic_term_id' => 1],
                ['name' => 'Final', 'order_num' => 4, 'weight' => 40.00, 'is_current' => 1, 'academic_term_id' => 1],
                ['name' => 'Prelim', 'order_num' => 1, 'weight' => 20.00, 'is_current' => 0, 'academic_term_id' => 2],
                ['name' => 'Midterm', 'order_num' => 2, 'weight' => 20.00, 'is_current' => 0, 'academic_term_id' => 2],
                ['name' => 'Semi-Final', 'order_num' => 3, 'weight' => 20.00, 'is_current' => 0, 'academic_term_id' => 2],
                ['name' => 'Final', 'order_num' => 4, 'weight' => 40.00, 'is_current' => 1, 'academic_term_id' => 2],
            ];
            foreach ($periods as $period) {
                GradingPeriod::create($period);
            }
        }

        // 7. Ensure Baseline Sets
        if (Set::count() === 0) {
            Set::create([
                'name' => 'BSIT 1-A',
                'academic_term_id' => $termId,
                'year_level' => 1,
                'department_id' => $cite ? (int) $cite->id : 1,
                'program_id' => 1,
                'status' => 'active',
            ]);
            Set::create([
                'name' => 'BSIT 1-B',
                'academic_term_id' => $termId,
                'year_level' => 1,
                'department_id' => $cite ? (int) $cite->id : 1,
                'program_id' => 1,
                'status' => 'active',
            ]);

            $set1 = Set::where('name', 'BSIT 1-A')->first();
            if ($set1) {
                Student::where('id', 1)->update(['set_id' => $set1->id]);
                DB::table('student_details')->where('user_id', 4)->update(['set_id' => $set1->id]);
            }
        }

        // 8. Ensure Baseline Subjects
        if (Subject::count() === 0) {
            $defaultSubjects = [
                [
                    'code' => 'IT101',
                    'name' => 'Introduction to Computing',
                    'units' => 3.0,
                    'nature' => 'Lecture',
                    'year_level' => 1,
                    'semester' => 1,
                    'program_id' => 1,
                    'academic_term_id' => $termId,
                    'is_archived' => 0,
                ],
                [
                    'code' => 'IT102',
                    'name' => 'Computer Programming 1',
                    'units' => 3.0,
                    'nature' => 'Laboratory',
                    'year_level' => 1,
                    'semester' => 1,
                    'program_id' => 1,
                    'academic_term_id' => $termId,
                    'is_archived' => 0,
                ],
                [
                    'code' => 'IT103',
                    'name' => 'Data Structures and Algorithms',
                    'units' => 3.0,
                    'nature' => 'Laboratory',
                    'year_level' => 2,
                    'semester' => 1,
                    'program_id' => 1,
                    'academic_term_id' => $termId,
                    'is_archived' => 0,
                ],
                [
                    'code' => 'IT104',
                    'name' => 'Discrete Mathematics and Structures',
                    'units' => 3.0,
                    'nature' => 'Lecture',
                    'year_level' => 1,
                    'semester' => 2,
                    'program_id' => 1,
                    'academic_term_id' => $termId,
                    'is_archived' => 0,
                ],
            ];

            foreach ($defaultSubjects as $subData) {
                Subject::create($subData);
            }

            $sub2 = Subject::where('subject_code', 'IT102')->first();
            $sub3 = Subject::where('subject_code', 'IT103')->first();
            if ($sub2 && $sub3) {
                DB::table('prerequisites')->insert([
                    'subject_id' => $sub3->id,
                    'prerequisite_subject_id' => $sub2->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // 9. Ensure Faculty-Subject Load & Student Enrollment
        $firstSubject = Subject::first();
        if ($firstSubject) {
            $facultyUser = User::find(3);
            if ($facultyUser && !DB::table('faculty_subjects')->where('subject_id', $firstSubject->id)->exists()) {
                DB::table('faculty_subjects')->insert([
                    'academic_term_id' => $termId,
                    'faculty_id' => $facultyUser->id,
                    'subject_id' => $firstSubject->id,
                    'assigned_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $studentUser = Student::find(1);
            if ($studentUser && !DB::table('enrollments')->where('subject_id', $firstSubject->id)->exists()) {
                DB::table('enrollments')->insert([
                    'academic_term_id' => $termId,
                    'student_id' => $studentUser->id,
                    'subject_id' => $firstSubject->id,
                    'enrolled_at' => date('Y-m-d H:i:s'),
                ]);
            }

            // 10. Ensure Grading Settings
            if (GradingSetting::count() === 0) {
                GradingSetting::create([
                    'academic_term_id' => $termId,
                    'subject_id' => $firstSubject->id,
                    'grading_method' => 'zero_based',
                    'min_grade' => 0.00,
                    'max_grade' => 100.00,
                    'prelim_weight' => 20.00,
                    'midterm_weight' => 20.00,
                    'semi_final_weight' => 20.00,
                    'final_weight' => 40.00,
                ]);
            }
        }
    }
}
