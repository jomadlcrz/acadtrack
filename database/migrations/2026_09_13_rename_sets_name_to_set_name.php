<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up(): void
    {
        $pdo = Capsule::connection()->getPdo();

        if (Capsule::schema()->hasTable('sets')) {
            if (Capsule::schema()->hasColumn('sets', 'name') && !Capsule::schema()->hasColumn('sets', 'set_name')) {
                // Drop unique_set index if exists
                try {
                    $pdo->exec("ALTER TABLE `sets` DROP INDEX `unique_set`");
                } catch (\Throwable $e) {}

                $pdo->exec("ALTER TABLE `sets` CHANGE COLUMN `name` `set_name` VARCHAR(50) NOT NULL");

                try {
                    $pdo->exec("ALTER TABLE `sets` ADD UNIQUE KEY `unique_set` (`set_name`, `academic_term_id`)");
                } catch (\Throwable $e) {}
            }
        }
    }

    public function down(): void
    {
        $pdo = Capsule::connection()->getPdo();

        if (Capsule::schema()->hasTable('sets')) {
            if (Capsule::schema()->hasColumn('sets', 'set_name') && !Capsule::schema()->hasColumn('sets', 'name')) {
                try {
                    $pdo->exec("ALTER TABLE `sets` DROP INDEX `unique_set`");
                } catch (\Throwable $e) {}

                $pdo->exec("ALTER TABLE `sets` CHANGE COLUMN `set_name` `name` VARCHAR(50) NOT NULL");

                try {
                    $pdo->exec("ALTER TABLE `sets` ADD UNIQUE KEY `unique_set` (`name`, `academic_term_id`)");
                } catch (\Throwable $e) {}
            }
        }
    }
};
