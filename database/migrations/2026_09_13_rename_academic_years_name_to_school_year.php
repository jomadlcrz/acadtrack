<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up(): void
    {
        $pdo = Capsule::connection()->getPdo();

        if (Capsule::schema()->hasTable('academic_years')) {
            if (Capsule::schema()->hasColumn('academic_years', 'name') && !Capsule::schema()->hasColumn('academic_years', 'school_year')) {
                $pdo->exec("ALTER TABLE `academic_years` CHANGE COLUMN `name` `school_year` VARCHAR(20) NOT NULL");
            }
        }
    }

    public function down(): void
    {
        $pdo = Capsule::connection()->getPdo();

        if (Capsule::schema()->hasTable('academic_years')) {
            if (Capsule::schema()->hasColumn('academic_years', 'school_year') && !Capsule::schema()->hasColumn('academic_years', 'name')) {
                $pdo->exec("ALTER TABLE `academic_years` CHANGE COLUMN `school_year` `name` VARCHAR(20) NOT NULL");
            }
        }
    }
};
