<?php

declare(strict_types=1);

namespace Tests;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\Program;

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
            ['dept_abbrev' => 'CITE', 'dept_name' => 'College of Information Technology Education', 'status' => 'active'],
            ['dept_abbrev' => 'COC', 'dept_name' => 'College of Criminology', 'status' => 'active'],
            ['dept_abbrev' => 'CBA', 'dept_name' => 'College of Business Administration', 'status' => 'active'],
            ['dept_abbrev' => 'COED', 'dept_name' => 'College of Education', 'status' => 'active'],
        ];
        foreach ($departments as $d) {
            Department::updateOrCreate(['dept_abbrev' => $d['dept_abbrev']], $d);
        }

        // 2. Ensure baseline programs
        $cite = Department::where('dept_abbrev', 'CITE')->first();
        $coc = Department::where('dept_abbrev', 'COC')->first();
        $cba = Department::where('dept_abbrev', 'CBA')->first();
        $coed = Department::where('dept_abbrev', 'COED')->first();

        $programs = [
            ['program_abbrev' => 'BSIT', 'program_name' => 'Bachelor of Science in Information Technology', 'department_id' => $cite ? $cite->id : 1, 'status' => 'active'],
            ['program_abbrev' => 'BSCS', 'program_name' => 'Bachelor of Science in Computer Science', 'department_id' => $cite ? $cite->id : 1, 'status' => 'active'],
            ['program_abbrev' => 'BSCRIM', 'program_name' => 'Bachelor of Science in Criminology', 'department_id' => $coc ? $coc->id : 2, 'status' => 'active'],
            ['program_abbrev' => 'BSBA', 'program_name' => 'Bachelor of Science in Business Administration major in Marketing Management', 'department_id' => $cba ? $cba->id : 3, 'status' => 'active'],
            ['program_abbrev' => 'BEED', 'program_name' => 'Bachelor of Elementary Education', 'department_id' => $coed ? $coed->id : 4, 'status' => 'active'],
            ['program_abbrev' => 'BSED', 'program_name' => 'Bachelor of Secondary Education', 'department_id' => $coed ? $coed->id : 4, 'status' => 'active'],
        ];
        foreach ($programs as $p) {
            Program::updateOrCreate(['program_abbrev' => $p['program_abbrev']], $p);
        }
    }
}
