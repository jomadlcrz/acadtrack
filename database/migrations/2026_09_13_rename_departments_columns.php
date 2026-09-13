<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up(): void
    {
        $pdo = Capsule::connection()->getPdo();

        if (Capsule::schema()->hasTable('departments')) {
            if (Capsule::schema()->hasColumn('departments', 'code') && !Capsule::schema()->hasColumn('departments', 'dept_abbrev')) {
                $pdo->exec("ALTER TABLE `departments` CHANGE COLUMN `code` `dept_abbrev` VARCHAR(50) NOT NULL");
            }
            if (Capsule::schema()->hasColumn('departments', 'name') && !Capsule::schema()->hasColumn('departments', 'dept_name')) {
                $pdo->exec("ALTER TABLE `departments` CHANGE COLUMN `name` `dept_name` VARCHAR(150) NOT NULL");
            }
        }
    }

    public function down(): void
    {
        $pdo = Capsule::connection()->getPdo();

        if (Capsule::schema()->hasTable('departments')) {
            if (Capsule::schema()->hasColumn('departments', 'dept_abbrev') && !Capsule::schema()->hasColumn('departments', 'code')) {
                $pdo->exec("ALTER TABLE `departments` CHANGE COLUMN `dept_abbrev` `code` VARCHAR(50) NOT NULL");
            }
            if (Capsule::schema()->hasColumn('departments', 'dept_name') && !Capsule::schema()->hasColumn('departments', 'name')) {
                $pdo->exec("ALTER TABLE `departments` CHANGE COLUMN `dept_name` `name` VARCHAR(150) NOT NULL");
            }
        }
    }
};
