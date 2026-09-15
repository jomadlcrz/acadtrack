<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up(): void
    {
        if (!Capsule::schema()->hasTable('password_resets')) {
            Capsule::schema()->create('password_resets', function (Blueprint $table) {
                $table->id();
                $table->string('email', 255);
                $table->string('token', 64);
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('used_at')->nullable();
                $table->index('email', 'idx_password_resets_email');
                $table->index('token', 'idx_password_resets_token');
            });
        }
    }

    public function down(): void
    {
        Capsule::schema()->dropIfExists('password_resets');
    }
};
