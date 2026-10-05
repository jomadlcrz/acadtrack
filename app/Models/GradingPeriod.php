<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class GradingPeriod extends Model
{
    protected $table = 'grading_periods';

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class, 'grading_period_id');
    }

    public function gradingSheets()
    {
        return $this->hasMany(GradingSheet::class, 'grading_period_id');
    }

    public static function getActive(): array
    {
        $stmt = self::db()->query("SELECT * FROM grading_periods ORDER BY order_num");
        return $stmt->fetchAll();
    }

    public static function getByAcademicTerm(int $academicTermId): array
    {
        $stmt = self::db()->prepare("SELECT * FROM grading_periods WHERE academic_term_id = :academic_term_id ORDER BY order_num");
        $stmt->execute(['academic_term_id' => $academicTermId]);
        return $stmt->fetchAll();
    }

    public static function determineCurrent(int $academicTermId): ?array
    {
        $periods = self::getByAcademicTerm($academicTermId);
        if (empty($periods)) {
            return null;
        }

        // 1. Filter out closed periods
        $unclosed = array_values(array_filter($periods, static fn (array $p): bool => (int) ($p['is_closed'] ?? 0) === 0));

        // If all periods are closed, the last period is the final state
        if (empty($unclosed)) {
            return end($periods) ?: null;
        }

        // 2. Fetch the academic term details to check semester
        $term = AcademicTerm::find($academicTermId);
        $semester = (string) ($term['semester'] ?? '1');
        $currentMonth = (int) date('n');

        // 3. Determine target order_num based on calendar month
        // Philippine collegiate calendar (GWC standard):
        // 1st Sem: Aug-Sep (Prelim: 1), Oct (Midterm: 2), Nov (Semi-Final: 3), Dec-Jan (Final: 4)
        // 2nd Sem: Jan-Feb (Prelim: 1), Mar (Midterm: 2), Apr (Semi-Final: 3), May-Jul (Final: 4)
        if ($semester === '2') {
            $targetOrder = match (true) {
                $currentMonth === 3 => 2,
                $currentMonth === 4 => 3,
                $currentMonth >= 5 && $currentMonth <= 7 => 4,
                default => 1,
            };
        } else {
            $targetOrder = match (true) {
                $currentMonth === 10 => 2,
                $currentMonth === 11 => 3,
                $currentMonth === 12 || $currentMonth === 1 => 4,
                default => 1,
            };
        }

        // 4. Find the matching unclosed period
        foreach ($unclosed as $p) {
            if ((int) $p['order_num'] === $targetOrder) {
                return $p;
            }
        }

        // 5. If the target period is closed/unavailable, pick the first unclosed period after it
        foreach ($unclosed as $p) {
            if ((int) $p['order_num'] > $targetOrder) {
                return $p;
            }
        }

        // Fallback to first unclosed period
        return reset($unclosed) ?: null;
    }

    public static function syncCurrentPeriod(int $academicTermId): ?array
    {
        $current = self::determineCurrent($academicTermId);
        if (!$current) {
            return null;
        }

        self::db()->prepare("UPDATE grading_periods SET is_current = 0 WHERE academic_term_id = :term_id")
            ->execute(['term_id' => $academicTermId]);
        self::db()->prepare("UPDATE grading_periods SET is_current = 1 WHERE id = :id")
            ->execute(['id' => (int) $current['id']]);

        $current['is_current'] = 1;
        return $current;
    }

    public static function getCurrent(?int $academicTermId = null): ?array
    {
        if ($academicTermId === null) {
            $activeTerm = AcademicTerm::getActive();
            $academicTermId = (int) ($activeTerm['id'] ?? 1);
        }

        self::syncCurrentPeriod($academicTermId);

        $stmt = self::db()->prepare("SELECT * FROM grading_periods WHERE academic_term_id = :term_id AND is_current = 1 LIMIT 1");
        $stmt->execute(['term_id' => $academicTermId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
