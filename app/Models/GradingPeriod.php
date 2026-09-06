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

    public static function getCurrent(): ?array
    {
        $stmt = self::db()->query("SELECT * FROM grading_periods WHERE is_current = 1 LIMIT 1");
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
