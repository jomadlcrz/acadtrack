<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up(): void
    {
        // 1. Convert any empty strings to true NULL
        Capsule::table('users')->where('student_number', '')->update(['student_number' => null]);

        // 2. Resolve any existing duplicates by setting duplicate/late student numbers to NULL
        $duplicates = Capsule::table('users')
            ->select('student_number')
            ->whereNotNull('student_number')
            ->groupBy('student_number')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('student_number');

        foreach ($duplicates as $duplicateNumber) {
            $users = Capsule::table('users')
                ->where('student_number', $duplicateNumber)
                ->orderBy('id', 'asc')
                ->get();

            foreach ($users->slice(1) as $user) {
                Capsule::table('users')->where('id', $user->id)->update(['student_number' => null]);
            }
        }

        // 3. Ensure student_number is nullable
        Capsule::statement("ALTER TABLE users MODIFY student_number VARCHAR(50) NULL DEFAULT NULL");

        // 4. Add UNIQUE constraint if it doesn't already exist
        $existingIndex = Capsule::select("SHOW INDEX FROM users WHERE Key_name = 'users_student_number_unique'");
        if (empty($existingIndex)) {
            Capsule::statement("ALTER TABLE users ADD UNIQUE KEY users_student_number_unique (student_number)");
        }
    }

    public function down(): void
    {
        $existingIndex = Capsule::select("SHOW INDEX FROM users WHERE Key_name = 'users_student_number_unique'");
        if (!empty($existingIndex)) {
            Capsule::statement("ALTER TABLE users DROP INDEX users_student_number_unique");
        }
    }
};
