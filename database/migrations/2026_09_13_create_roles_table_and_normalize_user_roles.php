<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up(): void
    {
        $pdo = Capsule::connection()->getPdo();

        // 1. Create `roles` table if not exists
        if (!Capsule::schema()->hasTable('roles')) {
            Capsule::schema()->create('roles', function (Blueprint $table) {
                $table->increments('id');
                $table->string('role_name', 50)->unique();
                $table->string('description', 255)->nullable();
                $table->timestamps();
            });
            echo "Created 'roles' table.\n";
        } elseif (Capsule::schema()->hasColumn('roles', 'name') && !Capsule::schema()->hasColumn('roles', 'role_name')) {
            $pdo->exec("ALTER TABLE `roles` CHANGE COLUMN `name` `role_name` VARCHAR(50) NOT NULL");
        }

        // 2. Seed initial roles
        $roles = [
            ['id' => 1, 'role_name' => 'Admin', 'description' => 'System Administrator'],
            ['id' => 2, 'role_name' => 'Dean', 'description' => 'College Dean'],
            ['id' => 3, 'role_name' => 'Faculty', 'description' => 'Faculty Instructor'],
            ['id' => 4, 'role_name' => 'Student', 'description' => 'Enrolled Student'],
        ];

        foreach ($roles as $r) {
            $stmt = $pdo->prepare("INSERT INTO roles (id, role_name, description, created_at, updated_at)
                VALUES (:id, :role_name, :description, NOW(), NOW())
                ON DUPLICATE KEY UPDATE description = VALUES(description)");
            $stmt->execute($r);
        }
        echo "Seeded core roles into 'roles' table.\n";

        // 3. Update `user_roles` table to reference `role_id`
        if (Capsule::schema()->hasTable('user_roles')) {
            if (!Capsule::schema()->hasColumn('user_roles', 'role_id')) {
                $pdo->exec("ALTER TABLE `user_roles` ADD COLUMN `role_id` INT NULL AFTER `user_id`");
            }

            // Populate role_id based on role_name if role_name exists
            if (Capsule::schema()->hasColumn('user_roles', 'role_name')) {
                $pdo->exec("
                    UPDATE `user_roles` ur
                    JOIN `roles` r ON LOWER(TRIM(ur.role_name)) = LOWER(TRIM(r.role_name))
                    SET ur.role_id = r.id
                ");

                // Default any unmapped role_id to Student (4)
                $pdo->exec("UPDATE `user_roles` SET `role_id` = 4 WHERE `role_id` IS NULL");

                // Ensure an index on user_id exists so the foreign key doesn't block dropping composite uq_user_role
                try {
                    $pdo->exec("ALTER TABLE `user_roles` ADD INDEX `idx_user_id` (`user_id`)");
                } catch (\Throwable $e) {}

                // Drop old indexes referencing role_name
                try {
                    $pdo->exec("ALTER TABLE `user_roles` DROP INDEX `uq_user_role`");
                } catch (\Throwable $e) {}
                try {
                    $pdo->exec("ALTER TABLE `user_roles` DROP INDEX `idx_user_role_role`");
                } catch (\Throwable $e) {}
                try {
                    $pdo->exec("ALTER TABLE `user_roles` DROP INDEX `idx_role_name`");
                } catch (\Throwable $e) {}

                // Drop role_name column
                $pdo->exec("ALTER TABLE `user_roles` DROP COLUMN `role_name`");
            }

            // Make role_id NOT NULL and add foreign key + indexes
            $pdo->exec("ALTER TABLE `user_roles` MODIFY COLUMN `role_id` INT NOT NULL");

            try {
                $pdo->exec("ALTER TABLE `user_roles` ADD UNIQUE KEY `uq_user_role` (`user_id`, `role_id`)");
            } catch (\Throwable $e) {}

            try {
                $pdo->exec("ALTER TABLE `user_roles` ADD INDEX `idx_role_id` (`role_id`)");
            } catch (\Throwable $e) {}

            try {
                $pdo->exec("ALTER TABLE `user_roles` ADD CONSTRAINT `fk_user_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE");
            } catch (\Throwable $e) {}

            echo "Updated 'user_roles' table with 'role_id' foreign key referencing 'roles.id'.\n";
        }
    }

    public function down(): void
    {
        $pdo = Capsule::connection()->getPdo();

        if (Capsule::schema()->hasTable('user_roles')) {
            try {
                $pdo->exec("ALTER TABLE `user_roles` DROP FOREIGN KEY `fk_user_roles_role`");
            } catch (\Throwable $e) {}

            if (!Capsule::schema()->hasColumn('user_roles', 'role_name')) {
                $pdo->exec("ALTER TABLE `user_roles` ADD COLUMN `role_name` ENUM('Admin', 'Dean', 'Faculty', 'Student') NOT NULL DEFAULT 'Student' AFTER `user_id`");
                $pdo->exec("
                    UPDATE `user_roles` ur
                    JOIN `roles` r ON ur.role_id = r.id
                    SET ur.role_name = r.name
                ");
            }

            try {
                $pdo->exec("ALTER TABLE `user_roles` DROP INDEX `uq_user_role`");
            } catch (\Throwable $e) {}

            if (Capsule::schema()->hasColumn('user_roles', 'role_id')) {
                $pdo->exec("ALTER TABLE `user_roles` DROP COLUMN `role_id`");
            }

            try {
                $pdo->exec("ALTER TABLE `user_roles` ADD UNIQUE KEY `uq_user_role` (`user_id`, `role_name`)");
            } catch (\Throwable $e) {}
        }

        if (Capsule::schema()->hasTable('roles')) {
            Capsule::schema()->drop('roles');
        }
    }
};
