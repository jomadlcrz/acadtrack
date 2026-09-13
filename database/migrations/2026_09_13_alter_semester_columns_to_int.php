<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up(): void
    {
        $pdo = Capsule::connection()->getPdo();

        // 1. academic_terms table
        if (Capsule::schema()->hasTable('academic_terms') && Capsule::schema()->hasColumn('academic_terms', 'semester')) {
            $pdo->exec("UPDATE `academic_terms` SET `semester` = '1' WHERE `semester` LIKE '%1%'");
            $pdo->exec("UPDATE `academic_terms` SET `semester` = '2' WHERE `semester` LIKE '%2%'");
            $pdo->exec("UPDATE `academic_terms` SET `semester` = '3' WHERE `semester` LIKE '%summer%' OR `semester` LIKE '%3%'");
            $pdo->exec("ALTER TABLE `academic_terms` MODIFY COLUMN `semester` INT(2) NOT NULL DEFAULT 1");
        }

        // 2. subjects table
        if (Capsule::schema()->hasTable('subjects') && Capsule::schema()->hasColumn('subjects', 'semester')) {
            $pdo->exec("UPDATE `subjects` SET `semester` = '1' WHERE `semester` LIKE '%1%'");
            $pdo->exec("UPDATE `subjects` SET `semester` = '2' WHERE `semester` LIKE '%2%'");
            $pdo->exec("UPDATE `subjects` SET `semester` = '3' WHERE `semester` LIKE '%summer%' OR `semester` LIKE '%3%'");
            $pdo->exec("ALTER TABLE `subjects` MODIFY COLUMN `semester` INT(2) NOT NULL DEFAULT 1");
        }

        // 3. curriculum_subjects table
        if (Capsule::schema()->hasTable('curriculum_subjects') && Capsule::schema()->hasColumn('curriculum_subjects', 'semester')) {
            $pdo->exec("UPDATE `curriculum_subjects` SET `semester` = '1' WHERE `semester` LIKE '%1%'");
            $pdo->exec("UPDATE `curriculum_subjects` SET `semester` = '2' WHERE `semester` LIKE '%2%'");
            $pdo->exec("UPDATE `curriculum_subjects` SET `semester` = '3' WHERE `semester` LIKE '%summer%' OR `semester` LIKE '%3%'");
            $pdo->exec("ALTER TABLE `curriculum_subjects` MODIFY COLUMN `semester` INT(2) NOT NULL DEFAULT 1");
        }
    }

    public function down(): void
    {
        $pdo = Capsule::connection()->getPdo();

        if (Capsule::schema()->hasTable('academic_terms') && Capsule::schema()->hasColumn('academic_terms', 'semester')) {
            $pdo->exec("ALTER TABLE `academic_terms` MODIFY COLUMN `semester` VARCHAR(50) NOT NULL DEFAULT '1'");
        }

        if (Capsule::schema()->hasTable('subjects') && Capsule::schema()->hasColumn('subjects', 'semester')) {
            $pdo->exec("ALTER TABLE `subjects` MODIFY COLUMN `semester` ENUM('1','2') NOT NULL DEFAULT '1'");
        }

        if (Capsule::schema()->hasTable('curriculum_subjects') && Capsule::schema()->hasColumn('curriculum_subjects', 'semester')) {
            $pdo->exec("ALTER TABLE `curriculum_subjects` MODIFY COLUMN `semester` VARCHAR(50) NOT NULL DEFAULT '1st Semester'");
        }
    }
};
