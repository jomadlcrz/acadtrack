<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Section extends Model
{
    protected $table = 'sections';

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'section_id');
    }

    public static function getByYearAndTerm(int $yearLevel, int $academicTermId): array
    {
        $stmt = self::db()->prepare("
            SELECT * FROM sections
            WHERE year_level = :year_level AND academic_term_id = :academic_term_id
            ORDER BY name
        ");
        $stmt->execute(['year_level' => $yearLevel, 'academic_term_id' => $academicTermId]);
        return $stmt->fetchAll();
    }
}
