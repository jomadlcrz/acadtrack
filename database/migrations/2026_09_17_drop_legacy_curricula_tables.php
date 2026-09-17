<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up(): void
    {
        Capsule::schema()->dropIfExists('curriculum_subjects');
        Capsule::schema()->dropIfExists('curricula');
    }

    public function down(): void
    {
        // Legacy tables permanently refactored into subjects and prerequisites
    }
};
