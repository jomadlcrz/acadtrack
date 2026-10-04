<?php

declare(strict_types=1);

if (!function_exists('semester_label')) {
    /**
     * Display label for a semester number: 1 => "1st Semester", 2 => "2nd Semester", 3 => "Summer".
     */
    function semester_label(int|string|null $semester): string
    {
        $n = (int) $semester;

        if ($n === 3) {
            return 'Summer';
        }

        $suffix = match (true) {
            $n % 100 >= 11 && $n % 100 <= 13 => 'th',
            $n % 10 === 1 => 'st',
            $n % 10 === 2 => 'nd',
            $n % 10 === 3 => 'rd',
            default => 'th',
        };

        return $n . $suffix . ' Semester';
    }
}
