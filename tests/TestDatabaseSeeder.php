<?php

declare(strict_types=1);

namespace Tests;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\Curriculum;
use App\Models\CurriculumSubject;
use App\Models\Department;
use App\Models\Program;
use App\Models\Set;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as Capsule;

class TestDatabaseSeeder
{
    public static function seedIfNeeded(): void
    {
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

        // 1. Ensure baseline departments
        $departments = [
            ['id' => 1, 'dept_abbrev' => 'CITE', 'dept_name' => 'College of Information Technology Education', 'status' => 'active'],
            ['id' => 2, 'dept_abbrev' => 'COC', 'dept_name' => 'College of Criminology', 'status' => 'active'],
            ['id' => 3, 'dept_abbrev' => 'CBA', 'dept_name' => 'College of Business Administration', 'status' => 'active'],
            ['id' => 4, 'dept_abbrev' => 'COED', 'dept_name' => 'College of Education', 'status' => 'active'],
        ];
        foreach ($departments as $d) {
            Department::updateOrCreate(['id' => $d['id']], $d);
        }

        // 2. Ensure baseline programs
        $programs = [
            ['id' => 1, 'program_abbrev' => 'BSIT', 'program_name' => 'Bachelor of Science in Information Technology', 'department_id' => 1, 'status' => 'active'],
            ['id' => 2, 'program_abbrev' => 'BSCS', 'program_name' => 'Bachelor of Science in Computer Science', 'department_id' => 1, 'status' => 'active'],
            ['id' => 3, 'program_abbrev' => 'BSCRIM', 'program_name' => 'Bachelor of Science in Criminology', 'department_id' => 2, 'status' => 'active'],
            ['id' => 4, 'program_abbrev' => 'BSBA', 'program_name' => 'Bachelor of Science in Business Administration major in Marketing Management', 'department_id' => 3, 'status' => 'active'],
            ['id' => 5, 'program_abbrev' => 'BEED', 'program_name' => 'Bachelor of Elementary Education', 'department_id' => 4, 'status' => 'active'],
            ['id' => 6, 'program_abbrev' => 'BSED', 'program_name' => 'Bachelor of Secondary Education', 'department_id' => 4, 'status' => 'active'],
        ];
        foreach ($programs as $p) {
            Program::updateOrCreate(['id' => $p['id']], $p);
        }

        // 3. Ensure baseline curriculum for BSIT
        $bsitCurriculum = Curriculum::firstOrCreate(
            ['program_id' => 1],
            ['status' => 'active']
        );

        // 4. Ensure curriculum subjects
        if (CurriculumSubject::where('curriculum_id', $bsitCurriculum->id)->count() === 0) {
            $currSubjects = [
                ['curriculum_id' => $bsitCurriculum->id, 'year_level' => 'First Year', 'semester' => 1, 'subject_code' => 'IT101', 'descriptive_title' => 'Introduction to Computing', 'units' => 3.0, 'subject_type' => 'GenEd Core', 'prerequisites' => 'None', 'display_order' => 1],
                ['curriculum_id' => $bsitCurriculum->id, 'year_level' => 'First Year', 'semester' => 1, 'subject_code' => 'IT102', 'descriptive_title' => 'Computer Programming 1', 'units' => 3.0, 'subject_type' => 'Major with Lab', 'prerequisites' => 'None', 'display_order' => 2],
                ['curriculum_id' => $bsitCurriculum->id, 'year_level' => 'First Year', 'semester' => 2, 'subject_code' => 'IT103', 'descriptive_title' => 'Data Structures and Algorithms', 'units' => 3.0, 'subject_type' => 'Major with Lab', 'prerequisites' => 'IT102', 'display_order' => 3],
            ];
            foreach ($currSubjects as $cs) {
                CurriculumSubject::create($cs);
            }
        }

        // 5. Ensure baseline sets
        $sets = [
            ['set_name' => 'BSIT-1A', 'program_id' => 1, 'year_level' => 1, 'set_code' => 'A', 'academic_term_id' => 1, 'department_id' => 1, 'status' => 'active'],
            ['set_name' => 'BSIT-1B', 'program_id' => 1, 'year_level' => 1, 'set_code' => 'B', 'academic_term_id' => 1, 'department_id' => 1, 'status' => 'active'],
            ['set_name' => 'BSIT-2A', 'program_id' => 1, 'year_level' => 2, 'set_code' => 'A', 'academic_term_id' => 1, 'department_id' => 1, 'status' => 'active'],
            ['set_name' => 'BSCS-1A', 'program_id' => 2, 'year_level' => 1, 'set_code' => 'A', 'academic_term_id' => 1, 'department_id' => 1, 'status' => 'active'],
        ];
        foreach ($sets as $s) {
            Set::firstOrCreate(
                ['set_name' => $s['set_name'], 'academic_term_id' => $s['academic_term_id']],
                $s
            );
        }

        // 6. Ensure baseline subjects
        $subjects = [
            ['subject_code' => 'IT101', 'descriptive_title' => 'Introduction to Computing', 'nature' => 'Lecture', 'year_level' => 1, 'semester' => 1, 'academic_term_id' => 1, 'is_archived' => 0],
            ['subject_code' => 'IT102', 'descriptive_title' => 'Computer Programming 1', 'nature' => 'Combined', 'year_level' => 1, 'semester' => 1, 'academic_term_id' => 1, 'is_archived' => 0],
            ['subject_code' => 'IT103', 'descriptive_title' => 'Data Structures and Algorithms', 'nature' => 'Combined', 'year_level' => 2, 'semester' => 1, 'academic_term_id' => 1, 'is_archived' => 0],
            ['subject_code' => 'IT201', 'descriptive_title' => 'Web Systems and Technologies', 'nature' => 'Combined', 'year_level' => 2, 'semester' => 2, 'academic_term_id' => 2, 'is_archived' => 0],
            ['subject_code' => 'IT202', 'descriptive_title' => 'Information Management', 'nature' => 'Lecture', 'year_level' => 2, 'semester' => 2, 'academic_term_id' => 2, 'is_archived' => 0],
        ];
        foreach ($subjects as $sub) {
            Subject::firstOrCreate(
                ['subject_code' => $sub['subject_code'], 'academic_term_id' => $sub['academic_term_id']],
                $sub
            );
        }

        // 7. Ensure student user Juan Dela Cruz is linked to set BSIT-1A
        $set1A = Set::where('set_name', 'BSIT-1A')->where('academic_term_id', 1)->first();
        if ($set1A) {
            Capsule::table('student_details')->where('user_id', 4)->update(['set_id' => $set1A->id]);
            Capsule::table('students')->where('user_id', 4)->update(['set_id' => $set1A->id]);
        }
    }
}
