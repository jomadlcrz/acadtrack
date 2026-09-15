<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up(): void
    {
        $term2 = Capsule::table('academic_terms')->where('semester', 2)->first();
        if ($term2) {
            Capsule::table('subjects')
                ->where('semester', 2)
                ->where('academic_term_id', 1)
                ->update(['academic_term_id' => $term2->id]);
        }
    }

    public function down(): void
    {
        Capsule::table('subjects')
            ->where('semester', 2)
            ->where('academic_term_id', 2)
            ->update(['academic_term_id' => 1]);
    }
};
