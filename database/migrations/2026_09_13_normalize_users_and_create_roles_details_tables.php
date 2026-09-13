<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
$dotenv->load();

$capsule = new Capsule();
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'database' => env('DB_DATABASE', 'acadtrack'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$pdo = Capsule::connection()->getPdo();

echo "Starting normalization migration for user_roles, admin_details, student_details, faculty_details...\n";

// 1. Create user_roles
if (!Capsule::schema()->hasTable('user_roles')) {
    Capsule::schema()->create('user_roles', function (Blueprint $table) {
        $table->increments('id');
        $table->integer('user_id');
        $table->enum('role_name', ['Admin', 'Dean', 'Faculty', 'Student']);
        $table->timestamps();

        $table->unique(['user_id', 'role_name'], 'uq_user_role');
        $table->index('role_name', 'idx_user_role_role');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
    echo "Created 'user_roles' table.\n";
}

// 2. Create admin_details
if (!Capsule::schema()->hasTable('admin_details')) {
    Capsule::schema()->create('admin_details', function (Blueprint $table) {
        $table->increments('id');
        $table->integer('user_id')->unique();
        $table->string('first_name', 100);
        $table->string('last_name', 100);
        $table->string('middle_name', 100)->nullable();
        $table->string('phone_number', 30)->nullable();
        $table->timestamps();

        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
    echo "Created 'admin_details' table.\n";
}

// 3. Create student_details
if (!Capsule::schema()->hasTable('student_details')) {
    Capsule::schema()->create('student_details', function (Blueprint $table) {
        $table->increments('id');
        $table->integer('user_id')->unique();
        $table->string('student_number', 50)->nullable()->unique();
        $table->string('first_name', 100);
        $table->string('last_name', 100);
        $table->string('middle_name', 100)->nullable();
        $table->unsignedInteger('program_id')->nullable();
        $table->integer('set_id')->nullable();
        $table->integer('year_level')->default(1);
        $table->enum('status', ['Regular', 'Irregular'])->default('Regular');
        $table->timestamps();

        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('set_id')->references('id')->on('sets')->onDelete('set null');
        $table->foreign('program_id')->references('id')->on('programs')->onDelete('set null');
        $table->index(['year_level', 'status'], 'idx_student_details_yr_status');
    });
    echo "Created 'student_details' table.\n";
}

// 4. Create faculty_details (faculty_type instructor, dean)
if (!Capsule::schema()->hasTable('faculty_details')) {
    Capsule::schema()->create('faculty_details', function (Blueprint $table) {
        $table->increments('id');
        $table->integer('user_id')->unique();
        $table->string('employee_id', 50)->nullable()->unique();
        $table->string('first_name', 100);
        $table->string('last_name', 100);
        $table->string('middle_name', 100)->nullable();
        $table->enum('faculty_type', ['instructor', 'dean'])->default('instructor');
        $table->unsignedInteger('department_id')->nullable();
        $table->timestamps();

        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
        $table->index('faculty_type', 'idx_faculty_type');
    });
    echo "Created 'faculty_details' table.\n";
}

// 5. Migrate existing data from users, students, and faculty
echo "Migrating data from existing users table into normalized tables...\n";

// Check if users has the old columns first
$hasOldColumns = Capsule::schema()->hasColumn('users', 'role') && Capsule::schema()->hasColumn('users', 'first_name');

if ($hasOldColumns) {
    // Populate user_roles
    $pdo->exec("
        INSERT IGNORE INTO user_roles (user_id, role_name, created_at, updated_at)
        SELECT id, role, created_at, COALESCE(updated_at, created_at)
        FROM users
    ");

    // Populate admin_details
    $pdo->exec("
        INSERT IGNORE INTO admin_details (user_id, first_name, last_name, created_at, updated_at)
        SELECT id, first_name, last_name, created_at, COALESCE(updated_at, created_at)
        FROM users
        WHERE role = 'Admin'
    ");

    // Populate faculty_details for Dean
    $pdo->exec("
        INSERT IGNORE INTO faculty_details (user_id, first_name, last_name, faculty_type, department_id, created_at, updated_at)
        SELECT u.id, u.first_name, u.last_name, 'dean', f.department_id, u.created_at, COALESCE(u.updated_at, u.created_at)
        FROM users u
        LEFT JOIN faculty f ON f.user_id = u.id
        WHERE u.role = 'Dean'
    ");

    // Populate faculty_details for Faculty
    $pdo->exec("
        INSERT IGNORE INTO faculty_details (user_id, first_name, last_name, faculty_type, department_id, created_at, updated_at)
        SELECT u.id, u.first_name, u.last_name, 'instructor', f.department_id, u.created_at, COALESCE(u.updated_at, u.created_at)
        FROM users u
        LEFT JOIN faculty f ON f.user_id = u.id
        WHERE u.role = 'Faculty'
    ");

    // Populate student_details
    $pdo->exec("
        INSERT IGNORE INTO student_details (user_id, student_number, first_name, last_name, set_id, year_level, status, created_at, updated_at)
        SELECT u.id, u.student_number, u.first_name, u.last_name, s.set_id, COALESCE(s.year_level, 1), COALESCE(s.status, 'Regular'), u.created_at, COALESCE(u.updated_at, u.created_at)
        FROM users u
        LEFT JOIN students s ON s.user_id = u.id
        WHERE u.role = 'Student'
    ");
    echo "Data successfully migrated to detail tables.\n";
}

// 6. Alter users table to match exact requested columns:
// id, email, password, is_temp_password, status, created_at, deactivated_at
echo "Altering users table...\n";

// Rename force_password_change -> is_temp_password if exists
if (Capsule::schema()->hasColumn('users', 'force_password_change')) {
    if (Capsule::schema()->hasColumn('users', 'is_temp_password')) {
        $pdo->exec("ALTER TABLE users DROP COLUMN force_password_change");
    } else {
        $pdo->exec("ALTER TABLE users CHANGE COLUMN force_password_change is_temp_password TINYINT(1) NOT NULL DEFAULT 0");
    }
    echo "Handled 'force_password_change'.\n";
} elseif (!Capsule::schema()->hasColumn('users', 'is_temp_password')) {
    $pdo->exec("ALTER TABLE users ADD COLUMN is_temp_password TINYINT(1) NOT NULL DEFAULT 0");
    echo "Added 'is_temp_password' column.\n";
}

// Add deactivated_at if missing
if (!Capsule::schema()->hasColumn('users', 'deactivated_at')) {
    $pdo->exec("ALTER TABLE users ADD COLUMN deactivated_at TIMESTAMP NULL DEFAULT NULL AFTER status");
    echo "Added 'deactivated_at' column.\n";
}

// Drop old columns from users if they still exist
if (Capsule::schema()->hasColumn('users', 'student_number')) {
    // Drop unique index if exists
    try {
        $pdo->exec("ALTER TABLE users DROP INDEX student_number");
    } catch (\Throwable $e) {}
    $pdo->exec("ALTER TABLE users DROP COLUMN student_number");
    echo "Dropped 'student_number' from users.\n";
}

if (Capsule::schema()->hasColumn('users', 'first_name')) {
    $pdo->exec("ALTER TABLE users DROP COLUMN first_name");
    echo "Dropped 'first_name' from users.\n";
}

if (Capsule::schema()->hasColumn('users', 'last_name')) {
    $pdo->exec("ALTER TABLE users DROP COLUMN last_name");
    echo "Dropped 'last_name' from users.\n";
}

if (Capsule::schema()->hasColumn('users', 'role')) {
    try {
        $pdo->exec("ALTER TABLE users DROP INDEX idx_role");
    } catch (\Throwable $e) {}
    $pdo->exec("ALTER TABLE users DROP COLUMN role");
    echo "Dropped 'role' from users.\n";
}

if (Capsule::schema()->hasColumn('users', 'updated_at')) {
    $pdo->exec("ALTER TABLE users DROP COLUMN updated_at");
    echo "Dropped 'updated_at' from users.\n";
}

echo "Normalization migration completed successfully!\n";
