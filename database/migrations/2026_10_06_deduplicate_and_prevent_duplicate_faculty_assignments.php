<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up(): void
    {
        $pdo = Capsule::connection()->getPdo();

        // 1. Remove duplicate faculty_subjects rows keeping the earliest assignment (lowest id)
        try {
            $pdo->exec("
                DELETE fs2 FROM faculty_subjects fs1
                JOIN faculty_subjects fs2 ON fs1.faculty_id = fs2.faculty_id
                    AND fs1.subject_id = fs2.subject_id
                    AND fs1.academic_term_id = fs2.academic_term_id
                    AND (
                        (fs1.set_id IS NULL AND fs2.set_id IS NULL)
                        OR (fs1.set_id = fs2.set_id)
                    )
                    AND fs1.id < fs2.id
            ");
        } catch (\Throwable $e) {
            // Log or ignore if table does not exist yet
        }
    }

    public function down(): void
    {
        // No reversal needed for deduplication
    }
};
