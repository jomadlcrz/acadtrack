<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

date_default_timezone_set(env('APP_TIMEZONE', 'Asia/Manila'));

new \App\Core\Database(
    env('DB_HOST'),
    env('DB_DATABASE'),
    env('DB_USERNAME'),
    (string) env('DB_PASSWORD', '')
);

define('PHPUNIT_RUNNING', true);

\Tests\TestDatabaseSeeder::seedIfNeeded();

// Tests write to the real database; drop any activity-log rows created during this run.
$auditLogBaseline = 0;
try {
    if (\Illuminate\Database\Capsule\Manager::schema()->hasTable('audit_logs')) {
        $auditLogBaseline = (int) \Illuminate\Database\Capsule\Manager::table('audit_logs')->max('id');
    }
} catch (\Throwable $e) {
    $auditLogBaseline = 0;
}

register_shutdown_function(static function () use ($auditLogBaseline): void {
    try {
        if (\Illuminate\Database\Capsule\Manager::schema()->hasTable('audit_logs')) {
            \Illuminate\Database\Capsule\Manager::table('audit_logs')->where('id', '>', $auditLogBaseline)->delete();
        }
    } catch (\Throwable $e) {
        // best effort
    }
});
