<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up(): void
    {
        if (!Capsule::schema()->hasColumn('users', 'force_password_change')) {
            Capsule::schema()->table('users', function (Blueprint $table) {
                $table->boolean('force_password_change')->default(false)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Capsule::schema()->hasColumn('users', 'force_password_change')) {
            Capsule::schema()->table('users', function (Blueprint $table) {
                $table->dropColumn('force_password_change');
            });
        }
    }
};
