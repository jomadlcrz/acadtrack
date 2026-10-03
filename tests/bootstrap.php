<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

new \App\Core\Database(
    env('DB_HOST'),
    env('DB_DATABASE'),
    env('DB_USERNAME'),
    (string) env('DB_PASSWORD', '')
);

define('PHPUNIT_RUNNING', true);

\Tests\TestDatabaseSeeder::seedIfNeeded();

// Tests write to the real database; drop any activity-log rows created during this run.
$auditLogBaseline = (int) \Illuminate\Database\Capsule\Manager::table('audit_logs')->max('id');
register_shutdown_function(static function () use ($auditLogBaseline): void {
    try {
        \Illuminate\Database\Capsule\Manager::table('audit_logs')->where('id', '>', $auditLogBaseline)->delete();
    } catch (\Throwable $e) {
        // best effort
    }
});
